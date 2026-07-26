<?php

declare(strict_types=1);

namespace App\Services\Island;

use App\Helpers\Time;
use App\Models\Ship;
use App\Models\ShipLocation;
use App\Models\ShipPrice;
use App\Models\TourLocation;
use Illuminate\Support\Collection;

/**
 * Build payload Superdong Speed Ferry — một card / điểm khởi hành.
 */
class HomeIslandFerryService
{
    private const MAX_ROUTES = 6;

    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function routesFor(TourLocation $location, string $islandName): array
    {
        /** @var array<string, array{ships: Collection<int, Ship>, shipLocation: ShipLocation, anchor: Ship}> $groups */
        $groups = [];

        foreach ($location->shipLocations as $relation) {
            $shipLocation = $relation->infoShipLocation;
            if (!$shipLocation) {
                continue;
            }

            foreach ($shipLocation->ships ?? [] as $ship) {
                if (!$this->shipHasBookablePrice($ship)) {
                    continue;
                }

                $key = $this->departureKey($ship);
                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'ships' => collect(),
                        'shipLocation' => $shipLocation,
                        'anchor' => $ship,
                    ];
                }

                $groups[$key]['ships']->push($ship);

                if ($this->minAdultPrice($ship) < $this->minAdultPrice($groups[$key]['anchor'])) {
                    $groups[$key]['anchor'] = $ship;
                }
            }
        }

        $routes = [];
        foreach ($groups as $group) {
            $route = $this->mapDepartureGroup($group, $islandName);
            if ($route !== null) {
                $routes[] = $route;
            }
        }

        usort($routes, fn (array $a, array $b) => ($a['_sort_price'] ?? PHP_INT_MAX) <=> ($b['_sort_price'] ?? PHP_INT_MAX));

        return array_map(function (array $route) {
            unset($route['_sort_price']);

            return $route;
        }, array_slice($routes, 0, self::MAX_ROUTES));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function routesForShipLocation(ShipLocation $shipLocation, string $islandName): array
    {
        /** @var array<string, array{ships: Collection<int, Ship>, shipLocation: ShipLocation, anchor: Ship}> $groups */
        $groups = [];

        foreach ($shipLocation->ships ?? [] as $ship) {
            if (!$ship instanceof Ship || !$this->shipHasBookablePrice($ship)) {
                continue;
            }

            $key = $this->departureKey($ship);
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'ships' => collect(),
                    'shipLocation' => $shipLocation,
                    'anchor' => $ship,
                ];
            }

            $groups[$key]['ships']->push($ship);

            if ($this->minAdultPrice($ship) < $this->minAdultPrice($groups[$key]['anchor'])) {
                $groups[$key]['anchor'] = $ship;
            }
        }

        $routes = [];
        foreach ($groups as $group) {
            $route = $this->mapDepartureGroup($group, $islandName);
            if ($route !== null) {
                $routes[] = $route;
            }
        }

        usort($routes, fn (array $a, array $b) => ($a['_sort_price'] ?? PHP_INT_MAX) <=> ($b['_sort_price'] ?? PHP_INT_MAX));

        return array_map(function (array $route) {
            unset($route['_sort_price']);

            return $route;
        }, $routes);
    }

    /**
     * @param  array{ships: Collection<int, Ship>, shipLocation: ShipLocation, anchor: Ship}  $group
     * @return array<string, mixed>|null
     */
    private function mapDepartureGroup(array $group, string $islandName): ?array
    {
        /** @var Ship $anchor */
        $anchor = $group['anchor'];
        /** @var ShipLocation $shipLocation */
        $shipLocation = $group['shipLocation'];
        /** @var Collection<int, Ship> $ships */
        $ships = $group['ships'];

        $allPrices = $ships
            ->flatMap(fn (Ship $ship) => $ship->prices ?? collect())
            ->filter(fn (ShipPrice $price) => (float) ($price->price_adult ?? 0) > 0)
            ->values();

        if ($allPrices->isEmpty()) {
            return null;
        }

        $fromLabels = $this->resolveFromLabels($anchor);
        $toLabels = $this->resolveToLabels($anchor, $shipLocation, $islandName);
        $fareGroups = $this->resolveFareGroups($allPrices);

        if ($fareGroups === []) {
            return null;
        }

        $minAdult = $this->minPriceField($allPrices, 'price_adult');
        $detailHref = $this->detailUrl($anchor);
        $bookingHref = $this->bookingUrl($anchor);

        return array_merge($fromLabels, $toLabels, [
            'title' => (string) ($anchor->name ?: ($anchor->seo->title ?? ($fromLabels['from'] . ' → ' . ($toLabels['to'] ?? '')))),
            'image' => $this->context->coverImage($anchor->seo, 'small'),
            'imageAlt' => (string) ($anchor->name ?: ($anchor->seo->title ?? '')),
            'duration' => $this->resolveDuration($ships),
            'schedules' => $this->resolveSchedules($allPrices),
            'fareGroups' => $fareGroups,
            'priceFrom' => $this->context->formatPrice($minAdult),
            'detailHref' => $detailHref,
            'bookingHref' => $bookingHref,
            'href' => $bookingHref,
            '_sort_price' => $minAdult ?? PHP_INT_MAX,
        ]);
    }

    /**
     * Card từng tàu cho trang danh mục ship location (1 card = 1 tuyến/vé).
     *
     * @return array<int, array<string, mixed>>
     */
    public function shipCardsForLocation(ShipLocation $shipLocation, string $islandName): array
    {
        $cards = [];

        foreach ($shipLocation->ships ?? [] as $ship) {
            if (!$ship instanceof Ship || !$this->shipHasBookablePrice($ship)) {
                continue;
            }

            $prices = ($ship->prices ?? collect())
                ->filter(fn (ShipPrice $price) => (float) ($price->price_adult ?? 0) > 0)
                ->values();

            if ($prices->isEmpty()) {
                continue;
            }

            $fareGroups = $this->resolveFareGroups($prices);
            if ($fareGroups === []) {
                continue;
            }

            $fromLabels = $this->resolveFromLabels($ship);
            $toLabels = $this->resolveToLabels($ship, $shipLocation, $islandName);
            $minAdult = $this->minPriceField($prices, 'price_adult');
            $title = (string) ($ship->name ?: ($ship->seo->title ?? ($fromLabels['from'] . ' → ' . $toLabels['to'])));

            $cards[] = array_merge($fromLabels, $toLabels, [
                'title' => $title,
                'image' => $this->context->coverImage($ship->seo, 'small'),
                'imageAlt' => $title,
                'duration' => $this->resolveDuration(collect([$ship])),
                'schedules' => $this->resolveSchedules($prices),
                'fareGroups' => $fareGroups,
                'priceFrom' => $this->context->formatPrice($minAdult),
                'detailHref' => $this->detailUrl($ship),
                'bookingHref' => $this->bookingUrl($ship),
                'href' => $this->bookingUrl($ship),
                '_sort_price' => $minAdult ?? PHP_INT_MAX,
            ]);
        }

        usort($cards, fn (array $a, array $b) => ($a['_sort_price'] ?? PHP_INT_MAX) <=> ($b['_sort_price'] ?? PHP_INT_MAX));

        return array_map(function (array $card) {
            unset($card['_sort_price']);

            return $card;
        }, $cards);
    }

    private function detailUrl(Ship $ship): string
    {
        $seo = $ship->seo ?? null;
        if ($seo === null) {
            return '#';
        }

        $path = trim((string) seo_url($seo));
        if ($path !== '' && $path !== '/') {
            return $path;
        }

        return $this->context->pageUrl($seo, '#');
    }

    private function bookingUrl(Ship $ship): string
    {
        return booking_route('shipBooking.form', [
            'ship_port_departure_id' => $ship->ship_port_departure_id,
            'ship_port_location_id' => $ship->ship_port_location_id,
        ]);
    }

    private function shipHasBookablePrice(Ship $ship): bool
    {
        return $this->minAdultPrice($ship) !== null;
    }

    private function minAdultPrice(Ship $ship): ?float
    {
        if (!$ship->relationLoaded('prices')) {
            $ship->load('prices.times');
        }

        return $this->minPriceField($ship->prices ?? collect(), 'price_adult');
    }

    /**
     * @param  Collection<int, ShipPrice>  $prices
     */
    private function minPriceField(Collection $prices, string $field): ?float
    {
        $min = $prices
            ->map(fn (ShipPrice $price) => (float) ($price->{$field} ?? 0))
            ->filter(fn (float $value) => $value > 0)
            ->min();

        return $min !== null ? (float) $min : null;
    }

    /**
     * @return array{from: string, fromSub: string}
     */
    private function resolveFromLabels(Ship $ship): array
    {
        $depName = trim((string) ($ship->departure?->display_name ?: $ship->departure?->name ?: ''));
        $port = trim((string) ($ship->portDeparture?->name ?? ''));
        $district = trim((string) ($ship->departure?->district?->district_name ?? ''));
        $province = trim((string) ($ship->departure?->province?->province_name ?? ''));

        if ($depName !== '') {
            return [
                'from' => $depName,
                'fromSub' => $port !== '' && $port !== $depName ? $port : ($district !== '' && $district !== $depName ? $district : ''),
            ];
        }

        if ($port !== '') {
            return [
                'from' => $port,
                'fromSub' => $district !== '' && $district !== $port
                    ? $district
                    : ($province !== '' && $province !== $port ? $province : ''),
            ];
        }

        if ($district !== '') {
            return [
                'from' => $district,
                'fromSub' => $province !== '' && $province !== $district ? $province : '',
            ];
        }

        return ['from' => $province ?: '—', 'fromSub' => ''];
    }

    /**
     * @return array{to: string, toSub: string}
     */
    private function resolveToLabels(Ship $ship, ShipLocation $shipLocation, string $islandName): array
    {
        $to = (string) ($shipLocation->display_name ?: $shipLocation->name ?: $islandName);
        $toSub = trim((string) (
            $ship->portLocation?->name
            ?: $shipLocation->district?->district_name
            ?: $shipLocation->province?->province_name
            ?: ''
        ));

        if ($toSub !== '' && $toSub === $to) {
            $toSub = '';
        }

        return ['to' => $to, 'toSub' => $toSub];
    }

    /**
     * @param  Collection<int, Ship>  $ships
     */
    private function resolveDuration(Collection $ships): string
    {
        foreach ($ships as $ship) {
            foreach ($ship->prices ?? [] as $price) {
                $timeMove = $price->times[0]->time_move ?? null;
                if (empty($timeMove)) {
                    continue;
                }

                $label = Time::convertMkToTimeMove($timeMove);
                if (!empty($label)) {
                    return '~' . str_replace(' ', '', $label);
                }
            }

            if (!empty($ship->name_round)) {
                return (string) $ship->name_round;
            }
        }

        return '~2h';
    }

    /**
     * @param  Collection<int, ShipPrice>  $prices
     * @return array<int, string>
     */
    private function resolveSchedules(Collection $prices): array
    {
        $times = [];

        foreach ($prices as $price) {
            foreach ($price->times ?? [] as $time) {
                if (!empty($time->time_departure)) {
                    $times[] = (string) $time->time_departure;
                }
            }
        }

        $times = array_values(array_unique($times));
        sort($times);

        return array_slice($times, 0, 8);
    }

    /**
     * @param  Collection<int, ShipPrice>  $prices
     * @return array<int, array<string, mixed>>
     */
    private function resolveFareGroups(Collection $prices): array
    {
        $tiers = array_values(array_filter([
            $this->fareTier('Người lớn', 'Từ 12 tuổi', $this->minPriceField($prices, 'price_adult')),
            $this->fareTier('Trẻ em', '6–11 tuổi', $this->minPriceField($prices, 'price_child')),
            $this->fareTier('Người cao tuổi', 'Trên 60 tuổi', $this->minPriceField($prices, 'price_old')),
        ]));

        $groups = [];
        if ($tiers !== []) {
            $groups[] = [
                'label' => 'Ghế phổ thông',
                'tiers' => $tiers,
            ];
        }

        $vipMin = $this->minPriceField($prices, 'price_vip');
        if ($vipMin !== null) {
            $groups[] = [
                'label' => 'Ghế VIP',
                'uniform' => $this->context->formatPrice($vipMin),
                'uniformHint' => 'Đồng giá mọi độ tuổi',
            ];
        }

        return $groups;
    }

    /**
     * @return array<string, string>|null
     */
    private function fareTier(string $label, string $hint, ?float $amount): ?array
    {
        $formatted = $this->context->formatPrice($amount);
        if ($formatted === null) {
            return null;
        }

        return [
            'label' => $label,
            'hint' => $hint,
            'amount' => $formatted,
        ];
    }

    private function departureKey(Ship $ship): string
    {
        if (!empty($ship->ship_port_departure_id)) {
            return 'port:' . $ship->ship_port_departure_id;
        }

        if (!empty($ship->ship_departure_id)) {
            return 'dep:' . $ship->ship_departure_id;
        }

        $labels = $this->resolveFromLabels($ship);

        return 'label:' . mb_strtolower(trim($labels['from']));
    }
}
