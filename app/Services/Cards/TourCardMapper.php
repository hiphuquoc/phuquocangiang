<?php

declare(strict_types=1);

namespace App\Services\Cards;

use App\Models\Tour;
use App\Services\Island\IslandContextService;

class TourCardMapper
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function fromTour(Tour $tour, string $islandName): ?array
    {
        $seo = $tour->seo;
        if (!$seo) {
            return null;
        }

        $title = $tour->name ?: ($seo->title ?? $islandName);
        $price = !empty($tour->price_show)
            ? format_price_plain((float) $tour->price_show)
            : (string) config('currency.contact_label', 'Liên hệ');
        $tagline = $this->context->excerpt($seo->description ?? '', '', 220);

        return [
            'image' => $this->context->coverImage($seo, 'small'),
            'alt' => $title,
            'title' => $title,
            'tagline' => $tagline !== '' ? $tagline : null,
            'price' => $price,
            'facts' => array_values(array_filter([
                !empty($tour->pick_up) ? ['icon' => 'pin', 'text_key' => 'tour_pickup_at', 'text_params' => ['place' => $tour->pick_up]] : null,
                !empty($tour->departure_schedule) ? ['icon' => 'clock', 'text' => $tour->departure_schedule] : null,
            ])),
            'ctaHref' => $this->context->pageUrl($seo),
            'filterDay' => ((int) ($tour->days ?? 1)) > 1 ? 'tour-nhieu-ngay' : 'tour-trong-ngay',
        ];
    }
}
