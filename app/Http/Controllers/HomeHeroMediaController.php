<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomeHeroBackground;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class HomeHeroMediaController extends Controller
{
    public function background(HomeHeroBackground $background): Response
    {
        $path = $background->gcs_path;
        if (empty($path)) {
            abort(404);
        }

        $disk = Storage::disk('gcs');
        if (!$disk->exists($path)) {
            abort(404);
        }

        $content = $disk->get($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mime = match ($extension) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return response($content, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
