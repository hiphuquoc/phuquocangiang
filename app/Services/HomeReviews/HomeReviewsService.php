<?php

declare(strict_types=1);

namespace App\Services\HomeReviews;

use App\Models\HomeReviewItem;
use App\Models\HomeReviewsConfig;

class HomeReviewsService
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
            'kicker' => 'Khách hàng nói gì',
            'title' => 'Hành trình được tin chọn',
            'description' => 'Hàng nghìn du khách đã trải nghiệm ' . $name . ' cùng Superdong — từ vé tàu cao tốc đến tour và nghỉ dưỡng trọn gói.',
            'score_value' => 4.9,
            'score_dashoffset' => $this->scoreDashoffset(4.9),
            'score_stats' => [
                ['value' => '12K+', 'label' => 'đánh giá'],
                ['value' => '98%', 'label' => 'quay lại'],
                ['value' => '5★', 'label' => 'trung bình'],
            ],
            'partners_label' => 'Đối tác tin cậy',
            'partners' => ['Superdong', 'Phú Quốc Express', 'Poulo Condor', 'Vietcombank', 'MoMo'],
            'items' => [],
        ];

        $config = HomeReviewsConfig::query()
            ->where('locale', $locale)
            ->with(['items' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->first();

        if (!$config) {
            return array_merge($defaults, [
                'active' => true,
                'items' => $this->defaultItems($name),
            ]);
        }

        if (!$config->is_active) {
            return array_merge($defaults, [
                'configured' => true,
                'active' => false,
                'items' => [],
            ]);
        }

        $items = $config->items
            ->map(fn (HomeReviewItem $item) => $this->mapItem($item))
            ->values()
            ->all();

        if ($items === []) {
            return array_merge($defaults, [
                'configured' => true,
                'active' => true,
                'items' => $this->defaultItems($name),
            ]);
        }

        $scoreValue = (float) ($config->score_value ?: $defaults['score_value']);
        $scoreStats = $this->normalizeScoreStats($config->score_stats) ?: $defaults['score_stats'];
        $partners = $this->normalizePartners($config->partners) ?: $defaults['partners'];

        return [
            'configured' => true,
            'active' => true,
            'kicker' => $config->kicker ?: $defaults['kicker'],
            'title' => $this->replaceIslandName($config->title ?: $defaults['title'], $name),
            'description' => $this->replaceIslandName($config->description ?: $defaults['description'], $name),
            'score_value' => $scoreValue,
            'score_dashoffset' => $this->scoreDashoffset($scoreValue),
            'score_stats' => $scoreStats,
            'partners_label' => $config->partners_label ?: $defaults['partners_label'],
            'partners' => $partners,
            'items' => $items,
        ];
    }

    public function cacheStamp(string $locale = 'vi'): string
    {
        $config = HomeReviewsConfig::query()->where('locale', $locale)->first();

        if (!$config) {
            return '0';
        }

        $itemStamp = (string) (HomeReviewItem::query()
            ->where('reviews_config_id', $config->id)
            ->where('is_active', true)
            ->max('updated_at') ?? '0');

        return $config->id . '|' . (string) ($config->updated_at ?? '0') . '|' . $itemStamp;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapItem(HomeReviewItem $item): array
    {
        $rating = max(1, min(5, (int) ($item->rating ?: 5)));
        $avatar = $item->avatarUrl();

        return [
            'text' => $item->quote_text,
            'name' => $item->customer_name,
            'meta' => trim((string) ($item->customer_meta ?? '')),
            'tag' => trim((string) ($item->tag ?? '')),
            'rating' => $rating,
            'avatar' => $avatar !== '' ? $avatar : 'https://i.pravatar.cc/120?u=sd-review-' . $item->id,
        ];
    }

    /**
     * @param  array<int, array<string, string>>|null  $stats
     * @return array<int, array{value: string, label: string}>
     */
    private function normalizeScoreStats(?array $stats): array
    {
        if (!$stats) {
            return [];
        }

        $normalized = [];
        foreach ($stats as $stat) {
            if (!is_array($stat)) {
                continue;
            }
            $value = trim((string) ($stat['value'] ?? ''));
            $label = trim((string) ($stat['label'] ?? ''));
            if ($value === '' && $label === '') {
                continue;
            }
            $normalized[] = ['value' => $value, 'label' => $label];
        }

        return $normalized;
    }

    /**
     * @param  array<int, string>|null  $partners
     * @return array<int, string>
     */
    private function normalizePartners(?array $partners): array
    {
        if (!$partners) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($name) => trim((string) $name),
            $partners,
        )));
    }

    private function scoreDashoffset(float $score): float
    {
        $circumference = 2 * M_PI * 52;
        $normalized = max(0.0, min(5.0, $score)) / 5.0;

        return round($circumference * (1 - $normalized), 1);
    }

    private function replaceIslandName(string $text, string $name): string
    {
        return str_replace([':name', '{name}'], $name, $text);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultItems(string $islandName): array
    {
        return [
            [
                'text' => 'Đặt combo tàu + khách sạn qua Superdong rất tiện, giá rẻ hơn tự book lẻ. Nhân viên tư vấn nhiệt tình, gửi vé điện tử ngay sau khi thanh toán.',
                'name' => 'Nguyễn Minh Anh',
                'meta' => 'Hà Nội',
                'tag' => 'Combo 3N2Đ',
                'rating' => 5,
                'avatar' => 'https://i.pravatar.cc/120?u=sd-review-1',
            ],
            [
                'text' => 'Tàu Superdong đúng giờ, ghế thoáng. Tour lặn san hô tuyệt vời — thấy rùa biển luôn! Sẽ quay lại ' . $islandName . ' chắc chắn.',
                'name' => 'Trần Hoàng Long',
                'meta' => 'TP.HCM',
                'tag' => 'Tour biển',
                'rating' => 5,
                'avatar' => 'https://i.pravatar.cc/120?u=sd-review-2',
            ],
            [
                'text' => 'Website dễ dùng, thanh toán nhanh. Hotline hỗ trợ đổi ngày vé linh hoạt khi thời tiết xấu. Rất recommend!',
                'name' => 'Phạm Thu Hà',
                'meta' => 'Cần Thơ',
                'tag' => 'Vé tàu',
                'rating' => 5,
                'avatar' => 'https://i.pravatar.cc/120?u=sd-review-3',
            ],
        ];
    }
}
