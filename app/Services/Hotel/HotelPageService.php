<?php

declare(strict_types=1);

namespace App\Services\Hotel;

use App\Helpers\Rating;
use App\Models\Hotel;
use App\Models\HotelPrice;
use App\Services\Island\IslandContextService;

class HotelPageService
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPage(Hotel $item, string $locale): array
    {
        $name = (string) ($item->name ?: ($item->seo->title ?? ''));
        $priceOffer = $this->minPriceOffer($item);

        return [
            'banner' => $this->banner($item, $name, $locale),
            'intro' => $this->intro($item, $name, $priceOffer),
            'gallery' => $this->gallery($item, $name),
            'facilities' => $this->facilities($item),
            'rooms' => $this->roomsSection($item),
            'policy' => $this->policyContent($item),
            'faq' => $this->faqSection($item, $name),
        ];
    }

    /**
     * @return array{low: float, high: float, currency: string}
     */
    public function schemaOfferPrices(Hotel $item, string $locale): array
    {
        $arrayPrice = [];

        foreach ($item->rooms ?? [] as $room) {
            foreach ($room->prices ?? [] as $price) {
                if (!empty($price->price)) {
                    $arrayPrice[] = (float) $price->price;
                }
            }
        }

        $currency = schema_currency($locale);

        return [
            'low' => schema_price_amount(!empty($arrayPrice) ? min($arrayPrice) : 3000000, $currency),
            'high' => schema_price_amount(!empty($arrayPrice) ? max($arrayPrice) : 5000000, $currency),
            'currency' => $currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(Hotel $item, string $name, string $locale): array
    {
        $seo = $item->seo;
        $heroImage = $this->firstGalleryUrl($item, 'large') ?? $this->context->coverImage($seo, 'large');

        return [
            'kicker' => t('kicker_hotel'),
            'title' => $seo->title ?? $name,
            'tagline' => $this->context->excerpt(
                (string) ($item->description ?: $seo->description ?? ''),
                strip_tags((string) t('hotel_location_desc', ['name' => island_name()])),
                160,
            ),
            'image' => $heroImage,
            'imageAlt' => $name,
            'locationName' => $name,
        ];
    }

    /**
     * @param  array{price: float|null, priceOld: float|null, saleOff: int|null}  $priceOffer
     * @return array<string, mixed>
     */
    private function intro(Hotel $item, string $name, array $priceOffer): array
    {
        [$rating, $ratingCount] = $this->aggregateRating($item);

        return [
            'title' => $name,
            'typeName' => trim((string) ($item->type_name ?? '')),
            'typeRating' => max(0, (int) ($item->type_rating ?? 0)),
            'rating' => $ratingCount > 0 ? $rating : null,
            'ratingCount' => $ratingCount,
            'ratingText' => $ratingCount > 0 ? Rating::getTextRatingByRule($rating) : null,
            'address' => $this->resolveAddress($item),
            'price' => $priceOffer['price'],
            'priceFormatted' => $priceOffer['price'] !== null
                ? format_price($priceOffer['price'])
                : null,
            'priceOldFormatted' => !empty($priceOffer['priceOld'])
                ? format_price($priceOffer['priceOld'])
                : null,
            'saleOff' => $priceOffer['saleOff'],
            'roomsAnchor' => '#hotel-rooms',
        ];
    }

    /**
     * @return array<int, array{src: string, alt: string, thumb: string|null}>
     */
    private function gallery(Hotel $item, string $name): array
    {
        $images = [];

        foreach ($item->images ?? [] as $image) {
            $src = $this->imageUrl($image->image ?? null, 'large');
            if ($src === null) {
                continue;
            }

            $images[] = [
                'src' => $src,
                'thumb' => $this->imageUrl($image->image_small ?? $image->image ?? null, 'small') ?? $src,
                'alt' => $name,
            ];
        }

        if ($images === [] && $item->seo) {
            $fallback = $this->context->coverImage($item->seo, 'large');
            if ($fallback !== '') {
                $images[] = [
                    'src' => $fallback,
                    'thumb' => $this->context->coverImage($item->seo, 'small'),
                    'alt' => $name,
                ];
            }
        }

        return $images;
    }

    /**
     * @return array<string, array<string, array<int, array<string, mixed>>>>
     */
    private function facilities(Hotel $item): array
    {
        $grouped = [
            'tripadvisor' => [],
            'traveloka' => [],
        ];

        foreach ($item->facilities ?? [] as $facility) {
            $info = $facility->infoFacility ?? null;
            if ($info === null) {
                continue;
            }

            $row = $info->toArray();
            if (!empty($info->type)) {
                $grouped['tripadvisor'][$info->type][] = $row;
            } elseif (!empty($info->category_name)) {
                $grouped['traveloka'][$info->category_name][] = $row;
            }
        }

        return $grouped;
    }

    /**
     * @return array<string, mixed>
     */
    private function roomsSection(Hotel $item): array
    {
        $items = [];

        foreach ($item->rooms ?? [] as $room) {
            foreach ($room->prices ?? [] as $price) {
                $card = $this->roomCard($price, $room);
                if ($card !== null) {
                    $items[] = $card;
                }
            }
        }

        return [
            'head' => [
                'eyebrow' => t('kicker_hotel'),
                'title' => t('hotel_choose_room'),
                'desc' => t('hotel_location_desc', ['name' => island_name()]),
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function roomCard(HotelPrice $price, object $room): ?array
    {
        $value = (float) ($price->price ?? 0);
        if ($value <= 0) {
            return null;
        }

        $includes = [];
        if ((int) ($price->breakfast ?? 0) === 1) {
            $includes[] = t('hotel_breakfast');
        }
        if ((int) ($price->given ?? 0) === 1) {
            $includes[] = t('hotel_pickup');
        }

        $bedParts = [];
        foreach ($price->beds ?? [] as $bed) {
            $bedName = $bed->infoHotelBed->name ?? '';
            if ($bedName === '') {
                continue;
            }
            $bedParts[] = ($bed->quantity ?? 1) . ' ' . $bedName;
        }

        $facilities = [];
        foreach ($room->facilities ?? [] as $facility) {
            $info = $facility->infoHotelRoomFacility ?? null;
            if ($info === null) {
                continue;
            }
            $facilities[] = [
                'icon' => $info->icon ?? '',
                'name' => (string) ($info->name ?? ''),
            ];
            if (count($facilities) >= 9) {
                break;
            }
        }

        $images = [];
        foreach ($room->images ?? [] as $image) {
            $src = $this->imageUrl($image->image ?? null, 'medium');
            if ($src === null) {
                continue;
            }
            $images[] = [
                'src' => $src,
                'thumb' => $this->imageUrl($image->image_small ?? $image->image ?? null, 'small') ?? $src,
                'alt' => (string) ($room->name ?? ''),
            ];
        }

        $roomTitle = (string) ($room->name ?? '');
        if ($includes !== []) {
            $roomTitle .= ' (' . implode(' + ', $includes) . ')';
        }

        $facilityTotal = (int) ($room->facilities?->count() ?? count($facilities));

        return [
            'id' => (int) $price->id,
            'roomName' => $roomTitle,
            'size' => !empty($room->size) ? (string) $room->size : null,
            'maxPeople' => (int) ($price->number_people ?? 0),
            'breakfast' => (int) ($price->breakfast ?? 0) === 1,
            'pickup' => (int) ($price->given ?? 0) === 1,
            'beds' => $bedParts !== [] ? implode(' ' . t('hotel_and') . ' ', $bedParts) : t('hotel_undefined'),
            'facilities' => $facilities,
            'facilityExtra' => max(0, $facilityTotal - count($facilities)),
            'images' => $images,
            'price' => $value,
            'priceFormatted' => format_price($value),
            'priceOldFormatted' => !empty($price->price_old) ? format_price($price->price_old) : null,
            'saleOff' => !empty($price->sale_off) ? (int) $price->sale_off : null,
            'bookingHref' => booking_route('hotelBooking.form', ['hotel_price_id' => $price->id]),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function policyContent(Hotel $item): ?array
    {
        $policyLabel = trim((string) t('hotel_policy_section'));

        foreach ($item->contents ?? [] as $content) {
            $name = trim((string) ($content->name ?? ''));
            if ($name === 'Chính sách khách sạn' || ($policyLabel !== '' && $name === $policyLabel)) {
                return [
                    'title' => t('hotel_policy_section'),
                    'html' => (string) ($content->content ?? ''),
                ];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function faqSection(Hotel $item, string $name): array
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
     * @return array{price: float|null, priceOld: float|null, saleOff: int|null}
     */
    private function minPriceOffer(Hotel $item): array
    {
        $min = null;
        $priceOld = null;
        $saleOff = null;

        foreach ($item->rooms ?? [] as $room) {
            foreach ($room->prices ?? [] as $price) {
                $value = (float) ($price->price ?? 0);
                if ($value <= 0) {
                    continue;
                }
                if ($min === null || $value < $min) {
                    $min = $value;
                    $priceOld = !empty($price->price_old) ? (float) $price->price_old : null;
                    $saleOff = !empty($price->sale_off) ? (int) $price->sale_off : null;
                }
            }
        }

        return [
            'price' => $min,
            'priceOld' => $priceOld,
            'saleOff' => $saleOff,
        ];
    }

    /**
     * @return array{0: float, 1: int}
     */
    private function aggregateRating(Hotel $item): array
    {
        $total = 0.0;
        $count = 0;

        foreach ($item->comments ?? [] as $comment) {
            $total += (float) ($comment->rating ?? 0);
            ++$count;
        }

        if ($count === 0) {
            return [0.0, 0];
        }

        return [round($total / $count, 1), $count];
    }

    private function resolveAddress(Hotel $item): string
    {
        if (!empty($item->address)) {
            return (string) $item->address;
        }

        $parts = array_filter([
            $item->location->district->district_name ?? null,
            $item->location->province->province_name ?? null,
        ]);

        return implode(', ', $parts);
    }

    private function firstGalleryUrl(Hotel $item, string $variant = 'large'): ?string
    {
        $first = $item->images[0] ?? null;
        if ($first === null) {
            return null;
        }

        return $this->imageUrl($first->image ?? null, $variant);
    }

    private function imageUrl(?string $path, string $variant = 'medium'): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $url = media_variant_url($path, $variant) ?? media_url($path);

        return $url !== null && $url !== '' ? $url : null;
    }
}
