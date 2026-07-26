<?php

namespace App\Services\Fragments;

use App\Contracts\PageFragmentProvider;
use App\Models\AirLocation;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;

class AirLocationFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE = 'air-location';
    public const SECTION_LIST = 'airs';

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

    public function loadBySeoId(int $seoId): ?AirLocation
    {
        return AirLocation::query()
            ->where('seo_id', $seoId)
            ->with([
                'seo',
                'airs.seo',
                'airs.location',
                'airs.departure',
                'airs.partners.infoPartner',
            ])
            ->first();
    }

    public function render(string $section, object $item): ?string
    {
        if (!$item instanceof AirLocation || $section !== self::SECTION_LIST) {
            return null;
        }

        return view('main.airLocation.fragments.airs', [
            'item' => $item,
            'list' => $item->airs,
        ])->render();
    }
}
