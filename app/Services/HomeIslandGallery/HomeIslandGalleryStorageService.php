<?php

declare(strict_types=1);

namespace App\Services\HomeIslandGallery;

use App\Services\Media\GcsMediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class HomeIslandGalleryStorageService
{
    private const FOLDER = 'island-gallery';

    public function __construct(
        private readonly GcsMediaStorageService $media,
    ) {}

    /**
     * @return array{original: string, small: string, medium: string}
     */
    public function uploadPhoto(UploadedFile $file, ?string $basename = null): array
    {
        $basename = $basename ?: 'photo';
        $basename = Str::slug($basename) ?: 'photo';
        $basename = $basename . '-' . now()->format('YmdHis') . '-' . Str::random(4);

        return $this->media->uploadImageSet($file, $basename, null, self::FOLDER);
    }

    public function deletePhoto(?string $gcsPath): void
    {
        $this->media->deleteImageSet($gcsPath);
    }
}
