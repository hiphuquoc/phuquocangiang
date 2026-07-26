<?php

namespace App\Services\Fragments;

use App\Contracts\PageFragmentProvider;
use App\Models\TourCountry;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;
use Illuminate\Support\Collection;

class TourCountryFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE       = 'tour-country';
    public const SECTION_TOURS   = 'tours';
    public const SECTION_AIR     = 'air';
    public const SECTION_SERVICE = 'service';

    public const SECTIONS = [
        self::SECTION_TOURS,
        self::SECTION_AIR,
        self::SECTION_SERVICE,
    ];

    public function pageType(): string
    {
        return self::PAGE_TYPE;
    }

    public function sections(): array
    {
        return self::SECTIONS;
    }

    public function fragmentUrl(int $seoId, string $section, ?string $locale = null): string
    {
        return $this->buildFragmentUrl(self::PAGE_TYPE, $seoId, $section, $locale);
    }

    public function loadBySeoId(int $seoId): ?TourCountry
    {
        return TourCountry::query()
            ->where('seo_id', $seoId)
            ->with([
                'seo',
                'tours.infoTourForeign' => fn ($q) => $q->where('status_show', 1),
                'airLocations.infoAirLocation.airs.seo',
                'airLocations.infoAirLocation.airs.location',
                'airLocations.infoAirLocation.airs.departure',
                'serviceLocations.infoServiceLocation.services.seo',
            ])
            ->first();
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(TourCountry $item, string $locale): array
    {
        $arrayPrice = [];
        foreach ($item->tours as $tour) {
            if (!empty($tour->infoTourForeign->price_show)) {
                $arrayPrice[] = (float) $tour->infoTourForeign->price_show;
            }
        }

        $currency = schema_currency($locale);

        return [
            'low'      => schema_price_amount(!empty($arrayPrice) ? min($arrayPrice) : 3000000, $currency),
            'high'     => schema_price_amount(!empty($arrayPrice) ? max($arrayPrice) : 5000000, $currency),
            'currency' => $currency,
        ];
    }

    public function toursForList(TourCountry $item): Collection
    {
        $data = new Collection();
        foreach ($item->tours as $tour) {
            if (!empty($tour->infoTourForeign)) {
                $data->push($tour->infoTourForeign);
            }
        }

        return $data;
    }

    public function airsForList(TourCountry $item): Collection
    {
        $data = new Collection();
        $i    = 0;
        foreach ($item->airLocations as $airLocation) {
            $infoLoc = $airLocation->infoAirLocation ?? null;
            if (empty($infoLoc->airs)) {
                continue;
            }
            foreach ($infoLoc->airs as $air) {
                $data[$i]            = $air;
                $data[$i]->seo       = $air->seo;
                $data[$i]->location  = $air->location;
                $data[$i]->departure = $air->departure;
                ++$i;
            }
        }

        return $data;
    }

    public function servicesForList(TourCountry $item): Collection
    {
        $data = new Collection();
        $i    = 0;
        foreach ($item->serviceLocations as $serviceLocation) {
            $infoLoc = $serviceLocation->infoServiceLocation ?? null;
            if (empty($infoLoc->services)) {
                continue;
            }
            foreach ($infoLoc->services as $service) {
                $data[$i]      = $service;
                $data[$i]->seo = $service->seo;
                ++$i;
            }
        }

        return $data;
    }

    public function render(string $section, object $item): ?string
    {
        if (!$item instanceof TourCountry || !in_array($section, self::SECTIONS, true)) {
            return null;
        }

        return match ($section) {
            self::SECTION_TOURS => view('main.tourCountry.fragments.tours', [
                'item' => $item,
                'list' => $this->toursForList($item),
            ])->render(),
            self::SECTION_AIR => view('main.tourLocation.fragments.air', [
                'item' => $item,
                'list' => $this->airsForList($item),
            ])->render(),
            self::SECTION_SERVICE => view('main.tourLocation.fragments.service', [
                'item' => $item,
                'list' => $this->servicesForList($item),
            ])->render(),
            default => null,
        };
    }
}
