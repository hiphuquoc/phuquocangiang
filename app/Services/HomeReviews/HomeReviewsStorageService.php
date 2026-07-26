<?php

declare(strict_types=1);

namespace App\Services\HomeReviews;

use App\Services\Media\GcsMediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class HomeReviewsStorageService
{
    private const FOLDER = 'review-avatars';

    public function __construct(
        private readonly GcsMediaStorageService $media,
    ) {}

    public function uploadAvatar(UploadedFile $file, ?string $basename = null): string
    {
        $basename = $basename ?: 'avatar';
        $basename = Str::slug($basename) ?: 'avatar';
        $basename = $basename . '-' . now()->format('YmdHis') . '-' . Str::random(4);

        $extension = $this->media->extension();
        $objectPath = $this->media->buildPath(self::FOLDER, $basename . '-small.' . $extension);

        $this->media->putResized($file, $objectPath, 120, 120, $extension);

        return $objectPath;
    }

    public function deleteAvatar(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $this->media->delete($path);
    }
}
