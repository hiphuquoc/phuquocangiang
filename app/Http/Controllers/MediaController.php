<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Media\GcsMediaStorageService;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function gcs(string $path, GcsMediaStorageService $storage): Response
    {
        $path = $storage->normalizePath($path);

        if (!$storage->exists($path)) {
            abort(404);
        }

        $content = $storage->get($path);
        if ($content === null) {
            abort(404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mime = match ($extension) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return response($content, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
