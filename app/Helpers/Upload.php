<?php

namespace App\Helpers;

use App\Services\Media\GcsMediaStorageService;

/**
 * Facade upload ảnh CMS — mọi ảnh đều lưu Google Cloud Storage qua GcsMediaStorageService.
 */
class Upload {
    private static function storage(): GcsMediaStorageService
    {
        return app(GcsMediaStorageService::class);
    }

    /**
     * Upload ảnh đại diện SEO — 3 phiên bản gốc / -small / -medium.
     *
     * @return array{filePathNormal: string, filePathSmall: string, filePathMedium: string}
     */
    public static function uploadThumnail($requestImage, $name = null){
        $result = [];
        if (!empty($requestImage)) {
            $name = $name ?? (string) time();
            $set = self::storage()->uploadImageSet($requestImage, $name);
            $result['filePathNormal'] = $set['original'];
            $result['filePathSmall'] = $set['small'];
            $result['filePathMedium'] = $set['medium'];
        }

        return $result;
    }

    public static function uploadAvatar($requestImage, $name = null){
        $result = null;
        if (!empty($requestImage)) {
            $storage = self::storage();
            $extension = config('admin.images.extension');
            $name = $name ?? (string) time();
            $fileName = $name . '-avatar-500x500.' . $extension;
            $path = $storage->buildUploadPath($fileName);
            $storage->putResized($requestImage, $path, 500, 500, $extension);
            $result = $path;
        }

        return $result;
    }

    public static function uploadLogo($requestImage, $name = null){
        $result = null;
        if (!empty($requestImage)) {
            $storage = self::storage();
            $extension = config('admin.images.extension');
            $name = $name ?? (string) time();
            $filename = $name . '-logo-660.' . $extension;
            $path = $storage->buildUploadPath($filename);
            $storage->putResized($requestImage, $path, 660, 660, $extension);
            $result = $path;
        }

        return $result;
    }

    public static function uploadCustom($requestImage, $name = null){
        $result = null;
        if (!empty($requestImage)) {
            $name = $name ?? (string) time();
            $set = self::storage()->uploadImageSet($requestImage, $name);
            $result['filePathNormal'] = $set['original'];
            $result['filePathSmall'] = $set['small'];
            $result['filePathMedium'] = $set['medium'];
        }

        return $result;
    }
}
