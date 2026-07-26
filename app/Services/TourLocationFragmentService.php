<?php

namespace App\Services;

use App\Contracts\PageFragmentProvider;
use App\Models\TourLocation;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;
use Illuminate\Support\Collection;

/**
 * Load & render HTML fragments cho trang tour_location (giá theo currency hiện tại).
 * Shell trang được cache không phụ thuộc currency; các section có giá gọi qua AJAX.
 */
class TourLocationFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE = 'tour-location';
    public const SECTION_TOURS   = 'tours';
    public const SECTION_COMBO   = 'combo';
    public const SECTION_AIR     = 'air';
    public const SECTION_SERVICE = 'service';
    public const SECTION_SHIP    = 'ship';

  /** @var list<string> */
    public const SECTIONS = [
        self::SECTION_TOURS,
        self::SECTION_COMBO,
        self::SECTION_AIR,
        self::SECTION_SERVICE,
        self::SECTION_SHIP,
    ];

    public function loadBySeoId(int $seoId): ?TourLocation
    {
        return TourLocation::query()
            ->where('seo_id', $seoId)
            ->with([
                'seo',
                'tours.infoTour' => fn ($q) => $q->where('status_show', 1),
                'tours.infoTour.seo',
                'comboLocations.infoComboLocation.combos.infoCombo.seo',
                'comboLocations.infoComboLocation.combos.infoCombo.location',
                'comboLocations.infoComboLocation.combos.infoCombo.departure',
                'comboLocations.infoComboLocation.seo',
                'airLocations.infoAirLocation.airs.seo',
                'airLocations.infoAirLocation.airs.location',
                'airLocations.infoAirLocation.airs.departure',
                'airLocations.infoAirLocation.seo',
                'serviceLocations.infoServiceLocation.services.seo',
                'serviceLocations.infoServiceLocation.seo',
                'shipLocations.infoShipLocation.ships.seo',
                'shipLocations.infoShipLocation.seo',
            ])
            ->first();
    }

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

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(TourLocation $item, string $locale): array
    {
        $arrayPrice = [];
        foreach ($item->tours as $tour) {
            if (!empty($tour->infoTour->price_show)) {
                $arrayPrice[] = (float) $tour->infoTour->price_show;
            }
        }

        $lowVnd  = !empty($arrayPrice) ? min($arrayPrice) : 3000000;
        $highVnd = !empty($arrayPrice) ? max($arrayPrice) : 5000000;
        $currency = schema_currency($locale);

        return [
            'low'      => schema_price_amount($lowVnd, $currency),
            'high'     => schema_price_amount($highVnd, $currency),
            'currency' => $currency,
        ];
    }

    public function toursForList(TourLocation $item): Collection
    {
        $data = new Collection();
        foreach ($item->tours as $tour) {
            if (!empty($tour->infoTour)) {
                $data->push($tour->infoTour);
            }
        }

        return $data;
    }

    public function combosForList(TourLocation $item): Collection
    {
        $data = new Collection();
        $i    = 0;
        foreach ($item->comboLocations as $comboLocation) {
            $infoLoc = $comboLocation->infoComboLocation ?? null;
            if (empty($infoLoc->combos)) {
                continue;
            }
            foreach ($infoLoc->combos as $combo) {
                if (empty($combo->infoCombo)) {
                    continue;
                }
                $row               = $combo->infoCombo;
                $row->seo          = $combo->infoCombo->seo;
                $row->location     = $combo->infoCombo->location;
                $row->departure    = $combo->infoCombo->departure;
                $data[$i]          = $row;
                ++$i;
            }
        }

        return $data;
    }

    public function airsForList(TourLocation $item): Collection
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

    public function servicesForList(TourLocation $item): Collection
    {
        $data = new Collection();
        $i    = 0;
        foreach ($item->serviceLocations as $serviceLocation) {
            $infoLoc = $serviceLocation->infoServiceLocation ?? null;
            if (empty($infoLoc->services)) {
                continue;
            }
            foreach ($infoLoc->services as $service) {
                $data[$i]       = $service;
                $data[$i]->seo  = $service->seo;
                ++$i;
            }
        }

        return $data;
    }

    public function shipsForList(TourLocation $item): Collection
    {
        $data = new Collection();
        foreach ($item->shipLocations as $shipLocation) {
            if (!empty($shipLocation->infoShipLocation->ships)) {
                $data = $data->merge($shipLocation->infoShipLocation->ships);
            }
        }

        return $data;
    }

    public function render(string $section, object $item): ?string
    {
        if (!$item instanceof TourLocation) {
            return null;
        }

        return $this->renderSection($section, $item);
    }

    protected function renderSection(string $section, TourLocation $item): ?string
    {
        if (!in_array($section, self::SECTIONS, true)) {
            return null;
        }

        return match ($section) {
            self::SECTION_TOURS   => $this->renderTours($item),
            self::SECTION_COMBO   => $this->renderCombo($item),
            self::SECTION_AIR     => $this->renderAir($item),
            self::SECTION_SERVICE => $this->renderService($item),
            self::SECTION_SHIP    => $this->renderShip($item),
            default               => null,
        };
    }

    protected function renderTours(TourLocation $item): string
    {
        if (config('modules.use_tour_location_v2')) {
            $name = (string) ($item->display_name ?: $item->name ?: island_name());
            $mapper = app(\App\Services\Cards\TourCardMapper::class);
            $items = $this->toursForList($item)
                ->map(fn ($tour) => $mapper->fromTour($tour, $name))
                ->filter()
                ->values()
                ->all();

            return view('main.tourLocation.fragments.tours-v2', [
                'item' => $item,
                'items' => $items,
            ])->render();
        }

        $list = $this->toursForList($item);

        return view('main.tourLocation.fragments.tours', [
            'item' => $item,
            'list' => $list,
        ])->render();
    }

    protected function renderCombo(TourLocation $item): string
    {
        return view('main.tourLocation.fragments.combo', [
            'item' => $item,
            'list' => $this->combosForList($item),
        ])->render();
    }

    protected function renderAir(TourLocation $item): string
    {
        return view('main.tourLocation.fragments.air', [
            'item' => $item,
            'list' => $this->airsForList($item),
        ])->render();
    }

    protected function renderService(TourLocation $item): string
    {
        return view('main.tourLocation.fragments.service', [
            'item' => $item,
            'list' => $this->servicesForList($item),
        ])->render();
    }

    protected function renderShip(TourLocation $item): string
    {
        return view('main.tourLocation.fragments.ship', [
            'item' => $item,
            'list' => $this->shipsForList($item),
        ])->render();
    }
}
