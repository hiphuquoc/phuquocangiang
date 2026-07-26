<?php

declare(strict_types=1);

namespace App\Services\HomeIslandGallery;

use App\Models\HomeIslandGalleryConfig;
use App\Models\HomeIslandGalleryPhoto;
use Illuminate\Support\Str;

class HomeIslandGalleryService
{
    /**
     * @return array<string, mixed>
     */
    public function forFrontend(string $locale = 'vi'): array
    {
        $name = island_name();

        $defaults = [
            'configured' => false,
            'active' => false,
            'eyebrow' => 'Trải nghiệm đảo',
            'title' => $name . ' qua từng khoảnh khắc đẹp',
            'lead' => 'Bãi biển hoang sơ, thiên nhiên trong lành và những trải nghiệm khó quên trên đảo.',
            'meta_caption' => 'Thư viện ảnh ' . $name,
            'items' => [],
        ];

        $config = HomeIslandGalleryConfig::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->with(['photos' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->first();

        if (!$config || !$config->is_active) {
            return $defaults;
        }

        $items = $config->photos
            ->map(fn (HomeIslandGalleryPhoto $photo) => $this->mapPhoto($photo))
            ->values()
            ->all();

        return [
            'configured' => true,
            'active' => true,
            'eyebrow' => $config->eyebrow ?: $defaults['eyebrow'],
            'title' => $this->replaceIslandName($config->title ?: $defaults['title'], $name),
            'lead' => $config->lead ?: $defaults['lead'],
            'meta_caption' => $this->replaceIslandName($config->meta_caption ?: $defaults['meta_caption'], $name),
            'items' => $items,
        ];
    }

    public function cacheStamp(string $locale = 'vi'): string
    {
        $config = HomeIslandGalleryConfig::query()->where('locale', $locale)->first();

        if (!$config) {
            return '0';
        }

        $photoStamp = (string) (\App\Models\HomeIslandGalleryPhoto::query()
            ->where('gallery_config_id', $config->id)
            ->where('is_active', true)
            ->max('updated_at') ?? '0');

        return $config->id . '|' . (string) ($config->updated_at ?? '0') . '|' . $photoStamp;
    }

    /**
     * @return array<string, string>
     */
    private function mapPhoto(HomeIslandGalleryPhoto $photo): array
    {
        $alt = trim($photo->alt_text);
        $title = trim((string) ($photo->title ?: ''));
        if ($title === '') {
            $title = Str::before($alt, '—') ?: Str::limit($alt, 48, '');
        }

        return [
            'image' => $photo->displayUrl(),
            'lightbox' => $photo->lightboxUrl(),
            'alt' => $alt,
            'title' => $title,
            'tag' => trim((string) ($photo->tag ?? '')),
            'pos' => $photo->object_position ?: 'center center',
        ];
    }

    private function replaceIslandName(string $text, string $name): string
    {
        return str_replace([':name', '{name}'], $name, $text);
    }
}

