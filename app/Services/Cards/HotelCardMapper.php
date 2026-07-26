<?php

declare(strict_types=1);

namespace App\Services\Cards;

use App\Helpers\Charactor;
use App\Models\Hotel;
use App\Services\Island\IslandContextService;

class HotelCardMapper
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function fromHotel(Hotel $hotel, string $locationName = ''): ?array
    {
        $seo = $hotel->seo;
        if (!$seo || !$this->hasRooms($hotel)) {
            return null;
        }

        $title = $hotel->name ?: ($seo->title ?? '');
        $displayTitle = $locationName !== '' ? ($locationName . ': ' . $title) : $title;

        $rating = 0.0;
        $ratingCount = 0;
        if (!empty($hotel->comments) && $hotel->comments->isNotEmpty()) {
            $total = 0.0;
            foreach ($hotel->comments as $comment) {
                $total += (float) ($comment->rating ?? 0);
                ++$ratingCount;
            }
            if ($ratingCount > 0) {
                $rating = round($total / $ratingCount, 1);
            }
        }

        $typeSlug = trim((string) Charactor::convertStrToUrl((string) ($hotel->type_name ?? '')));
        $filterTags = ['tat-ca-khach-san'];
        if ($typeSlug !== '') {
            $filterTags[] = $typeSlug;
        }

        $facts = array_values(array_filter([
            !empty($hotel->address) ? ['icon' => 'pin', 'text' => (string) $hotel->address] : null,
            !empty($hotel->type_name) ? ['icon' => 'tag', 'text' => (string) $hotel->type_name] : null,
        ]));

        return [
            'image' => $this->context->coverImage($seo, 'small'),
            'alt' => $title,
            'title' => $displayTitle,
            'rating' => $ratingCount > 0 ? $rating : null,
            'price' => $this->context->formatPrice($this->minPrice($hotel)),
            'facts' => $facts,
            'ctaHref' => $this->detailUrl($hotel),
            'filterHotel' => implode(' ', $filterTags),
        ];
    }

    private function detailUrl(Hotel $hotel): string
    {
        $seo = $hotel->seo;
        if (!$seo) {
            return '#';
        }

        $path = trim(seo_url($seo));
        if ($path !== '' && $path !== '/' && str_contains($path, '/')) {
            return $path;
        }

        $slugFull = (string) ($seo->getRawOriginal('slug_full') ?: $seo->getRawOriginal('slug') ?: '');
        if ($slugFull !== '') {
            return '/' . ltrim($slugFull, '/');
        }

        return $this->context->pageUrl($seo);
    }

    private function minPrice(Hotel $hotel): ?float
    {
        $min = null;

        foreach ($hotel->rooms ?? [] as $room) {
            foreach ($room->prices ?? [] as $price) {
                $value = (float) ($price->price ?? 0);
                if ($value <= 0) {
                    continue;
                }
                $min = $min === null ? $value : min($min, $value);
            }
        }

        return $min;
    }

    /**
     * Khách sạn đủ điều kiện hiển thị listing (giống grid cũ: phải có phòng).
     */
    public function hasRooms(Hotel $hotel): bool
    {
        return ($hotel->rooms ?? collect())->isNotEmpty();
    }
}
