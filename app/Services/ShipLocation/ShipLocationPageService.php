<?php

declare(strict_types=1);

namespace App\Services\ShipLocation;

use App\Models\ShipLocation;
use App\Services\Island\HomeIslandFerryService;
use App\Services\Island\IslandContextService;
use App\Services\Listing\ListingRelatedServicesBuilder;

class ShipLocationPageService
{
    public function __construct(
        private readonly IslandContextService $context,
        private readonly HomeIslandFerryService $ferry,
        private readonly ListingRelatedServicesBuilder $related,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(ShipLocation $item, string $locale): array
    {
        $name = $this->locationName($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'routes' => $this->routesSection($item, $name),
            'relatedServices' => $this->related->forShipLocation($item, $name),
        ];
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(ShipLocation $item, string $locale): array
    {
        $arrayPrice = [];
        foreach ($item->ships ?? [] as $ship) {
            foreach ($ship->prices ?? [] as $price) {
                foreach (['price_adult', 'price_child', 'price_old', 'price_vip'] as $field) {
                    if (!empty($price->{$field})) {
                        $arrayPrice[] = (float) $price->{$field};
                    }
                }
            }
        }

        $currency = schema_currency($locale);

        return [
            'low' => schema_price_amount(!empty($arrayPrice) ? min($arrayPrice) : 3000000, $currency),
            'high' => schema_price_amount(!empty($arrayPrice) ? max($arrayPrice) : 5000000, $currency),
            'currency' => $currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(ShipLocation $item, string $name, string $locale): array
    {
        $seo = $item->seo;
        $districtSuffix = !empty($item->district?->district_name)
            ? t('ship_location_suffix', ['district' => $item->district->district_name])
            : '';

        return [
            'kicker' => t('kicker_ship'),
            'title' => $seo->title ?? ($name . ($districtSuffix !== '' ? ' - ' . $districtSuffix : '')),
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                strip_tags((string) ($item->description ?? '')),
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
    private function routesSection(ShipLocation $item, string $name): array
    {
        return [
            'head' => [
                'eyebrow' => t('kicker_ship'),
                'title' => t('ship_detail_title', ['name' => $name]),
                'desc' => strip_tags((string) ($item->description ?: t('ship_schedule_intro', ['brand' => config('main.name')]))),
            ],
            'items' => $this->ferry->shipCardsForLocation($item, $name),
        ];
    }

    private function locationName(ShipLocation $item): string
    {
        return (string) ($item->display_name ?: $item->name ?: island_name());
    }
}
