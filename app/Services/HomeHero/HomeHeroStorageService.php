<?php

declare(strict_types=1);

namespace App\Services\HomeHero;

use App\Services\Media\GcsMediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic;

class HomeHeroStorageService
{
    private const FOLDER = 'hero/backgrounds';
    private const MAX_WIDTH = 2400;

    public function __construct(
        private readonly GcsMediaStorageService $media,
    ) {}

    public function uploadBackground(UploadedFile $file, string $basename = 'hero'): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $encodeAs = in_array($extension, ['png', 'webp'], true) ? $extension : 'jpg';
        $filename = sprintf(
            '%s/%s-%s.%s',
            self::FOLDER,
            Str::slug($basename) ?: 'hero',
            now()->format('YmdHis') . '-' . Str::random(6),
            $encodeAs
        );

        $image = ImageManagerStatic::make($file->getRealPath())->orientate();
        if ($image->width() > self::MAX_WIDTH) {
            $image->resize(self::MAX_WIDTH, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $binary = (string) $image->encode($encodeAs, $encodeAs === 'png' ? 90 : 85);
        $path = $this->media->put($filename, $binary, ['visibility' => 'public']);

        return [
            'gcs_path' => $path,
            'public_url' => $this->media->publicUrl($path),
        ];
    }

    public function deleteBackground(?string $gcsPath): void
    {
        $this->media->delete($gcsPath);
    }
}
