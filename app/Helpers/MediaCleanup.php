<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Seo;
use App\Services\Media\GcsMediaStorageService;

class MediaCleanup
{
    public static function deletePath(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        app(GcsMediaStorageService::class)->deleteImageSet($path);
    }

    public static function deleteSeoImages(?Seo $seo): void
    {
        if ($seo === null) {
            return;
        }

        $image = $seo->getRawOriginal('image');
        if (!empty($image)) {
            self::deletePath($image);
            return;
        }

        self::deletePath($seo->getRawOriginal('image_small'));
    }
}
