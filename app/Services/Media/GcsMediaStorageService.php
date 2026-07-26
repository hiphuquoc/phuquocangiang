<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;

class GcsMediaStorageService
{
    public function diskName(): string
    {
        return (string) config('media.disk', 'gcs');
    }

    public function uploadPrefix(): string
    {
        return trim((string) config('media.upload_prefix', 'media/uploads'), '/');
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

    public function isCloudPath(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return false;
        }

        if (str_starts_with($path, '/storage') || str_starts_with($path, '/images')) {
            return false;
        }

        $prefix = $this->uploadPrefix();

        return str_starts_with($path, $prefix . '/')
            || str_starts_with($path, 'hotels/')
            || str_starts_with($path, 'hero/')
            || str_starts_with($path, 'island-gallery/');
    }

    /**
     * Upload 3 phiên bản: gốc, -small (450px), -medium (800px).
     *
     * @return array{original: string, small: string, medium: string}
     */
    public function uploadImageSet(UploadedFile $file, string $basename, ?string $extension = null): array
    {
        $extension ??= $this->extension();
        $basename = $this->sanitizeBasename($basename);

        $originalPath = $this->buildUploadPath("{$basename}.{$extension}");
        $smallPath = $this->buildUploadPath("{$basename}-small.{$extension}");
        $mediumPath = $this->buildUploadPath("{$basename}-medium.{$extension}");

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

    /**
     * Resolve path biến thể từ path gốc hoặc path bất kỳ trong set.
     */
    public function resolveVariantPath(?string $storedPath, string $variant = 'original'): ?string
    {
        if ($storedPath === null || $storedPath === '') {
            return null;
        }

        if (!$this->isCloudPath($storedPath) && !str_starts_with($storedPath, '/storage')) {
            return $storedPath;
        }

        $normalized = $this->normalizePath($storedPath);
        $dir = dirname($normalized);
        $dir = $dir === '.' ? '' : $dir . '/';
        $ext = pathinfo($normalized, PATHINFO_EXTENSION) ?: $this->extension();
        $base = $this->extractBaseName($normalized);

        if ($variant === 'original') {
            $original = $dir . $base . '.' . $ext;
            if ($this->exists($original)) {
                return $original;
            }

            return $normalized;
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

        $original = $dir . $base . '.' . $ext;

        return $this->exists($original) ? $original : $normalized;
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
        if ($this->isCloudPath($path)) {
            return Storage::disk($this->diskName())->exists($this->normalizePath($path));
        }

        $local = $this->localFilesystemPath($path);

        return $local !== null && is_file($local);
    }

    public function get(string $path): ?string
    {
        if (!$this->exists($path)) {
            return null;
        }

        if ($this->isCloudPath($path)) {
            return Storage::disk($this->diskName())->get($this->normalizePath($path));
        }

        $local = $this->localFilesystemPath($path);

        return $local !== null ? (string) file_get_contents($local) : null;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if ($this->isCloudPath($path)) {
            try {
                Storage::disk($this->diskName())->delete($this->normalizePath($path));
            } catch (\Throwable) {
                // Best-effort cleanup.
            }

            return;
        }

        $local = $this->localFilesystemPath($path);
        if ($local !== null && is_file($local)) {
            @unlink($local);
        }
    }

    /**
     * @return array<int, string> GCS object paths
     */
    public function listUploads(?string $search = null): array
    {
        $prefix = $this->uploadPrefix() . '/';
        $files = Storage::disk($this->diskName())->files($prefix);

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
        return $this->uploadPrefix() . '/' . ltrim($filename, '/');
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
