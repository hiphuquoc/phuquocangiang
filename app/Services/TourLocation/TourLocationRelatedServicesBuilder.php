<?php

declare(strict_types=1);

namespace App\Services\TourLocation;

use App\Models\TourLocation;
use App\Services\Island\IslandContextService;

/**
 * Gợi ý dịch vụ liên quan trên trang danh mục tour (v2).
 */
class TourLocationRelatedServicesBuilder
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    public function forLocation(TourLocation $item, string $name): array
    {
        $items = array_values(array_filter([
            $this->shipCard($item, $name),
            $this->airCard($item, $name),
            $this->comboCard($item, $name),
            $this->serviceCard($item, $name),
            $this->hotelCard($item, $name),
            $this->carrentalCard($item, $name),
            $this->guideCard($item, $name),
        ]));

        return [
            'head' => [
                'eyebrow' => t('tour_related_services_kicker'),
                'title' => t('tour_related_services_title'),
                'desc' => strip_tags((string) t('tour_related_services_desc', ['name' => $name])),
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function shipCard(TourLocation $item, string $name): ?array
    {
        $loc = $item->shipLocations->first()?->infoShipLocation;
        $seo = $loc?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('ship', t('kicker_ship'), t('tour_ship_title', ['name' => $name]), t('tour_related_ship_teaser'), $seo, 'ferry');
    }

    /**
     * @return array<string, string>|null
     */
    private function airCard(TourLocation $item, string $name): ?array
    {
        $loc = $item->airLocations->first()?->infoAirLocation;
        $seo = $loc?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('air', t('kicker_air'), t('tour_air_title', ['name' => $name]), t('tour_related_air_teaser'), $seo);
    }

    /**
     * @return array<string, string>|null
     */
    private function comboCard(TourLocation $item, string $name): ?array
    {
        $loc = $item->comboLocations->first()?->infoComboLocation;
        $seo = $loc?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('combo', t('kicker_combo'), t('tour_combo_title', ['name' => $name]), t('tour_related_combo_teaser'), $seo);
    }

    /**
     * @return array<string, string>|null
     */
    private function serviceCard(TourLocation $item, string $name): ?array
    {
        $loc = $item->serviceLocations->first()?->infoServiceLocation;
        $seo = $loc?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('service', t('kicker_entertainment'), t('tour_service_title', ['name' => $name]), t('tour_related_service_teaser'), $seo, 'services');
    }

    /**
     * @return array<string, string>|null
     */
    private function hotelCard(TourLocation $item, string $name): ?array
    {
        $loc = $item->hotelLocations->first()?->infoHotelLocation ?? null;
        $seo = $loc?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('hotel', t('kicker_hotel'), t('tour_related_hotel_title', ['name' => $name]), t('tour_related_hotel_teaser'), $seo, 'hotels');
    }

    /**
     * @return array<string, string>|null
     */
    private function carrentalCard(TourLocation $item, string $name): ?array
    {
        $loc = $item->carrentalLocations->first()?->infoCarrentalLocation;
        $seo = $loc?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('carrental', t('kicker_transport'), t('tour_carrental_title', ['name' => $name]), t('tour_related_carrental_teaser'), $seo, 'rental');
    }

    /**
     * @return array<string, string>|null
     */
    private function guideCard(TourLocation $item, string $name): ?array
    {
        $guide = $item->guides->first()?->infoGuide;
        $seo = $guide?->seo;
        if (!$seo || empty($seo->slug_full)) {
            return null;
        }

        return $this->card('guide', t('kicker_guide'), t('tour_guide_section_title', ['name' => $name]), t('tour_related_guide_teaser'), $seo);
    }

    /**
     * @return array<string, string>
     */
    private function card(string $type, string $kicker, string $title, string $desc, object $seo, ?string $sectionId = null): array
    {
        $fallback = $this->context->pageUrl($seo);
        $href = $sectionId !== null && $sectionId !== ''
            ? $this->context->categoryUrl($seo, $sectionId, $fallback)
            : $fallback;

        return [
            'type' => $type,
            'kicker' => $kicker,
            'title' => $title,
            'desc' => $desc,
            'href' => $href,
            'cta' => t('tour_related_cta'),
        ];
    }
}
