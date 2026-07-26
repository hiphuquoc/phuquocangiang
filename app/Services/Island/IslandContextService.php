<?php

declare(strict_types=1);

namespace App\Services\Island;

use App\Models\TourLocation;
use Illuminate\Support\Str;

class IslandContextService
{
    private ?TourLocation $location = null;

    private bool $loaded = false;

    public function id(): int
    {
        return (int) config('island.tour_location_id', 0);
    }

    public function isConfigured(): bool
    {
        return $this->id() > 0;
    }

    public function location(): ?TourLocation
    {
        if ($this->loaded) {
            return $this->location;
        }

        $this->loaded = true;
        $id = $this->id();

        if ($id <= 0) {
            return null;
        }

        $this->location = TourLocation::query()
            ->where('id', $id)
            ->with($this->defaultRelations())
            ->first();

        return $this->location;
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultRelations(): array
    {
        return [
            'seo',
            'tours.infoTour' => fn ($q) => $q->where('status_show', 1),
            'tours.infoTour.seo',
            'shipLocations.infoShipLocation.seo',
            'shipLocations.infoShipLocation.district',
            'shipLocations.infoShipLocation.province',
            'shipLocations.infoShipLocation.ships' => fn ($q) => $q->whereHas(
                'prices',
                fn ($p) => $p->where('price_adult', '>', 0)
            ),
            'shipLocations.infoShipLocation.ships.seo',
            'shipLocations.infoShipLocation.ships.portLocation',
            'shipLocations.infoShipLocation.ships.departure.district',
            'shipLocations.infoShipLocation.ships.departure.province',
            'shipLocations.infoShipLocation.ships.portDeparture',
            'shipLocations.infoShipLocation.ships.prices.times',
            'hotelLocations.infoHotelLocation.seo',
            'hotelLocations.infoHotelLocation.hotels' => fn ($q) => $q
                ->whereHas('rooms')
                ->limit(8),
            'hotelLocations.infoHotelLocation.hotels.seo',
            'hotelLocations.infoHotelLocation.hotels.rooms.prices',
            'hotelLocations.infoHotelLocation.hotels.comments',
            'serviceLocations.infoServiceLocation.seo',
            'serviceLocations.infoServiceLocation.services.seo',
            'airLocations.infoAirLocation.seo',
            'guides.infoGuide.seo',
            'guides.infoGuide.seo',
            'carrentalLocations.infoCarrentalLocation.seo',
            'destinations.infoCategory.seo',
            'specials.infoCategory.seo',
        ];
    }

    public function name(): string
    {
        $location = $this->location();

        if ($location) {
            return (string) ($location->display_name ?: $location->name ?: config('island.name_fallback'));
        }

        return (string) config('island.name_fallback', 'Đảo');
    }

    public function cacheStamp(): string
    {
        return $this->id() . '|' . (string) ($this->location()?->updated_at ?? '0');
    }

    public function pageUrl(?object $seo, string $fallback = '#'): string
    {
        $slug = $seo->slug_full ?? $seo->slug ?? null;
        if (empty($slug)) {
            return $fallback;
        }

        return url('/' . ltrim((string) $slug, '/'));
    }

    /**
     * URL trang danh mục kèm anchor section (tour, ferry, services…).
     */
    public function categoryUrl(?object $seo, string $sectionId = '', string $fallback = '#'): string
    {
        $slug = $seo->slug_full ?? $seo->slug ?? null;

        if (empty($slug)) {
            if ($fallback !== '#' && $fallback !== '') {
                return $fallback;
            }

            if ($sectionId !== '') {
                return route('main.home') . '#' . ltrim($sectionId, '#');
            }

            return $fallback;
        }

        $url = url('/' . ltrim((string) $slug, '/'));

        if ($sectionId !== '') {
            $url .= '#' . ltrim($sectionId, '#');
        }

        return $url;
    }

    public function coverImage(?object $seo, string $variant = 'medium'): string
    {
        if ($seo === null) {
            return (string) config('admin.images.default_750x460');
        }

        $base = $seo->getRawOriginal('image') ?? $seo->getRawOriginal('image_small') ?? null;

        if (!empty($base)) {
            $url = media_variant_url($base, $variant);
            if (!empty($url)) {
                return $url;
            }
        }

        return (string) config('admin.images.default_750x460');
    }

    public function excerpt(?string $text, string $fallback = '', int $limit = 72): string
    {
        $plain = trim(strip_tags((string) $text));
        if ($plain === '') {
            return $fallback;
        }

        return Str::limit($plain, $limit);
    }

    public function formatPrice(?float $amount): ?string
    {
        if ($amount === null || $amount <= 0) {
            return null;
        }

        return number_format($amount, 0, ',', '.');
    }
}
