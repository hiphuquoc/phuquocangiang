<?php

declare(strict_types=1);

namespace App\Services\HomeNewsletter;

use App\Models\HomeNewsletterConfig;

class HomeNewsletterService
{
    /**
     * @return array<string, mixed>
     */
    public function forFrontend(string $locale = 'vi'): array
    {
        $name = island_name();
        $year = (string) date('Y');

        $defaults = $this->defaults($name, $year);

        $config = HomeNewsletterConfig::query()
            ->where('locale', $locale)
            ->first();

        if (!$config) {
            return array_merge($defaults, ['active' => true]);
        }

        if (!$config->is_active) {
            return array_merge($defaults, [
                'configured' => true,
                'active' => false,
            ]);
        }

        return [
            'configured' => true,
            'active' => true,
            'stamp_text' => $config->stamp_text ?: $defaults['stamp_text'],
            'stamp_year' => $config->stamp_year ?: $defaults['stamp_year'],
            'kicker' => $config->kicker ?: $defaults['kicker'],
            'title' => $config->title ?: $defaults['title'],
            'lead' => $this->replaceIslandName($config->lead ?: $defaults['lead'], $name),
            'field_label' => $config->field_label ?: $defaults['field_label'],
            'email_placeholder' => $config->email_placeholder ?: $defaults['email_placeholder'],
            'submit_text' => $config->submit_text ?: $defaults['submit_text'],
            'note' => $config->note ?: $defaults['note'],
            'sign_text' => $config->sign_text ?: $defaults['sign_text'],
        ];
    }

    public function cacheStamp(string $locale = 'vi'): string
    {
        $config = HomeNewsletterConfig::query()->where('locale', $locale)->first();

        if (!$config) {
            return '0';
        }

        return $config->id . '|' . (string) ($config->updated_at ?? '0');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(string $islandName, string $year): array
    {
        return [
            'configured' => false,
            'active' => false,
            'stamp_text' => 'SD',
            'stamp_year' => $year,
            'kicker' => 'Thư từ Superdong',
            'title' => 'Gửi bạn ưu đãi vé tàu & combo mới nhất',
            'lead' => 'Đăng ký một lần — nhận deal thật, không spam. Cập nhật lịch tàu, mùa biển đẹp và gợi ý lịch trình ' . $islandName . ' mỗi tháng.',
            'field_label' => 'Kính gửi',
            'email_placeholder' => 'email@ban.com',
            'submit_text' => 'Gửi thư đăng ký',
            'note' => 'Bạn có thể hủy đăng ký bất cứ lúc nào. Chúng tôi tôn trọng quyền riêng tư của bạn.',
            'sign_text' => 'Trân trọng!',
        ];
    }

    private function replaceIslandName(string $text, string $name): string
    {
        return str_replace([':name', '{name}'], $name, $text);
    }
}
