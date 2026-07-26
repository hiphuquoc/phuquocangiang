<?php

namespace App\Services\Fragments;

use App\Contracts\PageFragmentProvider;
use App\Models\ComboLocation;
use App\Services\Fragments\Concerns\BuildsFragmentUrl;
use Illuminate\Support\Collection;

class ComboLocationFragmentService implements PageFragmentProvider
{
    use BuildsFragmentUrl;

    public const PAGE_TYPE = 'combo-location';
    public const SECTION_LIST = 'combos';

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

    public function loadBySeoId(int $seoId): ?ComboLocation
    {
        return ComboLocation::query()
            ->where('seo_id', $seoId)
            ->with([
                'seo',
                'combos.infoCombo.seo',
                'combos.infoCombo.location',
                'combos.infoCombo.departure',
            ])
            ->first();
    }

    public function combosForList(ComboLocation $item): Collection
    {
        $data = new Collection();
        $i    = 0;
        foreach ($item->combos as $combo) {
            if (empty($combo->infoCombo)) {
                continue;
            }
            $row            = $combo->infoCombo;
            $row->seo       = $combo->infoCombo->seo;
            $row->location  = $combo->infoCombo->location ?? null;
            $row->departure = $combo->infoCombo->departure ?? null;
            $data[$i]       = $row;
            ++$i;
        }

        return $data;
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(ComboLocation $item, string $locale): array
    {
        $arrayPrice = [];
        foreach ($this->combosForList($item) as $combo) {
            if (!empty($combo->price_show)) {
                $arrayPrice[] = (float) $combo->price_show;
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
        if (!$item instanceof ComboLocation || $section !== self::SECTION_LIST) {
            return null;
        }

        return view('main.comboLocation.fragments.combos', [
            'item' => $item,
            'list' => $this->combosForList($item),
        ])->render();
    }
}
