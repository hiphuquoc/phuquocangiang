<?php

declare(strict_types=1);

namespace App\Services\Service;

use App\Models\Service;
use App\Services\Island\IslandContextService;

class ServicePageService
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(Service $item, string $locale): array
    {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));
        $bookingHref = booking_route('serviceBooking.form', [
            'service_location_id' => $item->serviceLocation->id ?? 0,
            'service_info_id' => $item->id ?? 0,
        ]);

        return [
            'banner' => $this->banner($item, $name),
            'intro' => $this->intro($item, $name, $bookingHref),
            'gallery' => $this->gallery($item, $name),
            'options' => $this->optionsSection($item, $bookingHref),
            'faq' => $this->faqSection($item, $name),
            'bookingHref' => $bookingHref,
        ];
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(Service $item, string $locale): array
    {
        $high = !empty($item->price_show) ? (float) $item->price_show : 1000000.0;
        $currency = schema_currency($locale);

        return [
            'low' => schema_price_amount($high / 2, $currency),
            'high' => schema_price_amount($high, $currency),
            'currency' => $currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Service $item, string $name): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_entertainment'),
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($seo->description ?? ''),
                strip_tags((string) t('service_location_desc', [
                    'name' => island_name(),
                    'brand' => config('main.name'),
                ])),
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
    private function intro(Service $item, string $name, string $bookingHref): array
    {
        $price = !empty($item->price_show) ? (float) $item->price_show : null;
        $duration = trim((string) (($item->time_start ?? '') . (!empty($item->time_end) ? ' – ' . $item->time_end : '')));
        $facts = array_values(array_filter([
            $duration !== '' ? $duration : null,
            !empty($item->options) ? (string) t('service_packages_count', ['count' => $item->options->count()]) : null,
            (string) t('service_e_ticket'),
        ]));

        $priceOld = null;
        $saleOff = null;
        if (!empty($item->price_del) && $price !== null && (float) $item->price_del > $price) {
            $priceOld = (float) $item->price_del;
            $saleOff = (int) \App\Helpers\Number::calculatorSaleoff($price, $priceOld);
        }

        return [
            'kicker' => t('kicker_entertainment'),
            'title' => $name,
            'description' => $this->context->excerpt((string) ($item->seo->description ?? ''), '', 180),
            'facts' => $facts,
            'price' => $price,
            'priceFormatted' => $price !== null ? format_price($price) : null,
            'priceOldFormatted' => $priceOld !== null ? format_price($priceOld) : null,
            'saleOff' => $saleOff,
            'priceUnit' => '/ khách',
            'priceFromLabel' => t('price_from'),
            'ctaLabel' => t('service_book_this_ticket') ?? t('book_tour'),
            'ctaHref' => $bookingHref,
            'ctaAnchor' => '#service-options',
        ];
    }

    /**
     * @return array<int, array{src: string, alt: string, thumb: string|null}>
     */
    private function gallery(Service $item, string $name): array
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
            $src = media_variant_url($raw, 'original') ?? media_url($raw);
            if (empty($src)) {
                continue;
            }
            $images[] = [
                'src' => $src,
                'thumb' => media_variant_url($raw, 'small') ?? $src,
                'alt' => $name,
            ];
        }

        return $images;
    }

    /**
     * @return array<string, mixed>
     */
    private function optionsSection(Service $item, string $bookingHref): array
    {
        $items = [];

        foreach ($item->options ?? [] as $option) {
            $rows = [];
            $firstPrice = null;
            foreach ($option->prices ?? [] as $price) {
                $value = !empty($price->price) ? (float) $price->price : null;
                if ($firstPrice === null && $value !== null) {
                    $firstPrice = $value;
                }
                $dates = '';
                if (!empty($price->date_start)) {
                    $dates = ($price->date_start == $price->date_end)
                        ? date('d/m/Y', strtotime((string) $price->date_start))
                        : date('d/m/Y', strtotime((string) $price->date_start)) . ' – ' . date('d/m/Y', strtotime((string) $price->date_end));
                }
                $rows[] = [
                    'label' => (string) ($price->apply_age ?? ''),
                    'priceFormatted' => $value !== null ? format_price($value) : (string) t('contact_price'),
                    'dates' => $dates,
                    'note' => (string) ($price->promotion ?? ''),
                ];
            }

            $items[] = [
                'title' => (string) ($option->name ?? ''),
                'priceFormatted' => $firstPrice !== null ? format_price($firstPrice) : null,
                'rows' => $rows,
                'bookingHref' => $bookingHref,
                'ctaLabel' => t('service_book_this_ticket'),
            ];
        }

        return [
            'head' => [
                'eyebrow' => t('kicker_entertainment'),
                'title' => t('service_price_table_title', ['name' => $item->name ?? '']),
                'desc' => strip_tags((string) t('service_price_note', ['hotline' => config('company.hotline')])),
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function faqSection(Service $item, string $name): array
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
            'title' => t('service_faq_about', ['name' => $name]),
            'description' => t('tour_faq_default_lead', ['brand' => config('main.name')]),
            'items' => $items,
            'open_index' => 0,
        ];
    }
}
