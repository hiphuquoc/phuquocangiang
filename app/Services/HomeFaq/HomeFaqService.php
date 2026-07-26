<?php

declare(strict_types=1);

namespace App\Services\HomeFaq;

use App\Models\HomeFaqConfig;
use App\Models\HomeFaqItem;

class HomeFaqService
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
            'kicker' => 'Hỏi đáp',
            'title' => 'Câu hỏi thường gặp',
            'description' => 'Giải đáp nhanh trước khi bạn lên đường khám phá ' . $name . ' — vé tàu, lưu trú, tour và thanh toán.',
            'help_title' => 'Cần tư vấn thêm?',
            'help_body' => 'Gọi hotline <a href="tel:19001234">1900 1234</a> — hỗ trợ 7:00–22:00 hàng ngày.',
            'items' => [],
        ];

        $config = HomeFaqConfig::query()
            ->where('locale', $locale)
            ->with(['items' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->first();

        if (!$config) {
            return array_merge($defaults, [
                'active' => true,
                'open_index' => 0,
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
            ->map(fn (HomeFaqItem $item) => $this->mapItem($item))
            ->values()
            ->all();

        if ($items === []) {
            return array_merge($defaults, [
                'configured' => true,
                'active' => true,
                'open_index' => 0,
                'items' => $this->defaultItems($name),
            ]);
        }

        $openIndex = $this->resolveOpenIndex($config->items);

        return [
            'configured' => true,
            'active' => true,
            'kicker' => $config->kicker ?: $defaults['kicker'],
            'title' => $this->replaceIslandName($config->title ?: $defaults['title'], $name),
            'description' => $this->replaceIslandName($config->description ?: $defaults['description'], $name),
            'help_title' => $config->help_title ?: $defaults['help_title'],
            'help_body' => $config->help_body ?: $defaults['help_body'],
            'open_index' => $openIndex,
            'items' => $items,
        ];
    }

    public function cacheStamp(string $locale = 'vi'): string
    {
        $config = HomeFaqConfig::query()->where('locale', $locale)->first();

        if (!$config) {
            return '0';
        }

        $itemStamp = (string) (HomeFaqItem::query()
            ->where('faq_config_id', $config->id)
            ->where('is_active', true)
            ->max('updated_at') ?? '0');

        return $config->id . '|' . (string) ($config->updated_at ?? '0') . '|' . $itemStamp;
    }

    /**
     * @return array{q: string, a: string}
     */
    private function mapItem(HomeFaqItem $item): array
    {
        return [
            'q' => $item->question,
            'a' => $item->answer_html,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, HomeFaqItem>  $items
     */
    private function resolveOpenIndex($items): int
    {
        foreach ($items as $index => $item) {
            if ($item->is_open_default) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @return array<int, array{q: string, a: string}>
     */
    private function defaultItems(string $islandName): array
    {
        return [
            [
                'q' => 'Cần đặt vé tàu trước bao lâu?',
                'a' => '<p>Nên đặt trước 3–7 ngày, đặc biệt dịp lễ Tết và mùa cao điểm đi lễ (tháng 10 đến tháng 3 năm sau). Superdong hỗ trợ đặt online 24/7 với vé điện tử gửi qua email.</p>',
            ],
            [
                'q' => 'Chính sách đổi / hủy vé tàu ' . $islandName . '?',
                'a' => '<p>Đổi chuyến: phí 20%, báo trước ít nhất 24 giờ. Hủy vé: phí 20%. Đổi thông tin hành khách: phí 10%. Liên hệ <a href="tel:19001234">hotline</a> để được hỗ trợ nhanh nhất.</p>',
            ],
            [
                'q' => 'Nên ở ' . $islandName . ' mấy ngày?',
                'a' => '<p>3 ngày 2 đêm là lý tưởng: ngày 1 di chuyển + tham quan, ngày 2 tour biển hoặc trekking, ngày 3 mua đặc sản + về. Combo 3N2Đ của Superdong được thiết kế sẵn lịch trình tối ưu.</p>',
            ],
            [
                'q' => 'Thanh toán qua những hình thức nào?',
                'a' => '<p>Chuyển khoản ngân hàng, thẻ quốc tế (Visa/Mastercard), ví MoMo, ZaloPay. Thanh toán an toàn qua cổng SSL — nhận xác nhận ngay sau khi thanh toán thành công.</p>',
            ],
        ];
    }

    private function replaceIslandName(string $text, string $name): string
    {
        return str_replace([':name', '{name}'], $name, $text);
    }
}
