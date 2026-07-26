<?php

declare(strict_types=1);

namespace App\Services\HomeHero;

use App\Helpers\Time;
use App\Models\HomeHeroRouteSlot;
use App\Models\Ship;
use App\Models\ShipLocation;
use App\Models\ShipPrice;
use App\Services\Island\IslandContextService;
use Illuminate\Support\Collection;

class HomeHeroRouteResolver
{
    public function __construct(
        private readonly IslandContextService $islandContext,
    ) {}
    /**
     * @param  Collection<int, HomeHeroRouteSlot>  $slots
     * @return array<int, array<string, mixed>>
     */
    public function resolveMany(Collection $slots): array
    {
        $routes = [];
        $usedDepartureKeys = [];
        $maxRoutes = min(2, $slots->count());

        foreach ($slots as $slot) {
            if (count($routes) >= $maxRoutes) {
                break;
            }

            if (empty($slot->ship_location_id)) {
                continue;
            }

            $route = $this->resolveOne($slot, $usedDepartureKeys);
            if ($route !== null) {
                $routes[] = $route;
            }
        }

        // Chỉ chọn 1 location: tự bổ sung thẻ thứ 2 từ điểm xuất phát khác (nếu có).
        if (count($routes) < $maxRoutes) {
            $primarySlot = $slots->first(fn (HomeHeroRouteSlot $slot) => !empty($slot->ship_location_id));
            if ($primarySlot) {
                while (count($routes) < $maxRoutes) {
                    $route = $this->resolveOne($primarySlot, $usedDepartureKeys);
                    if ($route === null) {
                        break;
                    }
                    $routes[] = $route;
                }
            }
        }

        return $routes;
    }

    /**
     * @param  array<int, string>  $usedDepartureKeys
     */
    public function resolveOne(HomeHeroRouteSlot $slot, array &$usedDepartureKeys = []): ?array
    {
        $location = ShipLocation::query()
            ->with([
                'seo',
                'ships.departure.district',
                'ships.departure.province',
                'ships.portDeparture',
                'ships.prices.times',
            ])
            ->find($slot->ship_location_id);

        if (!$location) {
            return null;
        }

        $ship = $this->pickCheapestShipWithUniqueDeparture($location->ships, $usedDepartureKeys);
        if (!$ship) {
            return null;
        }

        $price = $this->resolveMinPrice($ship);
        if ($price === null) {
            return null;
        }

        $usedDepartureKeys[] = $this->departureKey($ship);

        return [
            'from' => $this->resolveFromLabel($ship),
            'to' => $location->display_name ?: $location->name,
            'duration' => $this->resolveDuration($ship),
            'price' => number_format($price, 0, ',', '.'),
            'href' => $this->resolveLink($location),
            'ship_location_id' => $location->id,
        ];
    }

    /**
     * @param  Collection<int, Ship>|null  $ships
     * @param  array<int, string>  $excludeDepartureKeys
     */
    private function pickCheapestShipWithUniqueDeparture(?Collection $ships, array $excludeDepartureKeys): ?Ship
    {
        if (!$ships || $ships->isEmpty()) {
            return null;
        }

        $candidates = $ships
            ->filter(fn (Ship $ship) => $this->resolveMinPrice($ship) !== null)
            ->filter(fn (Ship $ship) => !in_array($this->departureKey($ship), $excludeDepartureKeys, true));

        if ($candidates->isEmpty()) {
            return null;
        }

        // Gom theo điểm xuất phát, lấy chuyến rẻ nhất mỗi điểm rồi sort theo giá.
        $byDeparture = $candidates
            ->groupBy(fn (Ship $ship) => $this->departureKey($ship))
            ->map(fn (Collection $group) => $group->sortBy(fn (Ship $ship) => $this->resolveMinPrice($ship))->first());

        return $byDeparture
            ->sortBy(fn (Ship $ship) => $this->resolveMinPrice($ship))
            ->first();
    }

    private function departureKey(Ship $ship): string
    {
        if (!empty($ship->ship_port_departure_id)) {
            return 'port:' . $ship->ship_port_departure_id;
        }

        if (!empty($ship->ship_departure_id)) {
            return 'dep:' . $ship->ship_departure_id;
        }

        return 'label:' . mb_strtolower(trim($this->resolveFromLabel($ship)));
    }

    private function resolveFromLabel(Ship $ship): string
    {
        if (!empty($ship->portDeparture?->name)) {
            return (string) $ship->portDeparture->name;
        }

        $district = $ship->departure?->district?->district_name;
        if (!empty($district)) {
            return (string) $district;
        }

        $province = $ship->departure?->province?->province_name;
        if (!empty($province)) {
            return (string) $province;
        }

        if (!empty($ship->departure?->name)) {
            return (string) $ship->departure->name;
        }

        return '—';
    }

    private function resolveDuration(Ship $ship): string
    {
        $prices = $ship->prices ?? collect();
        foreach ($prices as $price) {
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

        return '~2h';
    }

    private function resolveMinPrice(Ship $ship): ?float
    {
        if (!$ship->relationLoaded('prices')) {
            $ship->load('prices');
        }

        $min = ($ship->prices ?? collect())
            ->map(fn (ShipPrice $price) => (float) ($price->price_adult ?? 0))
            ->filter(fn (float $value) => $value > 0)
            ->min();

        return $min !== null ? (float) $min : null;
    }

    private function resolveLink(ShipLocation $location): string
    {
        return $this->islandContext->categoryUrl(
            $location->seo,
            'booking',
            route('main.home') . '#booking',
        );
    }
}
