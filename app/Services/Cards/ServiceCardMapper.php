<?php

declare(strict_types=1);

namespace App\Services\Cards;

use App\Models\Service;
use App\Services\Island\IslandContextService;

class ServiceCardMapper
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function fromService(Service $service): ?array
    {
        $seo = $service->seo;
        if (!$seo) {
            return null;
        }

        $title = $service->name ?: ($seo->title ?? '');
        $duration = trim((string) (($service->time_start ?? '') . (!empty($service->time_end) ? ' – ' . $service->time_end : '')));

        $rating = 0.0;
        $ratingCount = 0;
        if (!empty($service->comments) && $service->comments->isNotEmpty()) {
            $total = 0;
            foreach ($service->comments as $comment) {
                $total += (float) ($comment->rating ?? 0);
                ++$ratingCount;
            }
            if ($ratingCount > 0) {
                $rating = round($total / $ratingCount, 1);
            }
        }

        $saleoff = (!empty($service->price_del) && !empty($service->price_show) && $service->price_del > $service->price_show)
            ? \App\Helpers\Number::calculatorSaleoff($service->price_show, $service->price_del)
            : 0;

        $filterTags = ['tat-ca-ve'];
        if (!empty($saleoff)) {
            $filterTags[] = 've-giam-gia';
        }
        if ($ratingCount > 0 && $rating >= 4.5) {
            $filterTags[] = 've-danh-gia-cao';
        }

        $optionCount = !empty($service->options) ? $service->options->count() : 0;
        $facts = [
            ['icon' => 'tag', 'text_key' => 'service_e_ticket'],
        ];
        if ($optionCount > 0) {
            $facts[] = ['icon' => 'tag', 'text_key' => 'service_packages_count', 'text_params' => ['count' => $optionCount]];
        }

        return [
            'image' => $this->context->coverImage($seo, 'small'),
            'alt' => $title,
            'category' => t('kicker_entertainment'),
            'duration' => $duration !== '' ? $duration : null,
            'title' => $title,
            'rating' => $ratingCount > 0 ? $rating : null,
            'price' => $this->context->formatPrice(!empty($service->price_show) ? (float) $service->price_show : null),
            'facts' => $facts,
            'ctaHref' => $this->context->pageUrl($seo),
            'filterTicket' => implode(' ', $filterTags),
            'saleBadge' => !empty($saleoff) ? t('discount_percent', ['percent' => $saleoff]) : null,
        ];
    }
}
