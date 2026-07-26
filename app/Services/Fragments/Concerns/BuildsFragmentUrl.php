<?php

namespace App\Services\Fragments\Concerns;

trait BuildsFragmentUrl
{
    protected function buildFragmentUrl(string $pageType, int $seoId, string $section, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return '/fragments/' . $pageType . '/' . $seoId
            . '?section=' . rawurlencode($section)
            . '&locale=' . rawurlencode($locale);
    }
}
