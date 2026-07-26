<?php

namespace App\Services;

use App\Contracts\PageFragmentProvider;
use App\Services\Fragments\AirLocationFragmentService;
use App\Services\Fragments\ComboLocationFragmentService;
use App\Services\Fragments\HomeFragmentService;
use App\Services\Fragments\ServiceLocationFragmentService;
use App\Services\Fragments\ShipLocationFragmentService;
use App\Services\Fragments\TourContinentFragmentService;
use App\Services\Fragments\TourCountryFragmentService;
use InvalidArgumentException;

class PageFragmentRegistry
{
    /** @var list<string> */
    private const ALL_PAGE_TYPES = [
        TourLocationFragmentService::PAGE_TYPE,
        TourCountryFragmentService::PAGE_TYPE,
        TourContinentFragmentService::PAGE_TYPE,
        ComboLocationFragmentService::PAGE_TYPE,
        AirLocationFragmentService::PAGE_TYPE,
        ShipLocationFragmentService::PAGE_TYPE,
        ServiceLocationFragmentService::PAGE_TYPE,
        HomeFragmentService::PAGE_TYPE,
    ];

    /** @var list<string> */
    public const PAGE_TYPES = self::ALL_PAGE_TYPES;

    /** @return list<string> */
    public static function enabledPageTypes(): array
    {
        return array_values(array_filter(
            self::ALL_PAGE_TYPES,
            static fn (string $pageType): bool => fragment_type_enabled($pageType)
        ));
    }

    public function __construct(
        private readonly TourLocationFragmentService $tourLocation,
        private readonly TourCountryFragmentService $tourCountry,
        private readonly TourContinentFragmentService $tourContinent,
        private readonly ComboLocationFragmentService $comboLocation,
        private readonly AirLocationFragmentService $airLocation,
        private readonly ShipLocationFragmentService $shipLocation,
        private readonly ServiceLocationFragmentService $serviceLocation,
        private readonly HomeFragmentService $home,
    ) {}

    public function get(string $pageType): PageFragmentProvider
    {
        return match ($pageType) {
            TourLocationFragmentService::PAGE_TYPE       => $this->tourLocation,
            TourCountryFragmentService::PAGE_TYPE        => $this->tourCountry,
            TourContinentFragmentService::PAGE_TYPE      => $this->tourContinent,
            ComboLocationFragmentService::PAGE_TYPE      => $this->comboLocation,
            AirLocationFragmentService::PAGE_TYPE        => $this->airLocation,
            ShipLocationFragmentService::PAGE_TYPE       => $this->shipLocation,
            ServiceLocationFragmentService::PAGE_TYPE    => $this->serviceLocation,
            HomeFragmentService::PAGE_TYPE               => $this->home,
            default => throw new InvalidArgumentException('Unknown page fragment type: ' . $pageType),
        };
    }
}
