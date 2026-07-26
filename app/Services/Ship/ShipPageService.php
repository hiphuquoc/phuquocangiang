<?php

declare(strict_types=1);

namespace App\Services\Ship;

use App\Models\Ship;
use App\Services\Island\IslandContextService;

class ShipPageService
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(Ship $item, string $locale): array
    {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));
        $bookingHref = booking_route('shipBooking.form', [
            'ship_port_departure_id' => $item->ship_port_departure_id,
            'ship_port_location_id' => $item->ship_port_location_id,
        ]);

        return [
            'banner' => $this->banner($item, $name),
            'intro' => $this->intro($item, $name, $bookingHref),
            'gallery' => $this->gallery($item, $name),
            'faq' => $this->faqSection($item, $name),
            'bookingHref' => $bookingHref,
            'partners' => $this->partners($item),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Ship $item, string $name): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_ship'),
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($seo->description ?? ''),
                'Lịch tàu, giá vé và thông tin tuyến ' . island_name() . '.',
                160,
            ),
            'image' => $this->context->coverImage($seo, 'large'),
            'imageAlt' => $name,
            'locationName' => $name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function intro(Ship $item, string $name, string $bookingHref): array
    {
        $departure = $item->portDeparture->name ?? null;
        $arrival = $item->portLocation->name ?? null;
        $facts = array_values(array_filter([
            ($departure && $arrival) ? ($departure . ' → ' . $arrival) : ($departure ?: $arrival),
            !empty($item->partners) && $item->partners->isNotEmpty()
                ? $item->partners->map(fn ($p) => $p->infoPartner->name ?? null)->filter()->take(3)->implode(', ')
                : null,
        ]));

        return [
            'kicker' => t('kicker_ship'),
            'title' => t('ship_detail_title', ['name' => $name]),
            'description' => $this->context->excerpt((string) ($item->seo->description ?? ''), '', 180),
            'facts' => $facts,
            'price' => null,
            'priceFormatted' => null,
            'priceUnit' => null,
            'priceFromLabel' => null,
            'ctaLabel' => t('ship_book_mobile') ?? t('book_tour'),
            'ctaHref' => $bookingHref,
            'ctaAnchor' => null,
        ];
    }

    /**
     * @return array<int, array{src: string, alt: string, thumb: string|null}>
     */
    private function gallery(Ship $item, string $name): array
    {
        $cover = $this->context->coverImage($item->seo, 'large');
        if ($cover === '') {
            return [];
        }

        return [[
            'src' => $cover,
            'thumb' => $this->context->coverImage($item->seo, 'small'),
            'alt' => $name,
        ]];
    }

    /**
     * @return array<int, array{name: string, logo: string|null}>
     */
    private function partners(Ship $item): array
    {
        $rows = [];
        foreach ($item->partners ?? [] as $relation) {
            $partner = $relation->infoPartner ?? null;
            if ($partner === null) {
                continue;
            }
            $name = trim((string) ($partner->name ?? $partner->seo->title ?? ''));
            if ($name === '') {
                continue;
            }
            $logoRaw = $partner->seo?->getRawOriginal('image_small')
                ?? $partner->seo?->getRawOriginal('image')
                ?? null;
            $rows[] = [
                'name' => $name,
                'logo' => !empty($logoRaw) ? (media_url($logoRaw) ?? null) : null,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function faqSection(Ship $item, string $name): array
    {
        $items = [];
        foreach ($item->questions ?? [] as $question) {
            $q = trim(strip_tags((string) ($question->question ?? '')));
            $a = trim((string) ($question->answer ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            $items[] = ['q' => $q, 'a' => $a];
        }

        return [
            'active' => $items !== [],
            'kicker' => t('kicker_support'),
            'title' => t('ship_faq_about', ['name' => $name]),
            'description' => t('tour_faq_default_lead', ['brand' => config('main.name')]),
            'items' => $items,
            'open_index' => 0,
        ];
    }
}
