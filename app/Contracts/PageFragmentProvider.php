<?php

namespace App\Contracts;

/**
 * Provider HTML fragment theo currency (AJAX), shell trang cache độc lập currency.
 */
interface PageFragmentProvider
{
    public function pageType(): string;

    /** @return list<string> */
    public function sections(): array;

    public function loadBySeoId(int $seoId): ?object;

    public function render(string $section, object $item): ?string;

    public function fragmentUrl(int $seoId, string $section, ?string $locale = null): string;
}
