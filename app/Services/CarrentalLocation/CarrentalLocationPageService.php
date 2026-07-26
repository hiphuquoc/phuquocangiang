<?php

declare(strict_types=1);

namespace App\Services\CarrentalLocation;

use App\Models\CarrentalLocation;
use App\Services\Island\IslandContextService;
use App\Services\Listing\ListingRelatedServicesBuilder;

class CarrentalLocationPageService
{
    public function __construct(
        private readonly IslandContextService $context,
        private readonly ListingRelatedServicesBuilder $related,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(CarrentalLocation $item, string $locale): array
    {
        $name = $this->locationName($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'relatedServices' => $this->related->forCarrentalLocation($item, $name),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(CarrentalLocation $item, string $name, string $locale): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_transport'),
            'title' => $seo->title ?? ($name !== '' ? t('tour_carrental_title', ['name' => $name]) : $name),
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                'Nhận xe nhanh tại trung tâm — giấy tờ gọn, bảng giá công khai, hỗ trợ lộ trình miễn phí.',
                160,
            ),
            'image' => $this->context->coverImage($seo, 'medium'),
            'imageAlt' => (string) ($seo->title ?? $name),
            'locationName' => $name,
        ];
    }

    private function locationName(CarrentalLocation $item): string
    {
        return (string) ($item->location_name ?: $item->name ?: island_name());
    }
}
