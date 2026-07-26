<?php

declare(strict_types=1);

namespace App\Services\Guide;

use App\Models\Guide;
use App\Services\Island\IslandContextService;
use App\Services\Listing\ListingRelatedServicesBuilder;

class GuidePageService
{
    public function __construct(
        private readonly IslandContextService $context,
        private readonly ListingRelatedServicesBuilder $related,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(Guide $item, string $locale): array
    {
        $name = $this->guideName($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'article' => $this->articleHead($item, $name),
            'relatedServices' => $this->related->forGuide($item, $name),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Guide $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_guide'),
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                'Lịch trình gợi ý, mẹo đi lại và ẩm thực — tổng hợp cho chuyến đi ' . island_name() . '.',
                160,
            ),
            'image' => $this->context->coverImage($seo, 'medium'),
            'imageAlt' => (string) ($seo->title ?? $name),
            'locationName' => $name,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function articleHead(Guide $item, string $name): array
    {
        return [
            'eyebrow' => t('kicker_guide'),
            'title' => $name,
            'lede' => $this->context->excerpt(
                (string) ($item->description ?? ''),
                'Lịch trình, kinh nghiệm đi lại và gợi ý ẩm thực — cập nhật cho chuyến đi ' . island_name() . '.',
                120,
            ),
        ];
    }

    private function guideName(Guide $item): string
    {
        return (string) ($item->display_name ?: $item->name ?: ($item->seo->title ?? island_name()));
    }
}
