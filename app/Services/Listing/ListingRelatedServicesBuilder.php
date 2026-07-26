<?php

declare(strict_types=1);

namespace App\Services\Listing;

use App\Models\CarrentalLocation;
use App\Models\Guide;
use App\Models\HotelLocation;
use App\Models\ServiceLocation;
use App\Models\ShipLocation;
use App\Models\TourLocation;
use App\Services\TourLocation\TourLocationRelatedServicesBuilder;

/**
 * Cross-sell dịch vụ liên quan — tái sử dụng builder tour, loại trừ trang hiện tại.
 */
class ListingRelatedServicesBuilder
{
    public function __construct(
        private readonly TourLocationRelatedServicesBuilder $tourBuilder,
    ) {}

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    public function forShipLocation(ShipLocation $item, string $name): array
    {
        $tourLocation = $this->resolveTourLocation($item->TourLocations ?? $item->tourLocations ?? collect());

        return $this->fromTourLocation($tourLocation, $name, 'ship');
    }

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    public function forServiceLocation(ServiceLocation $item, string $name): array
    {
        $tourLocation = $this->resolveTourLocation($item->tourLocations ?? collect());

        return $this->fromTourLocation($tourLocation, $name, 'service');
    }

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    public function forHotelLocation(HotelLocation $item, string $name): array
    {
        $tourLocation = $this->resolveTourLocation($item->tourLocations ?? collect());

        return $this->fromTourLocation($tourLocation, $name, 'hotel');
    }

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    public function forCarrentalLocation(CarrentalLocation $item, string $name): array
    {
        $tourLocation = $this->resolveTourLocation($item->tourLocations ?? collect());

        return $this->fromTourLocation($tourLocation, $name, 'carrental');
    }

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    public function forGuide(Guide $item, string $name): array
    {
        $tourLocation = $this->resolveTourLocation($item->tourLocations ?? collect());

        return $this->fromTourLocation($tourLocation, $name, 'guide');
    }

    /**
     * @param  iterable<mixed>  $relations
     */
    private function resolveTourLocation(iterable $relations): ?TourLocation
    {
        foreach ($relations as $relation) {
            $location = $relation->infoTourLocation ?? null;
            if ($location instanceof TourLocation) {
                return $location;
            }
        }

        return null;
    }

    /**
     * @return array{head: array<string, string>, items: list<array<string, string>>}
     */
    private function fromTourLocation(?TourLocation $location, string $name, ?string $excludeType): array
    {
        if (!$location) {
            return [
                'head' => [
                    'eyebrow' => t('tour_related_services_kicker'),
                    'title' => t('tour_related_services_title'),
                    'desc' => strip_tags((string) t('tour_related_services_desc', ['name' => $name])),
                ],
                'items' => [],
            ];
        }

        $data = $this->tourBuilder->forLocation($location, $name);

        if ($excludeType !== null && $excludeType !== '') {
            $data['items'] = array_values(array_filter(
                $data['items'],
                fn (array $card): bool => ($card['type'] ?? '') !== $excludeType,
            ));
        }

        return $data;
    }
}
