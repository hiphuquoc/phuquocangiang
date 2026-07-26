<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Models\Blog;
use App\Models\Category;
use App\Services\Cards\BlogCardMapper;
use App\Services\Island\IslandContextService;
use App\Services\Island\IslandNavigationService;

class CategoryPageService
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
        Category $item,
        iterable $blogs,
        iterable $sidebarCategories,
        string $locale,
    ): array {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));

        return [
            'banner' => $this->banner($item, $name, $locale),
            'posts' => $this->postsSection($blogs),
            'sidebar' => [
                'categories' => $this->nav->blogCategoryLinks(),
            ],
        ];
    }

    /**
     * @param  iterable<int, Category&object{childs?: mixed}>  $groups
     * @return array<string, mixed>
     */
    public function forParentPage(
        Category $item,
        iterable $groups,
        string $locale,
    ): array {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));
        $sections = [];

        foreach ($groups as $group) {
            $posts = [];
            foreach ($group->childs ?? [] as $blog) {
                $card = $this->cards->fromBlog($blog);
                if ($card !== null) {
                    $posts[] = $card;
                }
            }

            $sections[] = [
                'title' => (string) ($group->name ?: ($group->seo->title ?? '')),
                'href' => seo_url($group->seo),
                'items' => $posts,
            ];
        }

        return [
            'banner' => $this->banner($item, $name, $locale),
            'sections' => $sections,
            'sidebar' => [
                'categories' => $this->nav->blogCategoryLinks(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Category $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => 'Blog & tin tức',
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                'Cẩm nang, kinh nghiệm và tin tức du lịch ' . island_name() . '.',
                160,
            ),
            'image' => $this->context->coverImage($seo, 'medium'),
            'imageAlt' => $name,
            'locationName' => $name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function postsSection(iterable $blogs): array
    {
        $items = [];
        foreach ($blogs as $blog) {
            $card = $this->cards->fromBlog($blog);
            if ($card !== null) {
                $items[] = $card;
            }
        }

        return [
            'head' => [
                'eyebrow' => 'Blog & tin tức',
                'title' => 'Bài viết mới nhất',
                'desc' => '',
            ],
            'count' => count($items),
            'items' => $items,
        ];
    }
}
