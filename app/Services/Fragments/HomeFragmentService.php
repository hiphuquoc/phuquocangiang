<?php

namespace App\Services\Fragments;

use App\Contracts\PageFragmentProvider;
use App\Models\ServiceLocation;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;
use Illuminate\Support\Collection;

class HomeFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE = 'home';
    public const SECTION_SERVICES = 'services';

    public const SECTIONS = [self::SECTION_SERVICES];

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

    public function loadBySeoId(int $seoId): object
    {
        return (object) ['seo_id' => $seoId];
    }

    public function servicesForHome(): Collection
    {
        $services = new Collection();
        $locations = ServiceLocation::query()
            ->where('district_id', '!=', '0')
            ->with('services.seo', 'services.comments', 'services.options')
            ->get();

        foreach ($locations as $serviceLocation) {
            foreach ($serviceLocation->services as $service) {
                $services->push($service);
            }
        }

        return $services;
    }

    public function render(string $section, object $item): ?string
    {
        if ($section !== self::SECTION_SERVICES) {
            return null;
        }

        return view('main.home.fragments.services', [
            'list' => $this->servicesForHome(),
        ])->render();
    }
}
