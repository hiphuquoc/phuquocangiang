<?php

declare(strict_types=1);

namespace App\Services\Island;

use App\Models\Tour;
use App\Models\TourLocation;
use App\Services\Cards\HotelCardMapper;
use App\Services\Cards\TourCardMapper;
use App\Services\TourLocationFragmentService;
use Illuminate\Support\Collection;

class HomeIslandService
{
    private const HOME_ITEM_LIMIT = 8;

    public function __construct(
        private readonly IslandContextService $context,
        private readonly TourCardMapper $tourCards,
        private readonly HotelCardMapper $hotelCards,
        private readonly TourLocationFragmentService $fragments,
        private readonly HomeIslandFerryService $ferry,
    ) {}

    /**
     * Payload trang chủ home-v2 — mọi section lấy từ Tour Location cấu hình.
     *
     * @return array<string, mixed>
     */
    public function forHomePage(string $locale = 'vi'): array
    {
        $location = $this->context->locationForHome();
        $name = $this->context->name();

        return [
            'configured' => $this->context->isConfigured() && $location !== null,
            'id' => $this->context->id(),
            'name' => $name,
            'locale' => $locale,
            'quickAccess' => $this->quickAccess($location, $name),
            'tours' => $this->toursSection($location, $name),
            'ferry' => $this->ferrySection($location, $name),
            'services' => $this->servicesSection($location, $name),
            'hotels' => $this->hotelsSection($location, $name),
            'guides' => $this->guidesSection($location, $name),
        ];
    }

    public function cacheStamp(): string
    {
        return $this->context->cacheStamp();
    }

