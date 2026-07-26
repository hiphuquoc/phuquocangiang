<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;

class GcsMediaStorageService
{
    /** @var array<string, bool> */
    private array $existsCache = [];

    public function diskName(): string
    {
        return (string) config('media.disk', 'gcs');
    }

    public function uploadPrefix(): string
    {
        return trim((string) config('media.upload_prefix', 'media/uploads'), '/');
    }

    /**
     * @return list<string>
     */
    public function cloudPrefixes(): array
    {
        $prefixes = (array) config('media.cloud_prefixes', []);
        $prefixes[] = $this->uploadPrefix();

        return array_values(array_unique(array_filter(array_map(
            static fn ($prefix) => trim((string) $prefix, '/'),
            $prefixes
        ))));
    }

    public function extension(): string
    {
        return (string) config('admin.images.extension', 'webp');
    }

    public function normalizePath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return $path;
    }

    public function isLegacyLocalPath(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        return str_starts_with($path, '/storage')
            || str_starts_with($path, '/images')
            || str_starts_with($normalized, 'storage/images/upload/')
            || str_starts_with($normalized, 'images/upload/')
            || str_starts_with($normalized, 'public/images/upload/');
    }

    public function isCloudPath(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        if ($this->isLegacyLocalPath($path)) {
            return false;
        }

        $normalized = $this->normalizePath($path);

        foreach ($this->cloudPrefixes() as $prefix) {
            if ($normalized === $prefix || str_starts_with($normalized, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Chuẩn hóa path DB (GCS hoặc legacy /storage/images/upload/…) về object path trên GCS nếu có thể.
     * Legacy: /storage/images/upload/foo-750.webp → media/uploads/foo.webp
     */
    public function toCloudObjectPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        if ($this->isCloudPath($path)) {
            // Giữ nguyên object key trên GCS (kể cả -750/-400/-small đã lưu sẵn).
            return $this->normalizePath($path);
        }

        if (!$this->isLegacyLocalPath($path)) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $normalized = preg_replace('#^(storage/)?images/upload/#', '', $normalized) ?? $normalized;
        $normalized = preg_replace('#^public/images/upload/#', '', $normalized) ?? $normalized;
        $filename = basename($normalized);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION) ?: $this->extension();
        $base = $this->extractBaseName($filename);

        return $this->buildUploadPath($base . '.' . $ext);
    }

    /**
     * Upload 3 phiên bản: gốc, -small (450px), -medium (800px).
     *
     * @return array{original: string, small: string, medium: string}
     */
    public function uploadImageSet(
        UploadedFile $file,
        string $basename,
        ?string $extension = null,
        ?string $prefix = null,
    ): array {
        $extension ??= $this->extension();
        $basename = $this->sanitizeBasename($basename);
        $folder = trim((string) ($prefix ?? $this->uploadPrefix()), '/');

        $originalPath = $this->buildPath($folder, "{$basename}.{$extension}");
        $smallPath = $this->buildPath($folder, "{$basename}-small.{$extension}");
        $mediumPath = $this->buildPath($folder, "{$basename}-medium.{$extension}");

        $this->putOriginal($file, $originalPath, $extension);
        $this->putResized($file, $smallPath, (int) config('media.variants.small.width', 450), null, $extension);
        $this->putResized($file, $mediumPath, (int) config('media.variants.medium.width', 800), null, $extension);

        return [
            'original' => $originalPath,
            'small' => $smallPath,
            'medium' => $mediumPath,
        ];
    }

    public function put(string $objectPath, string $binary, array $options = []): string
    {
        $path = $this->normalizePath($objectPath);
        Storage::disk($this->diskName())->put($path, $binary, $options);

        return $path;
    }

    /**
     * Tải ảnh từ URL remote rồi lưu lên GCS (vd. crawler hotel).
     */
    public function putFromUrl(string $url, string $objectPath, ?string $extension = null): bool
    {
        $extension ??= pathinfo($this->normalizePath($objectPath), PATHINFO_EXTENSION) ?: $this->extension();
        $quality = (int) config('media.quality', 90);

        try {
            $binary = (string) ImageManagerStatic::make($url)->encode($extension, $quality);
            $this->put($objectPath, $binary);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function putResized(
        UploadedFile|string $source,
        string $objectPath,
        int $width,
        ?int $height = null,
        ?string $extension = null,
    ): string {
        $extension ??= $this->extension();
        $quality = (int) config('media.quality', 90);

        $image = is_string($source)
            ? ImageManagerStatic::make($source)
            : ImageManagerStatic::make($source->getRealPath());

        if ($height !== null) {
            $image->resize($width, $height);
        } else {
            $image->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        return $this->put($objectPath, (string) $image->encode($extension, $quality));
    }

    public function putOriginal(UploadedFile $file, string $objectPath, ?string $extension = null): string
    {
        $extension ??= $this->extension();
        $quality = (int) config('media.quality', 90);

        $binary = (string) ImageManagerStatic::make($file->getRealPath())
            ->encode($extension, $quality);

        return $this->put($objectPath, $binary);
    }

    public function publicUrl(string $objectPath): string
    {
        return Storage::disk($this->diskName())->url($this->normalizePath($objectPath));
    }

    public function mimeForPath(string $path): string
    {
        $extension = strtolower(pathinfo($this->normalizePath($path), PATHINFO_EXTENSION));

        return match ($extension) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
    }

    /**
     * Resolve path biến thể từ path gốc hoặc path bất kỳ trong set.
     * Legacy /storage/images/upload/foo-750.webp → thử media/uploads/foo[-small|-medium].webp trên GCS.
     */
    public function resolveVariantPath(?string $storedPath, string $variant = 'original'): ?string
    {
        if ($storedPath === null || $storedPath === '') {
            return null;
        }

        $cloudPath = $this->toCloudObjectPath($storedPath);

        if ($cloudPath === null) {
            // Không map được sang GCS — giữ nguyên path (legacy local hoặc URL lạ).
            return $storedPath;
        }

        $normalized = $this->normalizePath($cloudPath);
        $dir = dirname($normalized);
        $dir = $dir === '.' ? '' : $dir . '/';
        $ext = pathinfo($normalized, PATHINFO_EXTENSION) ?: $this->extension();
        $base = $this->extractBaseName($normalized);

        $original = $dir . $base . '.' . $ext;

        if ($variant === 'original') {
            if ($this->exists($original)) {
                return $original;
            }

            // Path DB đã là cloud nhưng object chưa có (hoặc tên lệch) → trả path đã chuẩn hóa.
            return $this->isCloudPath($storedPath) ? $normalized : $storedPath;
        }

        $suffix = (string) (config("media.variants.{$variant}.suffix") ?? "-{$variant}");
        $candidate = $dir . $base . $suffix . '.' . $ext;

        if ($this->exists($candidate)) {
            return $candidate;
        }

        if ($variant === 'small') {
            foreach ((array) config('media.legacy_suffixes', []) as $legacySuffix) {
                $legacy = $dir . $base . $legacySuffix . '.' . $ext;
                if ($this->exists($legacy)) {
                    return $legacy;
                }
            }
        }

        if ($variant === 'medium') {
            $legacyNormal = $dir . $base . '-750.' . $ext;
            if ($this->exists($legacyNormal)) {
                return $legacyNormal;
            }
        }

        if ($this->exists($original)) {
            return $original;
        }

        // Legacy chưa migrate lên GCS → giữ path gốc để fallback /storage hoặc default.
        return $this->isLegacyLocalPath($storedPath) ? $storedPath : $normalized;
    }

    public function deleteImageSet(?string $anyPathInSet): void
    {
        if ($anyPathInSet === null || $anyPathInSet === '') {
            return;
        }

        if (!$this->isCloudPath($anyPathInSet)) {
            $this->delete($anyPathInSet);

            return;
        }

        $normalized = $this->normalizePath($anyPathInSet);
        $dir = dirname($normalized);
        $dir = $dir === '.' ? '' : $dir . '/';
        $ext = pathinfo($normalized, PATHINFO_EXTENSION) ?: $this->extension();
        $base = $this->extractBaseName($normalized);

        $paths = [
            $dir . $base . '.' . $ext,
            $dir . $base . '-small.' . $ext,
            $dir . $base . '-medium.' . $ext,
        ];

        foreach ((array) config('media.legacy_suffixes', []) as $legacySuffix) {
            $paths[] = $dir . $base . $legacySuffix . '.' . $ext;
        }
        $paths[] = $dir . $base . '-750.' . $ext;
        $paths[] = $dir . $base . '-normal.' . $ext;

        foreach (array_unique($paths) as $path) {
            $this->delete($path);
        }
    }

    public function exists(string $path): bool
    {
        $cacheKey = $path;
        if (array_key_exists($cacheKey, $this->existsCache)) {
            return $this->existsCache[$cacheKey];
        }

        if ($this->isCloudPath($path)) {
            try {
                return $this->existsCache[$cacheKey] = Storage::disk($this->diskName())
                    ->exists($this->normalizePath($path));
            } catch (\Throwable) {
                return $this->existsCache[$cacheKey] = false;
            }
        }

        $local = $this->localFilesystemPath($path);

        return $this->existsCache[$cacheKey] = ($local !== null && is_file($local));
    }

    public function get(string $path): ?string
    {
        if ($this->isCloudPath($path)) {
            try {
                if (!Storage::disk($this->diskName())->exists($this->normalizePath($path))) {
                    return null;
                }

                return Storage::disk($this->diskName())->get($this->normalizePath($path));
            } catch (\Throwable) {
                return null;
            }
        }

        if (!$this->exists($path)) {
            return null;
        }

        $local = $this->localFilesystemPath($path);

        return $local !== null ? (string) file_get_contents($local) : null;
    }

    public function delete(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        if ($this->isCloudPath($path)) {
            try {
                return (bool) Storage::disk($this->diskName())->delete($this->normalizePath($path));
            } catch (\Throwable) {
                return false;
            }
        }

        $local = $this->localFilesystemPath($path);
        if ($local !== null && is_file($local)) {
            return @unlink($local);
        }

        return false;
    }

    /**
     * @return array<int, string> GCS object paths
     */
    public function listUploads(?string $search = null, ?string $prefix = null): array
    {
        $folder = trim((string) ($prefix ?? $this->uploadPrefix()), '/') . '/';
        $files = Storage::disk($this->diskName())->files($folder);

        if ($search !== null && $search !== '') {
            $needle = mb_strtolower($search);
            $files = array_values(array_filter(
                $files,
                static fn (string $file): bool => str_contains(mb_strtolower(basename($file)), $needle),
            ));
        }

        rsort($files);

        return $files;
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        $oldPath = $this->normalizePath($oldPath);
        $newPath = $this->normalizePath($newPath);

        if (!$this->exists($oldPath)) {
            return false;
        }

        if ($this->exists($newPath)) {
            return false;
        }

        $content = $this->get($oldPath);
        if ($content === null) {
            return false;
        }

        $this->put($newPath, $content);
        $this->delete($oldPath);

        return true;
    }

    public function buildUploadPath(string $filename): string
    {
        return $this->buildPath($this->uploadPrefix(), $filename);
    }

    public function buildPath(string $prefix, string $filename): string
    {
        return trim($prefix, '/') . '/' . ltrim($filename, '/');
    }

    public function extractBaseName(string $path): string
    {
        $filename = pathinfo($this->normalizePath($path), PATHINFO_FILENAME);

        return (string) preg_replace('/-(small|medium|normal|avatar|logo)(-\d+x\d+)?$/', '', (string) preg_replace('/-\d{3,4}$/', '', $filename));
    }

    public function sanitizeBasename(string $basename): string
    {
        $basename = trim($basename, '/');
        $basename = pathinfo($basename, PATHINFO_FILENAME) ?: $basename;

        return $this->extractBaseName($basename);
    }

    public function localFilesystemPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, '/storage/')) {
            $relative = substr($path, strlen('/storage/'));

            return Storage::path($relative);
        }

        if (str_starts_with($path, 'public/images/upload/')) {
            return Storage::path($path);
        }

        if (is_file($path)) {
            return $path;
        }

        return null;
    }
}
