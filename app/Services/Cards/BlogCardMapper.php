<?php

declare(strict_types=1);

namespace App\Services\Cards;

use App\Models\Blog;
use App\Services\Island\IslandContextService;

class BlogCardMapper
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function fromBlog(Blog $blog): ?array
    {
        $seo = $blog->seo;
        if (!$seo) {
            return null;
        }

        $title = (string) ($blog->name ?: ($seo->title ?? ''));
        $desc = $this->context->excerpt(
            (string) ($blog->description ?: $seo->description ?? ''),
            '',
            140,
        );

        $updatedAt = !empty($seo->updated_at)
            ? date('d/m/Y', strtotime((string) $seo->updated_at))
            : null;

        return [
            'image' => $this->context->coverImage($seo, 'small'),
            'alt' => $title,
            'title' => $title,
            'desc' => $desc !== '' ? $desc : null,
            'date' => $updatedAt,
            'author' => (string) config('main.name', 'Superdong'),
            'ctaHref' => seo_url($seo),
            'highlight' => !empty($blog->highlight_tag) ? (string) $blog->highlight_tag : null,
        ];
    }
}
