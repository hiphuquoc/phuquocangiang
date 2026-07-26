<?php

declare(strict_types=1);

namespace App\Services\ServiceLocation;

use App\Models\Service;
use App\Models\ServiceLocation;
use App\Services\Cards\ServiceCardMapper;
use App\Services\Fragments\ServiceLocationFragmentService;
use App\Services\Island\IslandContextService;
use App\Services\Listing\ListingRelatedServicesBuilder;

class ServiceLocationPageService
{
    public function __construct(
        private readonly ServiceLocationFragmentService $fragments,
        private readonly IslandContextService $context,
        private readonly ServiceCardMapper $cards,
        private readonly ListingRelatedServicesBuilder $related,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(ServiceLocation $item, string $locale): array
    {
        $name = $this->locationName($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'services' => $this->servicesSection($item, $name),
            'relatedServices' => $this->related->forServiceLocation($item, $name),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function serviceCardItems(ServiceLocation $item): array
    {
        return $this->fragments->servicesForList($item)
            ->map(fn (Service $service) => $this->cards->fromService($service))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(ServiceLocation $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_entertainment'),
            'title' => $seo->title ?? t('service_location_title', ['name' => $name]),
            'tagline' => $this->context->excerpt(
                (string) ($seo->description ?? ''),
                strip_tags((string) t('service_location_desc', ['name' => $name, 'brand' => config('main.name')])),
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
    private function servicesSection(ServiceLocation $item, string $name): array
    {
        return [
            'head' => [
                'eyebrow' => t('kicker_entertainment'),
                'title' => t('service_location_title', ['name' => $name]),
                'desc' => strip_tags((string) t('service_location_desc', ['name' => $name, 'brand' => config('main.name')])),
            ],
            'items' => $this->serviceCardItems($item),
        ];
    }

    private function locationName(ServiceLocation $item): string
    {
        return (string) ($item->display_name ?: $item->name ?: island_name());
    }
}
