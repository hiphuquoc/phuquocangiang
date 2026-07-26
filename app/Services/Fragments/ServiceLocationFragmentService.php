<?php

namespace App\Services\Fragments;

use App\Contracts\PageFragmentProvider;
use App\Models\ServiceLocation;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;
use Illuminate\Support\Collection;

class ServiceLocationFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE = 'service-location';
    public const SECTION_LIST = 'services';

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

    public function loadBySeoId(int $seoId): ?ServiceLocation
    {
        return ServiceLocation::query()
            ->where('seo_id', $seoId)
            ->with([
                'seo',
                'district',
                'services' => fn ($q) => $q->whereHas('seo'),
                'services.seo',
                'services.comments',
                'services.options',
                'services.serviceLocation',
            ])
            ->first();
    }

    public function servicesForList(ServiceLocation $item): Collection
    {
        return $item->services->filter(fn ($service) => !empty($service->seo));
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(ServiceLocation $item, string $locale): array
    {
        $arrayPrice = [];
        foreach ($this->servicesForList($item) as $service) {
            if (!empty($service->price_show)) {
                $arrayPrice[] = (float) $service->price_show;
            }
        }

        $currency = schema_currency($locale);

        return [
            'low'      => schema_price_amount(!empty($arrayPrice) ? min($arrayPrice) : 3000000, $currency),
            'high'     => schema_price_amount(!empty($arrayPrice) ? max($arrayPrice) : 5000000, $currency),
            'currency' => $currency,
        ];
    }

    public function render(string $section, object $item): ?string
    {
        if (!$item instanceof ServiceLocation || $section !== self::SECTION_LIST) {
            return null;
        }

        return view('main.serviceLocation.fragments.services', [
            'item' => $item,
            'list' => $this->servicesForList($item),
        ])->render();
    }
}
