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

        $folderPath = self::FOLDER . '/' . $basename;
        $extension = $this->media->extension();

        $originalPath = $folderPath . '.' . $extension;
        $smallPath = $folderPath . '-small.' . $extension;
        $mediumPath = $folderPath . '-medium.' . $extension;

        $this->media->putOriginal($file, $originalPath, $extension);
        $this->media->putResized($file, $smallPath, 450, null, $extension);
        $this->media->putResized($file, $mediumPath, 800, null, $extension);

        return [
            'original' => $originalPath,
            'small' => $smallPath,
            'medium' => $mediumPath,
        ];
    }

    public function deletePhoto(?string $gcsPath): void
    {
        $this->media->deleteImageSet($gcsPath);
    }
}

