<?php

declare(strict_types=1);

namespace App\Services\HotelLocation;

use App\Models\Hotel;
use App\Models\HotelLocation;
use App\Services\Cards\HotelCardMapper;
use App\Services\Island\IslandContextService;
use App\Services\Listing\ListingRelatedServicesBuilder;

class HotelLocationPageService
{
    public function __construct(
        private readonly IslandContextService $context,
        private readonly HotelCardMapper $cards,
        private readonly ListingRelatedServicesBuilder $related,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(HotelLocation $item, string $locale): array
    {
        $name = $this->locationName($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'hotels' => $this->hotelsSection($item, $name),
            'relatedServices' => $this->related->forHotelLocation($item, $name),
        ];
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(HotelLocation $item, string $locale): array
    {
        $arrayPrice = [];

        foreach ($this->hotelsForList($item) as $hotel) {
            foreach ($hotel->rooms ?? [] as $room) {
                foreach ($room->prices ?? [] as $price) {
                    if (!empty($price->price)) {
                        $arrayPrice[] = (float) $price->price;
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
    private function banner(HotelLocation $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_hotel'),
            'title' => $seo->title ?? t('hotel_location_title', ['name' => $name, 'count' => '']),
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                strip_tags((string) t('hotel_location_desc', ['name' => $name])),
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
    private function hotelsSection(HotelLocation $item, string $name): array
    {
        $items = $this->hotelCardItems($item, $name);

        return [
            'head' => [
                'eyebrow' => t('kicker_hotel'),
                'title' => t('hotel_location_title', [
                    'name' => $name,
                    'count' => '<span class="sd-text-grad">' . count($items) . '</span>',
                ]),
                'desc' => strip_tags((string) t('hotel_location_desc', ['name' => $name])),
            ],
            'filters' => $this->filterTypes($items),
            'items' => $items,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function hotelCardItems(HotelLocation $item, string $name = ''): array
    {
        $locationName = $name !== '' ? $name : $this->locationName($item);

        return $this->hotelsForList($item)
            ->map(fn (Hotel $hotel) => $this->cards->fromHotel($hotel, $locationName))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{slug: string, label: string}>
     */
    private function filterTypes(array $items): array
    {
        $labels = [];

        foreach ($items as $card) {
            $tags = explode(' ', (string) ($card['filterHotel'] ?? ''));
            foreach ($tags as $tag) {
                if ($tag === '' || $tag === 'tat-ca-khach-san') {
                    continue;
                }
                if (!isset($labels[$tag])) {
                    $labels[$tag] = ucfirst(str_replace('-', ' ', $tag));
                }
            }
        }

        $filters = [];
        foreach ($labels as $slug => $label) {
            $filters[] = ['slug' => $slug, 'label' => $label];
        }

        return $filters;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Hotel>
     */
    private function hotelsForList(HotelLocation $item)
    {
        return ($item->hotels ?? collect())
            ->filter(fn (Hotel $hotel) => !empty($hotel->seo) && $this->cards->hasRooms($hotel));
    }

    private function locationName(HotelLocation $item): string
    {
        return (string) ($item->display_name ?: $item->name ?: island_name());
    }
}
