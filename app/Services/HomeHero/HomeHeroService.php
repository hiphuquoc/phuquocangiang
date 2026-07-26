<?php

declare(strict_types=1);

namespace App\Services\HomeHero;

use App\Models\HomeHeroConfig;

class HomeHeroService
{
    public function __construct(
        private readonly HomeHeroRouteResolver $routeResolver,
    ) {}

    public function forFrontend(string $locale = 'vi'): array
    {
        $config = HomeHeroConfig::forLocale($locale);

        if (!$config) {
            return $this->defaultPayload();
        }

        $backgrounds = $config->backgrounds
            ->map(fn ($item) => [
                'src' => $item->mediaUrl(),
                'alt' => $item->alt_text ?: ($config->title ?: 'Hero Superdong'),
            ])
            ->values()
            ->all();

        if ($backgrounds === []) {
            $backgrounds = $this->defaultPayload()['backgrounds'];
        }

        return [
            'backgrounds' => $backgrounds,
            'title' => $config->title,
            'title_accent' => $config->title_accent,
            'tagline' => $config->tagline,
            'routes' => $this->routeResolver->resolveMany($config->routeSlots),
            'buttons' => [
                'primary' => [
                    'enabled' => (bool) $config->btn_primary_enabled,
                    'label' => $config->btn_primary_label,
                    'url' => $config->btn_primary_url ?: '#booking',
                ],
                'secondary' => [
                    'enabled' => (bool) $config->btn_secondary_enabled,
                    'label' => $config->btn_secondary_label,
                    'url' => $config->btn_secondary_url ?: 'tel:1900545487',
                ],
            ],
        ];
    }

    public function defaultPayload(): array
    {
        $island = island_name();

        return [
            'backgrounds' => [[
                'src' => 'https://www.agoda.com/wp-content/uploads/2024/03/Featured-image-An-Thoi-Harbour-In-Phu-Quoc-Island-Vietnam.jpg',
                'alt' => 'Cảng biển ' . $island,
            ]],
            'title' => 'Khám phá ' . $island,
            'title_accent' => 'đặt trọn hành trình',
            'tagline' => 'Vé tàu cao tốc Superdong, tour trải nghiệm và lưu trú — giá công khai, vé điện tử QR nhận trong 30 giây.',
            'routes' => [
                ['from' => '—', 'to' => $island, 'duration' => '~2h', 'price' => '0', 'href' => '#booking'],
            ],
            'buttons' => [
                'primary' => ['enabled' => true, 'label' => 'Đặt vé tàu', 'url' => '#booking'],
                'secondary' => ['enabled' => true, 'label' => '1900 545 487', 'url' => 'tel:1900545487'],
            ],
        ];
    }
}
