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
            'eyebrow' => 'Dịch vụ trọn đảo chính hãng',
            'title' => 'Mọi Nhu Cầu Chuyến Đi ' . $name . ' Trong <span class="sd-text-grad">Một Nền Tảng</span>',
            'desc' => 'Từ vé tàu cao tốc niêm yết, khách sạn view biển, tour cano hòn đến thuê xe di chuyển — Toàn bộ hành trình được thiết kế chuẩn xác, giữ chỗ 100% và nhận vé QR tức thì.',
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
            $meta = $tourCount . ' tour khám phá ' . $display . ' hấp dẫn';
        } elseif ($meta === '') {
            $meta = 'Tour cano 4 hòn, lặn ngắm san hô & lịch trình trọn gói ' . $display;
        }

        return [
            'tag' => 'Tour trải nghiệm',
            'label' => 'Tour ' . $display,
            'meta' => $meta,
            'href' => $this->catUrl($location->seo, 'tours'),
            'image' => $this->context->coverImage($location->seo),
            'cta' => 'Xem tour ngay',
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
            'tag' => 'Vé tàu cao tốc',
            'label' => 'Vé Tàu Cao Tốc ' . $labelName,
            'meta' => $this->context->excerpt($shipLocation->description, 'Lịch tàu chuẩn · Giá niêm yết · Nhận vé QR 30s'),
            'href' => $this->catUrl($shipLocation->seo, 'ferry'),
            'image' => $this->context->coverImage($shipLocation->seo),
            'cta' => 'Đặt vé tàu',
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
            'label' => 'Vé Vui Chơi & Giải Trí ' . $labelName,
            'meta' => $this->context->excerpt($serviceLocation->description, 'Cáp treo Hòn Thơm · VinWonders · Lặn biển'),
            'href' => $this->catUrl($serviceLocation->seo, 'services'),
            'image' => $this->context->coverImage($serviceLocation->seo),
            'cta' => 'Đặt vé vui chơi',
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
            $meta = $hotelCount . ' khách sạn & resort view biển tại ' . $labelName;
        } elseif ($meta === '') {
            $meta = 'Resort sát biển & khách sạn trung tâm vị trí đắc địa';
        }

        return [
            'tag' => 'Khách sạn & Resort',
            'label' => 'Khách Sạn & Resort ' . $labelName,
            'meta' => $meta,
            'href' => $this->catUrl($hotelLocation->seo, 'hotels'),
            'image' => $this->context->coverImage($hotelLocation->seo),
            'cta' => 'Xem phòng tốt',
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
            'label' => 'Vé Máy Bay ' . $labelName,
            'meta' => $this->context->excerpt($airLocation->description, 'Bay thẳng đến đảo · Giữ chỗ trực tuyến'),
            'href' => $this->context->pageUrl($airLocation->seo, $this->catUrl(null, 'booking')),
            'image' => $this->context->coverImage($airLocation->seo),
            'cta' => 'Săn vé bay',
        ];
    }

    private function guideQuickCard(TourLocation $location, string $name): ?array
    {
        $guide = $location->guides->first()?->infoGuide;
        if (!$guide) {
            return null;
        }

        return [
            'tag' => 'Cẩm nang du lịch',
            'label' => 'Cẩm Nang Du Lịch ' . ($guide->display_name ?: $guide->name ?: $name),
            'meta' => $this->context->excerpt($guide->description, 'Lịch trình 3N2Đ · Mẹo đi tàu không say · Quán ăn ngon'),
            'href' => $this->context->pageUrl($guide->seo, $this->catUrl(null, 'guide')),
            'image' => $this->context->coverImage($guide->seo),
            'cta' => 'Đọc cẩm nang',
        ];
    }

    private function carrentalQuickCard(TourLocation $location, string $name): ?array
    {
        $carrentalLocation = $location->carrentalLocations->first()?->infoCarrentalLocation;
        if (!$carrentalLocation) {
            return null;
        }

        return [
            'tag' => 'Thuê xe di chuyển',
            'label' => 'Thuê Xe Máy & Ô Tô ' . ($carrentalLocation->name ?: $name),
            'meta' => $this->context->excerpt($carrentalLocation->description ?? null, 'Giao xe tận bến tàu · Xe mới chất lượng'),
            'href' => $this->catUrl($carrentalLocation->seo, 'rental'),
            'image' => $this->context->coverImage($carrentalLocation->seo),
            'cta' => 'Xem bảng giá xe',
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
            'eyebrow' => 'Tour trải nghiệm hấp dẫn',
            'title' => 'Tour Khám Phá Biển Đảo ' . $name . ' Nổi Bật Bật Nhất',
            'desc' => 'Tour cano 4 hòn, lặn ngắm san hô tự nhiên và hành trình di sản — Hướng dẫn viên bản địa am hiểu, nhiệt tình, giá tour minh bạch 100%.',
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
            'eyebrow' => 'Vé tàu cao tốc chính hãng',
            'title' => 'Lịch Tàu & Bảng Giá Vé Tàu Cao Tốc Đi ' . $name,
            'desc' => 'Tra cứu lịch tàu chạy liên tục trong ngày — Giá niêm yết chính hãng, giữ chỗ 100% và nhận vé điện tử QR code tức thì trong 30 giây.',
            'linkHref' => $shipPage ? $this->catUrl($shipPage->seo, 'ferry') : route('main.home') . '#ferry',
            'linkLabel' => 'Xem tất cả tuyến tàu →',
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
            'eyebrow' => 'Vé vui chơi & giải trí',
            'title' => 'Vé Tham Quan & Hoạt Động Biển Đảo ' . $name,
            'desc' => 'Đặt trước vé cáp treo Hòn Thơm, VinWonders, Safari và trải nghiệm lặn biển — Nhận vé QR quét vào cổng trực tiếp không chờ đợi.',
            'linkHref' => $servicePage ? $this->catUrl($servicePage->seo, 'services') : route('main.home') . '#services',
            'linkLabel' => 'Xem tất cả dịch vụ →',
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
            'eyebrow' => 'Lưu trú bãi biển',
            'title' => 'Khách Sạn & Resort View Biển Đẹp Nhất ' . $name,
            'desc' => 'Tự do lựa chọn từ resort 5 sao sang trọng đến bungalow sát bãi biển và homestay trung tâm với mức giá ưu đãi tốt nhất.',
            'linkHref' => $hotelPage ? $this->catUrl($hotelPage->seo, 'hotels') : route('main.home') . '#hotels',
            'linkLabel' => 'Xem tất cả khách sạn →',
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
