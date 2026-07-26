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

        $prices = ($item->prices ?? collect())
            ->filter(fn ($price) => (float) ($price->price_adult ?? 0) > 0)
            ->values();

        $minAdult = $prices
            ->map(fn ($price) => (float) ($price->price_adult ?? 0))
            ->filter(fn (float $v) => $v > 0)
            ->min();

        $priceFrom = $minAdult !== null
            ? $this->context->formatPrice((float) $minAdult)
            : null;

        return [
            'banner' => $this->banner($item, $name),
            'intro' => $this->intro($item, $name, $bookingHref, $priceFrom),
            'gallery' => $this->gallery($item, $name),
            'routeCard' => $this->routeCard($item, $name, $bookingHref, $prices, $priceFrom),
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
    private function intro(Ship $item, string $name, string $bookingHref, ?string $priceFrom): array
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
            'title' => $name,
            'description' => $this->context->excerpt((string) ($item->seo->description ?? ''), '', 180),
            'facts' => $facts,
            'price' => null,
            'priceFormatted' => $priceFrom,
            'priceUnit' => $priceFrom !== null ? '/ khách' : null,
            'priceFromLabel' => t('price_from'),
            'ctaLabel' => t('ship_book_mobile') ?? t('book_tour'),
            'ctaHref' => $bookingHref,
            'ctaAnchor' => '#ship-fares',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, mixed>  $prices
     * @return array<string, mixed>|null
     */
    private function routeCard(Ship $item, string $name, string $bookingHref, $prices, ?string $priceFrom): ?array
    {
        if ($prices->isEmpty()) {
            return null;
        }

        $from = trim((string) ($item->portDeparture->name ?? $item->departure->name ?? ''));
        $to = trim((string) ($item->portLocation->name ?? $item->location->name ?? island_name()));
        $schedules = [];
        foreach ($prices as $price) {
            foreach ($price->times ?? [] as $time) {
                if (!empty($time->time_departure)) {
                    $schedules[] = (string) $time->time_departure;
                }
            }
        }
        $schedules = array_values(array_unique($schedules));
        sort($schedules);

        $tiers = [];
        foreach ([
            ['Người lớn', 'Từ 12 tuổi', 'price_adult'],
            ['Trẻ em', '6–11 tuổi', 'price_child'],
            ['Người cao tuổi', 'Trên 60 tuổi', 'price_old'],
        ] as [$label, $hint, $field]) {
            $min = $prices->map(fn ($p) => (float) ($p->{$field} ?? 0))->filter(fn ($v) => $v > 0)->min();
            if ($min === null) {
                continue;
            }
            $formatted = $this->context->formatPrice((float) $min);
            if ($formatted === null) {
                continue;
            }
            $tiers[] = ['label' => $label, 'hint' => $hint, 'amount' => $formatted];
        }

        $fareGroups = [];
        if ($tiers !== []) {
            $fareGroups[] = ['label' => 'Ghế phổ thông', 'tiers' => $tiers];
        }
        $vipMin = $prices->map(fn ($p) => (float) ($p->price_vip ?? 0))->filter(fn ($v) => $v > 0)->min();
        if ($vipMin !== null) {
            $fareGroups[] = [
                'label' => 'Ghế VIP',
                'uniform' => $this->context->formatPrice((float) $vipMin),
                'uniformHint' => 'Đồng giá mọi độ tuổi',
            ];
        }

        if ($fareGroups === []) {
            return null;
        }

        return [
            'title' => $name,
            'from' => $from !== '' ? $from : '—',
            'fromSub' => '',
            'to' => $to !== '' ? $to : island_name(),
            'toSub' => '',
            'duration' => null,
            'schedules' => array_slice($schedules, 0, 12),
            'fareGroups' => $fareGroups,
            'priceFrom' => $priceFrom,
            'bookingHref' => $bookingHref,
            'detailHref' => null,
            'image' => '',
        ];
    }

    /**
     * @return array<int, array{src: string, alt: string, thumb: string|null}>
     */
    private function gallery(Ship $item, string $name): array
    {
        $images = [];
        $cover = $this->context->coverImage($item->seo, 'large');
        if ($cover !== '') {
            $images[] = [
                'src' => $cover,
                'thumb' => $this->context->coverImage($item->seo, 'small'),
                'alt' => $name,
            ];
        }

        foreach ($item->files ?? [] as $file) {
            if (($file->file_type ?? '') !== 'gallery') {
                continue;
            }
            $raw = $file->getRawOriginal('file_path') ?? $file->file_path ?? null;
            $src = media_url($raw);
            if (empty($src)) {
                continue;
            }
            $images[] = [
                'src' => $src,
                'thumb' => $src,
                'alt' => $name,
            ];
        }

        return $images;
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
