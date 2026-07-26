<?php

declare(strict_types=1);

namespace App\Services\Island;

use App\Models\Category;
use App\Models\TourLocation;

/**
 * Link menu / footer / quick access — trỏ đúng trang danh mục từ Tour Location cấu hình.
 */
class IslandNavigationService
{
    public function __construct(
        private readonly IslandContextService $context,
    ) {}

    /** @var array{links: array<string, string>, items: array<int, array<string, mixed>>}|null */
    private ?array $mainMenuCache = null;

    /** @var array<int, array{key: string, label: string, href: string, children: array<int, array<string, mixed>>}>|null */
    private ?array $blogCategoriesCache = null;

    /**
     * @return array<string, string>
     */
    public function links(): array
    {
        $menu = $this->mainMenu();
        $links = $menu['links'];

        return $links;
    }

    /**
     * Menu chính V2 — item, active, dropdown blog.
     *
     * @return array{links: array<string, string>, items: array<int, array<string, mixed>>}
     */
    public function mainMenu(?string $currentPath = null): array
    {
        if ($this->mainMenuCache !== null && $currentPath === null) {
            return $this->mainMenuCache;
        }

        $home = route('main.home');
        $location = $this->context->locationForNav();
        $blogCategories = $this->blogCategories($location);
        $blogHref = $blogCategories[0]['href'] ?? ($home . '#blog');

        if (!$location instanceof TourLocation) {
            $links = $this->homeAnchors($home);
            $links['blog'] = $blogHref;

            $payload = [
                'links' => $links,
                'items' => $this->buildItems($links, $blogCategories, $currentPath, null),
            ];

            if ($currentPath === null) {
                $this->mainMenuCache = $payload;
            }

            return $payload;
        }

        $links = [
            'home' => $home,
            'booking' => $home . '#booking',
            'ferry' => $this->section($location->shipLocations->first()?->infoShipLocation?->seo, 'ferry', $home),
            'tours' => $this->section($location->seo, 'tours', $home),
            'hotels' => $this->section($location->hotelLocations->first()?->infoHotelLocation?->seo, 'hotels', $home),
            'services' => $this->section($location->serviceLocations->first()?->infoServiceLocation?->seo, 'services', $home),
            'guide' => $this->guideLink($location, $home),
            'rental' => $this->section($location->carrentalLocations->first()?->infoCarrentalLocation?->seo, 'rental', $home),
            'blog' => $blogHref,
            'faq' => $home . '#faq',
        ];

        $payload = [
            'links' => $links,
            'items' => $this->buildItems($links, $blogCategories, $currentPath, $location),
        ];

        if ($currentPath === null) {
            $this->mainMenuCache = $payload;
        }

        return $payload;
    }

    /**
     * @param  array<string, string>  $links
     * @param  array<int, array<string, mixed>>  $blogCategories
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(array $links, array $blogCategories, ?string $currentPath, ?TourLocation $location): array
    {
        $page = request()->attributes->get('superdong_page', []);
        $path = $this->normalizePath($currentPath ?? request()->path());
        $pageSlug = $this->normalizePath((string) ($page['slug_full'] ?? ''));

        $items = [
            $this->navItem('ferry', 'Vé tàu', $links['ferry'] ?? '#ferry', $page, $path, $pageSlug),
            $this->navItem('tours', 'Tour', $links['tours'] ?? '#tours', $page, $path, $pageSlug),
            $this->navItem('hotels', 'Khách sạn', $links['hotels'] ?? '#hotels', $page, $path, $pageSlug),
            $this->navItem('services', 'Vé vui chơi', $links['services'] ?? '#services', $page, $path, $pageSlug),
            $this->navItem('guide', 'Cẩm nang', $links['guide'] ?? '#guide', $page, $path, $pageSlug),
            $this->navItem('rental', 'Thuê xe', $links['rental'] ?? '#rental', $page, $path, $pageSlug),
        ];

        $blogChildren = [];
        foreach ($blogCategories as $category) {
            $child = [
                'key' => $category['key'],
                'label' => $category['label'],
                'href' => $category['href'],
                'active' => $this->isBlogCategoryActive($category, $page, $pageSlug),
            ];
            if (!empty($category['children'])) {
                $child['children'] = array_map(function (array $sub) use ($page, $pageSlug) {
                    $sub['active'] = $this->isBlogCategoryActive($sub, $page, $pageSlug);

                    return $sub;
                }, $category['children']);
            }
            $blogChildren[] = $child;
        }

        $items[] = [
            'key' => 'blog',
            'label' => 'Blog',
            'href' => $links['blog'] ?? '#',
            'active' => $this->isKeyActive('blog', $page, $path, $pageSlug),
            'dropdown' => true,
            'children' => $blogChildren,
        ];

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function navItem(
        string $key,
        string $label,
        string $href,
        array $page,
        string $path,
        string $pageSlug,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'href' => $href,
            'active' => $this->isKeyActive($key, $page, $path, $pageSlug),
            'dropdown' => false,
            'children' => [],
        ];
    }

    private function isKeyActive(string $key, array $page, string $path, string $pageSlug): bool
    {
        $type = (string) ($page['type'] ?? '');

        return match ($key) {
            'home' => $path === '' || $type === 'home',
            'ferry' => in_array($type, ['ship_location', 'ship_info', 'ship_partner'], true),
            'tours' => in_array($type, ['tour_location', 'tour_info'], true),
            'hotels' => in_array($type, ['hotel_location', 'hotel_info'], true),
            'services' => in_array($type, ['service_location', 'service_info'], true),
            'guide' => $type === 'guide_info',
            'rental' => $type === 'carrental_location',
            'blog' => in_array($type, ['blog_info', 'category_info'], true),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $category
     */
    private function isBlogCategoryActive(array $category, array $page, string $pageSlug): bool
    {
        if (!in_array((string) ($page['type'] ?? ''), ['blog_info', 'category_info'], true)) {
            return false;
        }

        $hrefPath = $this->normalizePath(parse_url((string) ($category['href'] ?? ''), PHP_URL_PATH) ?: '');
        if ($hrefPath === '' || $pageSlug === '') {
            return false;
        }

        return $pageSlug === $hrefPath || str_starts_with($pageSlug, $hrefPath . '/');
    }

