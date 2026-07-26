<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomeHeroBackground;
use App\Services\Media\GcsMediaStorageService;
use Illuminate\Http\Response;

class HomeHeroMediaController extends Controller
{
    public function background(HomeHeroBackground $background, GcsMediaStorageService $storage): Response
    {
        $path = $background->gcs_path;
        if (empty($path) || !$storage->exists($path)) {
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
