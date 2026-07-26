<?php

declare(strict_types=1);

namespace App\Services\TourLocation;

use App\Models\Tour;
use App\Models\TourLocation;
use App\Services\Cards\TourCardMapper;
use App\Services\Island\IslandContextService;
use App\Services\TourLocationFragmentService;

class TourLocationPageService
{
    public function __construct(
        private readonly TourLocationFragmentService $fragments,
        private readonly IslandContextService $context,
        private readonly TourCardMapper $cards,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(TourLocation $item, string $locale): array
    {
        $name = $this->locationName($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'tours' => $this->toursSection($item, $name),
            'relatedServices' => app(TourLocationRelatedServicesBuilder::class)->forLocation($item, $name),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tourCardItems(TourLocation $item): array
    {
        $name = $this->locationName($item);

        return $this->fragments->toursForList($item)
            ->map(fn (Tour $tour) => $this->cards->fromTour($tour, $name))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(TourLocation $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_tour_list'),
            'title' => $seo->title ?? t('tour_list_title', ['name' => $name]),
            'tagline' => $this->context->excerpt(
                (string) ($seo->description ?? ''),
                strip_tags((string) t('tour_list_desc', ['name' => $name, 'brand' => config('main.name')])),
                160,
            ),
            'image' => $this->context->coverImage($seo, 'medium'),
            'imageAlt' => (string) ($seo->title ?? $name),
            'locationName' => $name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toursSection(TourLocation $item, string $name): array
    {
        return [
            'head' => [
                'eyebrow' => t('kicker_tour_list'),
                'title' => t('tour_list_title', ['name' => $name]),
                'desc' => strip_tags((string) t('tour_list_desc', ['name' => $name, 'brand' => config('main.name')])),
                'linkHref' => '#tours',
                'linkLabel' => '',
            ],
            'items' => $this->tourCardItems($item),
        ];
    }

    private function locationName(TourLocation $item): string
    {
        return (string) ($item->display_name ?: $item->name ?: island_name());
    }
}
