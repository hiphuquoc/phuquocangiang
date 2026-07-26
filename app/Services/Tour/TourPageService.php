<?php

declare(strict_types=1);

namespace App\Services\Tour;

use App\Http\Controllers\AdminTourOptionController;
use App\Models\Tour;
use App\Services\Cards\TourCardMapper;
use App\Services\Island\IslandContextService;
use Illuminate\Support\Collection;

class TourPageService
{
    public function __construct(
        private readonly IslandContextService $context,
        private readonly TourCardMapper $tourCards,
    ) {}

    /**
     * @param  Collection<int, Tour>  $related
     * @return array<string, mixed>
     */
    public function forPage(Tour $item, string $locale, Collection $related): array
    {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));
        $bookingHref = booking_route('tourBooking.form', [
            'tour_location_id' => $item->locations[0]->infoLocation->id ?? 0,
            'tour_info_id' => $item->id ?? 0,
        ]);

        return [
            'banner' => $this->banner($item, $name),
            'intro' => $this->intro($item, $name, $bookingHref),
            'gallery' => $this->gallery($item, $name),
            'options' => $this->optionsSection($item, $bookingHref),
            'faq' => $this->faqSection($item, $name),
            'related' => $this->relatedSection($related),
            'bookingHref' => $bookingHref,
        ];
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(Tour $item, string $locale): array
    {
        $high = !empty($item->price_show) ? (float) $item->price_show : 5000000.0;
        $currency = schema_currency($locale);

        return [
            'low' => schema_price_amount(round($high / 2, 0), $currency),
            'high' => schema_price_amount($high, $currency),
            'currency' => $currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Tour $item, string $name): array
    {
        $seo = $item->seo;

        return [
            'kicker' => t('kicker_book_tour'),
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($seo->description ?? ''),
                'Tour, lịch trình và giá tốt tại ' . island_name() . '.',
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
    private function intro(Tour $item, string $name, string $bookingHref): array
    {
        $price = !empty($item->price_show) ? (float) $item->price_show : null;
        $facts = array_values(array_filter([
            !empty($item->days)
                ? ((int) $item->days > 1
                    ? t('tour_days_nights', ['days' => $item->days, 'nights' => $item->nights ?? max(0, (int) $item->days - 1)])
                    : trim((string) (($item->time_start ?? '') . (!empty($item->time_end) ? ' – ' . $item->time_end : ''))))
                : null,
            !empty($item->departure_schedule) ? (string) $item->departure_schedule : null,
            !empty($item->transport) ? (string) $item->transport : null,
            !empty($item->pick_up) ? (string) t('tour_pickup_at', ['place' => $item->pick_up]) : null,
        ]));

        return [
            'kicker' => t('kicker_book_tour'),
            'title' => $name,
            'description' => $this->context->excerpt((string) ($item->seo->description ?? ''), '', 180),
            'facts' => $facts,
            'price' => $price,
            'priceFormatted' => $price !== null ? format_price($price) : null,
            'priceUnit' => '/ khách',
            'priceFromLabel' => t('price_from'),
            'ctaLabel' => t('book_tour'),
            'ctaHref' => $bookingHref,
            'ctaAnchor' => '#tour-options',
        ];
    }

    /**
     * @return array<int, array{src: string, alt: string, thumb: string|null}>
     */
    private function gallery(Tour $item, string $name): array
    {
        $images = [];

        $cover = $this->context->coverImage($item->seo, 'large');
        $thumb = $cover !== '' ? $this->context->coverImage($item->seo, 'small') : '';

        // Soft-fix: legacy /storage cover → prefer GCS URL when toCloud maps (no exists probe).
        if ($cover !== '' && str_starts_with($cover, '/storage') && $item->seo) {
            $raw = $item->seo->getRawOriginal('image') ?: $item->seo->getRawOriginal('image_small');
            if (!empty($raw)) {
                $storage = app(\App\Services\Media\GcsMediaStorageService::class);
                $cloudPath = $storage->toCloudObjectPath($raw);
                if ($cloudPath !== null) {
                    $cloudUrl = media_url($cloudPath);
                    if (!empty($cloudUrl) && !str_starts_with((string) $cloudUrl, '/storage')) {
                        $cover = $cloudUrl;
                        $rawSmall = $item->seo->getRawOriginal('image_small') ?: $raw;
                        $cloudSmall = $storage->toCloudObjectPath($rawSmall);
                        if ($cloudSmall !== null) {
                            $thumbUrl = media_url($cloudSmall);
                            if (!empty($thumbUrl) && !str_starts_with((string) $thumbUrl, '/storage')) {
                                $thumb = $thumbUrl;
                            }
                        }
                    }
                }
            }
        }

        if ($cover !== '') {
            $images[] = [
                'src' => $cover,
                'thumb' => $thumb !== '' ? $thumb : null,
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
     * @return array<string, mixed>
     */
    private function optionsSection(Tour $item, string $bookingHref): array
    {
        $items = [];

        if (!empty($item->options) && $item->options->isNotEmpty()) {
            $merged = AdminTourOptionController::margeTourPriceByDate($item->options);
            foreach ($merged as $option) {
                $priceRows = [];
                $firstPrice = null;
                foreach ($option['date_apply'] ?? [] as $priceGroup) {
                    foreach ($priceGroup as $applyAge) {
                        $value = !empty($applyAge['price']) ? (float) $applyAge['price'] : null;
                        if ($firstPrice === null && $value !== null) {
                            $firstPrice = $value;
                        }
                        $priceRows[] = [
                            'label' => (string) ($applyAge['apply_age'] ?? ''),
                            'priceFormatted' => $value !== null ? format_price($value) : '—',
                            'dates' => t('tour_from_to_dates', [
                                'from' => !empty($applyAge['date_start']) ? date('d/m/Y', strtotime((string) $applyAge['date_start'])) : '…',
                                'to' => !empty($applyAge['date_end']) ? date('d/m/Y', strtotime((string) $applyAge['date_end'])) : '…',
                            ]),
                        ];
                    }
                    break;
                }

                $items[] = [
                    'title' => (string) ($option['name'] ?? ''),
                    'priceFormatted' => $firstPrice !== null ? format_price($firstPrice) : null,
                    'rows' => $priceRows,
                    'bookingHref' => $bookingHref,
                    'ctaLabel' => t('book_tour'),
                ];
            }
        }

        return [
            'head' => [
                'eyebrow' => t('kicker_book_tour'),
                'title' => t('tour_pricing', ['name' => $item->name ?? '']),
                'desc' => 'Chọn gói phù hợp và đặt nhanh.',
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function faqSection(Tour $item, string $name): array
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
            'title' => t('tour_faq_question_about') . ' ' . $name,
            'description' => t('tour_faq_default_lead', ['brand' => config('main.name')]),
            'items' => $items,
            'open_index' => 0,
        ];
    }

    /**
     * @param  Collection<int, Tour>  $related
     * @return array<string, mixed>
     */
    private function relatedSection(Collection $related): array
    {
        $cards = [];
        foreach ($related as $tour) {
            $card = $this->tourCards->fromTour($tour, island_name());
            if ($card !== null) {
                $cards[] = $card;
            }
            if (count($cards) >= 6) {
                break;
            }
        }

        return [
            'head' => [
                'eyebrow' => t('kicker_tour_list'),
                'title' => 'Tour liên quan',
                'desc' => 'Gợi ý thêm trải nghiệm phù hợp tại ' . island_name() . '.',
            ],
            'items' => $cards,
        ];
    }
}
