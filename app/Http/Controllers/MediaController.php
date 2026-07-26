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

        return response($content, 200, [
            'Content-Type' => $storage->mimeForPath($path),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
