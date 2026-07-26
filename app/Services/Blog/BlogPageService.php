<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Models\Blog;
use App\Models\Category;
use App\Services\Cards\BlogCardMapper;
use App\Services\Island\IslandContextService;
use App\Services\Island\IslandNavigationService;

class BlogPageService
{
    public function __construct(
        private readonly IslandContextService $context,
        private readonly BlogCardMapper $cards,
        private readonly IslandNavigationService $nav,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(
        Blog $item,
        ?Category $parent,
        iterable $related,
        string $locale,
    ): array {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));

        return [
            'banner' => $this->banner($item, $name, $locale),
            'article' => $this->articleHead($item, $name, $parent),
            'related' => $this->relatedSection($related),
            'sidebar' => [
                'categories' => $this->nav->blogCategoryLinks(),
                'parent' => $parent ? [
                    'label' => (string) ($parent->name ?: ($parent->seo->title ?? '')),
                    'href' => seo_url($parent->seo),
                ] : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Blog $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => 'Blog & tin tức',
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                'Chia sẻ kinh nghiệm và cẩm nang cho chuyến đi ' . island_name() . '.',
                160,
            ),
            'image' => $this->context->coverImage($seo, 'large'),
            'imageAlt' => $name,
            'locationName' => $name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function articleHead(Blog $item, string $name, ?Category $parent): array
    {
        $seo = $item->seo;
        $updatedAt = !empty($seo->updated_at)
            ? date('d/m/Y', strtotime((string) $seo->updated_at))
            : null;
        $updatedAtIso = !empty($seo->updated_at)
            ? date('Y-m-d', strtotime((string) $seo->updated_at))
            : null;

        return [
            'eyebrow' => 'Blog & tin tức',
            'title' => $name,
            'lede' => $this->context->excerpt(
                (string) ($item->description ?? ''),
                '',
                160,
            ),
            'date' => $updatedAt,
            'dateIso' => $updatedAtIso,
            'author' => (string) config('main.name', 'Superdong'),
            'category' => $parent ? [
                'label' => (string) ($parent->name ?: ($parent->seo->title ?? '')),
                'href' => seo_url($parent->seo),
            ] : null,
            'cover' => [
                'src' => $this->context->coverImage($seo, 'large'),
                'alt' => $name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function relatedSection(iterable $related): array
    {
        $items = [];
        foreach ($related as $blog) {
            $card = $this->cards->fromBlog($blog);
            if ($card !== null) {
                $items[] = $card;
            }
        }

        return [
            'head' => [
                'eyebrow' => 'Blog',
                'title' => t('blog_related'),
                'desc' => '',
            ],
            'items' => $items,
        ];
    }
}