    /**
     * @return array<string, mixed>
     */
    private function quickAccess(?TourLocation $location, string $name): array
    {
        return [
            'eyebrow' => 'Dịch vụ trọn đảo',
            'title' => 'Mọi nhu cầu cho chuyến đi ' . $name . ' chỉ trong <span class="sd-text-grad">một nền tảng</span>',
            'desc' => 'Từ vé tàu Superdong đến lưu trú, tour khám phá và trải nghiệm biển đảo '
                . $name . ', toàn bộ hành trình được thiết kế để bạn đặt nhanh và đi nhẹ nhàng.',
            'cards' => $location ? $this->buildQuickCards($location, $name) : [],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildQuickCards(TourLocation $location, string $name): array
    {
        $cards = [$this->tourLocationQuickCard($location, $name)];

        foreach ([
            fn () => $this->shipQuickCard($location),
            fn () => $this->hotelQuickCard($location, $name),
            fn () => $this->serviceQuickCard($location),
            fn () => $this->airQuickCard($location),
            fn () => $this->guideQuickCard($location, $name),
            fn () => $this->carrentalQuickCard($location, $name),
        ] as $builder) {
            $card = $builder();
            if ($card !== null) {
                $cards[] = $card;
            }
        }

        foreach ($cards as $index => &$card) {
            $card['num'] = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        }
        unset($card);

        return $cards;
    }

    private function tourLocationQuickCard(TourLocation $location, string $name): array
    {
        $display = $location->display_name ?: $location->name ?: $name;
        $tourCount = $location->tours->count();
        $meta = $this->context->excerpt($location->description, '');

        if ($meta === '' && $tourCount > 0) {
            $meta = $tourCount . ' tour khám phá ' . $display;
        } elseif ($meta === '') {
            $meta = 'Khám phá trọn vẹn ' . $display;
        }

        return [
            'tag' => 'Tour',
            'label' => 'Tour ' . $display,
            'meta' => $meta,
            'href' => $this->catUrl($location->seo, 'tours'),
            'image' => $this->context->coverImage($location->seo),
            'cta' => 'Xem tour',
            'large' => true,
        ];
    }

    private function shipQuickCard(TourLocation $location): ?array
    {
        $shipLocation = $location->shipLocations->first()?->infoShipLocation;
        if (!$shipLocation) {
            return null;
        }

        $labelName = $shipLocation->display_name ?: $shipLocation->name;

        return [
            'tag' => 'Vé tàu',
            'label' => 'Vé tàu cao tốc ' . $labelName,
            'meta' => $this->context->excerpt($shipLocation->description, 'Đặt vé nhanh · xác nhận ngay'),
            'href' => $this->catUrl($shipLocation->seo, 'ferry'),
            'image' => $this->context->coverImage($shipLocation->seo),
            'cta' => 'Đặt vé ngay',
        ];
    }

    private function serviceQuickCard(TourLocation $location): ?array
    {
        $serviceLocation = $location->serviceLocations->first()?->infoServiceLocation;
        if (!$serviceLocation) {
            return null;
        }

        $labelName = $serviceLocation->display_name ?: $serviceLocation->name;

        return [
            'tag' => 'Vé vui chơi',
            'label' => 'Vé vui chơi ' . $labelName,
            'meta' => $this->context->excerpt($serviceLocation->description, 'Lặn biển · tour · giải trí'),
            'href' => $this->catUrl($serviceLocation->seo, 'services'),
            'image' => $this->context->coverImage($serviceLocation->seo),
            'cta' => 'Khám phá',
        ];
    }

    private function hotelQuickCard(TourLocation $location, string $name): ?array
    {
        $hotelLocation = $location->hotelLocations->first()?->infoHotelLocation;
        if (!$hotelLocation) {
            return null;
        }

        $labelName = $hotelLocation->display_name ?: $hotelLocation->name ?: $name;
        $hotelCount = ($hotelLocation->hotels ?? collect())
            ->filter(fn ($hotel) => !empty($hotel->seo))
            ->count();

        $meta = $this->context->excerpt($hotelLocation->description, '');
        if ($meta === '' && $hotelCount > 0) {
            $meta = $hotelCount . ' lựa chọn lưu trú tại ' . $labelName;
        } elseif ($meta === '') {
            $meta = 'Khách sạn & homestay view biển · gần trung tâm';
        }

        return [
            'tag' => 'Khách sạn',
            'label' => 'Khách sạn ' . $labelName,
            'meta' => $meta,
            'href' => $this->catUrl($hotelLocation->seo, 'hotels'),
            'image' => $this->context->coverImage($hotelLocation->seo),
            'cta' => 'Xem phòng',
        ];
    }

    private function airQuickCard(TourLocation $location): ?array
    {
        $airLocation = $location->airLocations->first()?->infoAirLocation;
        if (!$airLocation) {
            return null;
        }

        $labelName = $airLocation->display_name ?: $airLocation->name;

        return [
            'tag' => 'Vé máy bay',
            'label' => 'Vé máy bay ' . $labelName,
            'meta' => $this->context->excerpt($airLocation->description, 'Bay thẳng · giá tốt · đặt online'),
            'href' => $this->context->pageUrl($airLocation->seo, $this->catUrl(null, 'booking')),
            'image' => $this->context->coverImage($airLocation->seo),
            'cta' => 'Đặt vé bay',
        ];
    }

    private function guideQuickCard(TourLocation $location, string $name): ?array
    {
        $guide = $location->guides->first()?->infoGuide;
        if (!$guide) {
            return null;
        }

        return [
            'tag' => 'Cẩm nang',
            'label' => 'Cẩm nang ' . ($guide->display_name ?: $guide->name ?: $name),
            'meta' => $this->context->excerpt($guide->description, 'Lịch trình · kinh nghiệm · mẹo hay'),
            'href' => $this->context->pageUrl($guide->seo, $this->catUrl(null, 'guide')),
            'image' => $this->context->coverImage($guide->seo),
            'cta' => 'Đọc ngay',
        ];
    }

    private function carrentalQuickCard(TourLocation $location, string $name): ?array
    {
        $carrentalLocation = $location->carrentalLocations->first()?->infoCarrentalLocation;
        if (!$carrentalLocation) {
            return null;
        }

        return [
            'tag' => 'Thuê xe',
            'label' => 'Thuê xe ' . ($carrentalLocation->name ?: $name),
            'meta' => $this->context->excerpt($carrentalLocation->description ?? null, 'Xe máy · ô tô · đón sân bay'),
            'href' => $this->catUrl($carrentalLocation->seo, 'rental'),
            'image' => $this->context->coverImage($carrentalLocation->seo),
            'cta' => 'Xem thêm',
        ];
    }

    private function catUrl(?object $seo, string $sectionId): string
    {
        return $this->context->categoryUrl($seo, $sectionId, route('main.home') . '#' . ltrim($sectionId, '#'));
    }

    /**
     * @return array<string, mixed>
     */
    private function toursSection(?TourLocation $location, string $name): array
    {
        $head = [
            'eyebrow' => 'Tour nổi bật',
            'title' => 'Trải nghiệm không thể bỏ lỡ tại ' . $name,
            'desc' => 'Tour trong ngày, tour lặn ngắm san hô và hành trình di sản — hướng dẫn viên bản địa am hiểu sâu sắc, nhiệt tình và trung thực.',
            'linkHref' => $location ? $this->catUrl($location->seo, 'tours') : route('main.home') . '#tours',
            'linkLabel' => 'Xem tất cả tour →',
        ];

        if (!$location) {
            return ['head' => $head, 'items' => []];
        }

        $items = $this->fragments->toursForList($location)
            ->take(self::HOME_ITEM_LIMIT)
            ->map(fn (Tour $tour) => $this->tourCards->fromTour($tour, $name))
            ->filter()
            ->values()
            ->all();

        return ['head' => $head, 'items' => $items];
    }

    /**
     * @return array<string, mixed>
     */
    private function ferrySection(?TourLocation $location, string $name): array
    {
        $shipPage = $location?->shipLocations->first()?->infoShipLocation;

        $head = [
            'eyebrow' => 'Superdong Speed Ferry',
            'title' => 'Vé tàu cao tốc đi ' . $name,
            'desc' => 'Chọn tuyến phù hợp — thời gian di chuyển minh bạch, giá tốt nhất mỗi ngày.',
            'linkHref' => $shipPage ? $this->catUrl($shipPage->seo, 'ferry') : route('main.home') . '#ferry',
            'linkLabel' => 'Xem tất cả tuyến →',
        ];

        if (!$location) {
            return ['head' => $head, 'routes' => []];
        }

        return [
            'head' => $head,
            'routes' => $this->ferry->routesFor($location, $name),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function servicesSection(?TourLocation $location, string $name): array
    {
        $servicePage = $location?->serviceLocations->first()?->infoServiceLocation;

        $head = [
            'eyebrow' => 'Giải trí & trải nghiệm',
            'title' => 'Vé vui chơi & hoạt động biển',
            'desc' => 'Đặt trước để giữ chỗ — khám phá ' . $name . ' với các hoạt động biển đảo và trải nghiệm địa phương.',
            'linkHref' => $servicePage ? $this->catUrl($servicePage->seo, 'services') : route('main.home') . '#services',
            'linkLabel' => 'Xem tất cả →',
        ];

        if (!$location) {
            return ['head' => $head, 'items' => []];
        }

        $items = $this->fragments->servicesForList($location)
            ->take(self::HOME_ITEM_LIMIT)
            ->map(function ($service) {
                $seo = $service->seo;
                if (!$seo) {
                    return null;
                }

                $title = $service->name ?: ($seo->title ?? '');
                $duration = trim((string) (($service->time_start ?? '') . (!empty($service->time_end) ? ' – ' . $service->time_end : '')));

                return [
                    'image' => $this->context->coverImage($seo, 'small'),
                    'alt' => $title,
                    'category' => 'Trải nghiệm',
                    'duration' => $duration !== '' ? $duration : null,
                    'title' => $title,
                    'price' => $this->context->formatPrice(!empty($service->price_show) ? (float) $service->price_show : null),
                    'facts' => [
                        ['icon' => 'tag', 'text_key' => 'service_e_ticket'],
                        ['icon' => 'tag', 'text_key' => 'card_service_hot_deals'],
                    ],
                    'ctaHref' => $this->context->pageUrl($seo),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return ['head' => $head, 'items' => $items];
    }

    /**
     * @return array<string, mixed>
     */
    private function hotelsSection(?TourLocation $location, string $name): array
    {
        $hotelPage = $location?->hotelLocations->first()?->infoHotelLocation;

        $head = [
            'eyebrow' => 'Nghỉ dưỡng',
            'title' => 'Khách sạn & homestay ' . $name,
            'desc' => 'View biển, gần trung tâm hoặc yên tĩnh giữa thiên nhiên — chọn mức giá phù hợp hành trình của bạn.',
            'linkHref' => $hotelPage ? $this->catUrl($hotelPage->seo, 'hotels') : route('main.home') . '#hotels',
            'linkLabel' => 'Xem tất cả →',
        ];

        if (!$location) {
            return ['head' => $head, 'items' => []];
        }

        $locationName = $name;
        $items = [];

        foreach ($location->hotelLocations as $relation) {
            $hotelLocation = $relation->infoHotelLocation;
            if (!$hotelLocation || empty($hotelLocation->hotels)) {
                continue;
            }

            foreach ($hotelLocation->hotels as $hotel) {
                $card = $this->hotelCards->fromHotel($hotel, $locationName);
                if ($card === null) {
                    continue;
                }

                $items[] = $card;

                if (count($items) >= self::HOME_ITEM_LIMIT) {
                    break 2;
                }
            }
        }

        return ['head' => $head, 'items' => $items];
    }

    /**
     * @return array<string, mixed>
     */
    private function guidesSection(?TourLocation $location, string $name): array
    {
        $head = [
            'title' => 'Bí kíp khám phá ' . $name,
            'desc' => 'Lịch trình, kinh nghiệm đi lại và gợi ý ẩm thực — cập nhật cho chuyến đi ' . $name . '.',
        ];

        if (!$location) {
            return ['head' => $head, 'items' => [], 'images' => []];
        }

        $items = [];
        $images = [];

        foreach ($location->guides as $relation) {
            $guide = $relation->infoGuide;
            if (!$guide?->seo) {
                continue;
            }

            $title = $guide->display_name ?: $guide->name ?: ($guide->seo->title ?? '');
            $items[] = [
                'href' => $this->context->pageUrl($guide->seo, $this->catUrl(null, 'guide')),
                'title' => $title,
            ];

            $image = $this->context->coverImage($guide->seo);
            if (!in_array($image, $images, true)) {
                $images[] = $image;
            }

            if (count($items) >= 6) {
                break;
            }
        }

        while (count($images) < 3) {
            $images[] = $this->context->coverImage($location->seo);
        }

        return [
            'head' => $head,
            'items' => $items,
            'images' => array_slice($images, 0, 3),
        ];
    }
}