    /**
     * Danh mục blog cho menu — ưu tiên destinations/specials của Tour Location.
     *
     * @return array<int, array{key: string, label: string, href: string, children: array<int, array<string, mixed>>}>
     */
    public function blogCategories(?TourLocation $location = null): array
    {
        $useCache = func_num_args() === 0;
        if ($useCache && $this->blogCategoriesCache !== null) {
            return $this->blogCategoriesCache;
        }

        $location ??= $this->context->locationForNav();
        $categories = collect();

        if ($location instanceof TourLocation) {
            foreach ($location->destinations ?? [] as $relation) {
                if (!empty($relation->infoCategory)) {
                    $categories->push($relation->infoCategory);
                }
            }
            foreach ($location->specials ?? [] as $relation) {
                if (!empty($relation->infoCategory)) {
                    $categories->push($relation->infoCategory);
                }
            }
        }

        if ($categories->isEmpty()) {
            $categories = Category::query()
                ->whereHas('seo', fn ($q) => $q->where('level', 1))
                ->with('seo')
                ->orderBy('id')
                ->get();
        } else {
            $ids = $categories->unique('id')->pluck('id')->filter()->values()->all();
            $categories = Category::query()
                ->whereIn('id', $ids)
                ->with('seo')
                ->get()
                ->sortBy(fn (Category $cat) => array_search($cat->id, $ids, true))
                ->values();
        }

        if ($categories->isEmpty()) {
            return $useCache ? ($this->blogCategoriesCache = []) : [];
        }

        $parentSeoIds = $categories
            ->map(fn (Category $cat) => $cat->seo?->id)
            ->filter()
            ->values()
            ->all();

        $childGroups = collect();
        if ($parentSeoIds !== []) {
            $childGroups = Category::query()
                ->whereHas('seo', fn ($q) => $q->whereIn('parent', $parentSeoIds))
                ->with('seo')
                ->orderBy('id')
                ->get()
                ->groupBy(fn (Category $child) => (int) ($child->seo->parent ?? 0));
        }

        $result = $categories->map(function (Category $cat) use ($childGroups) {
            $seo = $cat->seo;
            $label = (string) ($cat->name ?: ($seo->title ?? ''));
            $parentSeoId = (int) ($seo->id ?? 0);

            $children = ($childGroups->get($parentSeoId) ?? collect())
                ->map(fn (Category $child) => [
                    'key' => 'blog-cat-' . $child->id,
                    'label' => (string) ($child->name ?: ($child->seo->title ?? '')),
                    'href' => seo_url($child->seo),
                ])
                ->values()
                ->all();

            return [
                'key' => 'blog-cat-' . $cat->id,
                'label' => $label,
                'href' => seo_url($seo),
                'children' => $children,
            ];
        })->values()->all();

        if ($useCache) {
            $this->blogCategoriesCache = $result;
        }

        return $result;
    }

    /**
     * Sidebar / footer — flat list level-1 blog categories.
     *
     * @return array<int, array{label: string, href: string, active: bool}>
     */
    public function blogCategoryLinks(?string $currentPath = null): array
    {
        $page = request()->attributes->get('superdong_page', []);
        $pageSlug = $this->normalizePath((string) ($page['slug_full'] ?? ''));
        $links = [];

        foreach ($this->blogCategories() as $category) {
            $links[] = [
                'label' => $category['label'],
                'href' => $category['href'],
                'active' => $this->isBlogCategoryActive($category, $page, $pageSlug),
            ];
            foreach ($category['children'] ?? [] as $child) {
                $links[] = [
                    'label' => '— ' . $child['label'],
                    'href' => $child['href'],
                    'active' => $this->isBlogCategoryActive($child, $page, $pageSlug),
                ];
            }
        }

        return $links;
    }

    private function normalizePath(string $path): string
    {
        $path = trim(strtolower(rawurldecode($path)), '/');
        $default = config('language.default_code', 'vi');
        $locale = app()->getLocale();

        if ($locale !== $default && str_starts_with($path, $locale . '/')) {
            $path = substr($path, strlen($locale) + 1);
        } elseif ($locale !== $default && $path === $locale) {
            $path = '';
        }

        return $path;
    }

    /**
     * @return array<string, string>
     */
    private function homeAnchors(string $home): array
    {
        return [
            'home' => $home,
            'booking' => $home . '#booking',
            'ferry' => $home . '#ferry',
            'tours' => $home . '#tours',
            'hotels' => $home . '#hotels',
            'services' => $home . '#services',
            'guide' => $home . '#guide',
            'rental' => $home . '#rental',
            'blog' => $home . '#blog',
            'faq' => $home . '#faq',
        ];
    }

    private function section(?object $seo, string $sectionId, string $home): string
    {
        return $this->context->categoryUrl($seo, $sectionId, $home . '#' . ltrim($sectionId, '#'));
    }

    private function guideLink(TourLocation $location, string $home): string
    {
        $guideSeo = $location->guides->first()?->infoGuide?->seo;

        if ($guideSeo) {
            return $this->context->pageUrl($guideSeo, $home . '#guide');
        }

        return $home . '#guide';
    }
}
