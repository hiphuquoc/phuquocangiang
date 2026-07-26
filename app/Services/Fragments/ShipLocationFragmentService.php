<?php

namespace App\Services\Fragments;

use App\Contracts\PageFragmentProvider;
use App\Models\ShipLocation;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;

class ShipLocationFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE = 'ship-location';
    public const SECTION_LIST = 'ships';

    public const SECTIONS = [self::SECTION_LIST];

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

    public function loadBySeoId(int $seoId): ?ShipLocation
    {
        return ShipLocation::query()
            ->where('seo_id', $seoId)
            ->with([
                'seo',
                'ships.seo',
                'ships.prices',
                'district',
            ])
            ->first();
    }

    public function render(string $section, object $item): ?string
    {
        if (!$item instanceof ShipLocation || $section !== self::SECTION_LIST) {
            return null;
        }

        return view('main.shipLocation.fragments.ships', [
            'item' => $item,
            'list' => $item->ships,
        ])->render();
    }
}
