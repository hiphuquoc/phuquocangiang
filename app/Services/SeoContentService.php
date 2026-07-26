<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoContentTranslation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Đồng bộ body content giữa admin form, DB (seo_content_translations) và file legacy.
 *
 * Frontend (RoutingController::renderContentBlade) ưu tiên DB → file.
 * Admin trước đây chỉ đọc file nên textarea trống khi nội dung đã nằm trong DB.
 */
class SeoContentService
{
    public function loadForAdmin(?Seo $seo, ?string $seoType = null, ?string $locale = null): string
    {
        if (!$seo || empty($seo->id)) {
            return '';
        }

        $locale ??= config('language.default_code', 'vi');

        if (Schema::hasTable('seo_content_translations')) {
            $body = SeoContentTranslation::getContent((int) $seo->id, $locale);
            if (is_string($body) && $body !== '') {
                return $body;
            }
        }

        $type = $seoType ?? (string) ($seo->type ?? '');
        $slug = (string) ($seo->getRawOriginal('slug') ?? $seo->slug ?? '');
        if ($type === '' || $slug === '') {
            return '';
        }

        return $this->readLegacyFile($type, $slug, $locale) ?? '';
    }

    public function persistForAdmin(Seo $seo, string $slug, ?string $content, ?string $locale = null): void
    {
        $content ??= '';
        $locale ??= config('language.default_code', 'vi');

        if (Schema::hasTable('seo_content_translations')) {
            $language = Language::byCode($locale);
            if ($language) {
                SeoContentTranslation::updateOrCreate(
                    [
                        'seo_id' => $seo->id,
                        'language_id' => $language->id,
                    ],
                    [
                        'content' => $content,
                        'status' => 'published',
                        'translated_by' => 'admin-form',
                    ],
                );
            }
        }

        $type = (string) ($seo->type ?? '');
        if ($type === '' || $slug === '') {
            return;
        }

        $path = $this->legacyFilePath($type, $slug);
        if ($path === null) {
            return;
        }

        $this->writeLegacyFile($path, $content);
    }

    private function readLegacyFile(string $type, string $slug, ?string $locale = null): ?string
    {
        $locale ??= config('language.default_code', 'vi');
        $defaultCode = config('language.default_code', 'vi');
        $dir = $this->contentDir($type);
        if ($dir === null) {
            return null;
        }

        $candidates = [
            rtrim($dir, '/') . '/' . $slug . '.' . $locale . '.blade.php',
        ];
        if ($locale !== $defaultCode) {
            $candidates[] = rtrim($dir, '/') . '/' . $slug . '.' . $defaultCode . '.blade.php';
        }
        $candidates[] = rtrim($dir, '/') . '/' . $slug . '.blade.php';

        $disks = array_values(array_unique([
            'local',
            (string) config('filesystems.default', 'local'),
        ]));

        foreach ($disks as $diskName) {
            $disk = Storage::disk($diskName);
            foreach ($candidates as $path) {
                if (!$disk->exists($path)) {
                    continue;
                }

                $raw = $disk->get($path);
                if (is_string($raw) && $raw !== '') {
                    return $raw;
                }
            }
        }

        return null;
    }

    private function contentDir(string $type): ?string
    {
        $map = [
            'ship_schedule' => 'public/contents/shipSchedule/',
        ];

        if (isset($map[$type])) {
            return $map[$type];
        }

        $cfg = config('tablemysql.' . $type);
        $dir = $cfg['content_dir'] ?? null;

        return is_string($dir) && $dir !== '' ? $dir : null;
    }

    private function legacyFilePath(string $type, string $slug): ?string
    {
        $dir = $this->contentDir($type);
        if ($dir === null) {
            return null;
        }

        return rtrim($dir, '/') . '/' . ltrim($slug, '/') . '.blade.php';
    }

    private function writeLegacyFile(string $path, string $content): void
    {
        Storage::disk('local')->put($path, $content);

        $defaultDisk = (string) config('filesystems.default', 'local');
        if ($defaultDisk !== 'local') {
            Storage::disk($defaultDisk)->put($path, $content);
        }
    }
}
