<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Redirect;

use App\Helpers\Url;
use App\Services\HtmlCacheService;
use App\Services\Fragments\AirLocationFragmentService;
use App\Services\Fragments\ComboLocationFragmentService;
use App\Services\Fragments\ServiceLocationFragmentService;
use App\Services\Fragments\ShipLocationFragmentService;
use App\Services\Fragments\TourContinentFragmentService;
use App\Services\Fragments\TourCountryFragmentService;
use App\Services\TourLocationFragmentService;

use App\Models\TourLocation;
use App\Models\Tour;
use App\Models\TourInfoForeign;
use App\Models\TourContinent;
use App\Models\TourCountry;
use App\Models\ShipLocation;
use App\Models\ShipPartner;
use App\Models\AirPartner;
use App\Models\Ship;
use App\Models\Service;
use App\Models\ServiceLocation;
use App\Models\Air;
use App\Models\AirLocation;
use App\Models\CarrentalLocation;
use App\Models\Guide;
use App\Models\Category;
use App\Models\Blog;
use App\Models\Page;
use App\Models\ComboLocation;
use App\Models\Combo;
use App\Models\HotelLocation;
use App\Models\Hotel;

/**
 * Catch-all routing controller.
 *
 * Trách nhiệm:
 *  - Chuẩn hoá URL từ request -> tra cứu trong bảng `seo` qua slug_full.
 *  - Nếu URL không khớp slug_full chuẩn -> 301 redirect.
 *  - Phân phối render đến handler tương ứng theo `seo.type`.
 *  - Cache HTML toàn trang qua HtmlCacheService (gzip + minify optional).
 *
 * Kiến trúc handler: mỗi `seo.type` có 1 private method `render<Type>()` trả
 * về string HTML. Đây là bước trung gian trước khi chuyển hẳn sang
 * pattern PageRenderer riêng biệt ở Phase 1 đa ngôn ngữ.
 */
class RoutingController extends Controller {

    public function routing(Request $request, HtmlCacheService $cache) {
        // 1. Chuẩn hoá path: tách locale (nếu có) ra khỏi segments
        [$localeFromUrl, $segments] = Url::cleanRequestPathWithLocale(rawurldecode($request->path()));

        // 2. Locale resolution
        //    Ưu tiên: locale segment URL -> locale từ middleware DetectLocale -> default
        $locale = $localeFromUrl
            ?: $request->attributes->get('locale')
            ?: app()->getLocale();
        $defaultCode = config('language.default_code', 'vi');
        $lang = \App\Models\Language::byCode($locale);
        if (!$lang || !$lang->is_active) {
            $lang   = \App\Models\Language::default();
            $locale = $lang ? $lang->code : $defaultCode;
        }
        app()->setLocale($locale);

        if (empty($segments)) {
            return ErrorController::error404();
        }
        $urlRequest = implode('/', $segments);

        // 3. Tra slug_full chuẩn xác — dùng seo_translations theo locale hiện tại
        $itemSeo = Url::checkUrlExists($urlRequest, $locale);
        if (empty($itemSeo) || empty($itemSeo->type)) {
            return ErrorController::error404();
        }

        request()->attributes->set('superdong_page', [
            'type' => (string) $itemSeo->type,
            'slug_full' => ltrim((string) ($itemSeo->slug_full ?? $urlRequest), '/'),
            'seo_id' => (int) $itemSeo->id,
        ]);

        // 4. Build cache key
        //    tour_location: một file cache / locale+slug; giá tải AJAX theo currency.
        //    Các type khác: vẫn suffix currency cho đến khi migrate fragment.
        $cacheParams = [];
        if ($p = $request->query('page'))   $cacheParams['page']   = $p;
        if ($s = $request->query('search')) $cacheParams['search'] = $s;
        $currencyIndependentTypes = [
            'tour_location',
            'tour_country',
            'tour_continent',
            'combo_location',
            'air_location',
            'ship_location',
            'service_location',
        ];
        $slugFull = HtmlCacheService::resolveSlugFullForCache($itemSeo);
        if ($slugFull === '') {
            $slugFull = $urlRequest;
        }
        $cacheKey = HtmlCacheService::buildKeyFromSlugFull(
            $slugFull,
            $localeFromUrl ?: $locale,
            $cacheParams
        );
        if (!in_array($itemSeo->type, $currencyIndependentTypes, true)) {
            $currency = strtolower((string) ($request->attributes->get('currency') ?: current_currency()));
            $cacheKey .= '-' . $currency;
        }

        // 5. Render qua cache — không bao giờ ghi file home / en-home (chỉ HomeController)
        $html = $cache->getOrRender($cacheKey, function () use ($itemSeo, $locale) {
            return $this->dispatch($itemSeo, $locale);
        }, false);

        if (empty($html)) {
            return ErrorController::error404();
        }
        echo $html;
    }

    /**
     * Dispatch render theo seo.type.
     * Trả về string HTML hoặc null nếu không khớp type nào.
     *
     * @param mixed  $itemSeo
     * @param string $locale   locale hiện tại (đã resolve trong routing()).
     */
    protected function dispatch($itemSeo, string $locale = 'vi'): ?string {
        // Đảm bảo locale được set khi render qua cache callback
        app()->setLocale($locale);

        $type = $itemSeo->type;

        if (!seo_type_enabled($type)) {
            return null;
        }

        return match ($type) {
            'tour_location'      => $this->renderTourLocation($itemSeo, $locale),
            'tour_info'          => $this->renderTour($itemSeo, $locale),
            'tour_continent'     => $this->renderTourContinent($itemSeo, $locale),
            'tour_country'       => $this->renderTourCountry($itemSeo, $locale),
            'tour_info_foreign'  => $this->renderTourInfoForeign($itemSeo, $locale),
            'ship_location'      => $this->renderShipLocation($itemSeo, $locale),
            'ship_partner'       => $this->renderShipPartner($itemSeo, $locale),
            'ship_info'          => $this->renderShip($itemSeo, $locale),
            'guide_info'         => $this->renderGuide($itemSeo, $locale),
            'service_info'       => $this->renderService($itemSeo, $locale),
            'service_location'   => $this->renderServiceLocation($itemSeo, $locale),
            'carrental_location' => $this->renderCarrentalLocation($itemSeo, $locale),
            'air_location'       => $this->renderAirLocation($itemSeo, $locale),
            'air_info'           => $this->renderAir($itemSeo, $locale),
            'combo_location'     => $this->renderComboLocation($itemSeo, $locale),
            'combo_info'         => $this->renderCombo($itemSeo, $locale),
            'hotel_location'     => $this->renderHotelLocation($itemSeo, $locale),
            'hotel_info'         => $this->renderHotel($itemSeo, $locale),
            'category_info'      => $this->renderCategory($itemSeo, $locale),
            'blog_info'          => $this->renderBlog($itemSeo, $locale),
            'page_info'          => $this->renderPage($itemSeo, $locale),
            default              => null,
        };
    }

    // ================== HANDLERS ==================

    private function renderTourLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = TourLocation::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with('tours.infoTour', fn($q) => $q->where('status_show', 1))
            ->with('tours.infoTour.seo')
            ->with(['files' => fn($q) => $q->where('relation_table', 'tour_location')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'tour_location')])
            ->with('seo', 'guides.infoGuide.seo', 'carrentalLocations.infoCarrentalLocation.seo', 'destinations', 'specials')
            ->with([
                'comboLocations.infoComboLocation.seo',
                'airLocations.infoAirLocation.seo',
                'serviceLocations.infoServiceLocation.seo',
                'shipLocations.infoShipLocation.seo',
                'hotelLocations.infoHotelLocation.seo',
            ])
            ->first();
        if (!$item) return null;

        $fragments = app(TourLocationFragmentService::class);
        $schemaOffer = $fragments->schemaOfferPrices($item, $locale);
        $dataToursNoscript = $fragments->toursForList($item);
        $fragmentUrls = [
            'tours'   => $fragments->fragmentUrl($itemSeo->id, TourLocationFragmentService::SECTION_TOURS, $locale),
            'combo'   => $fragments->fragmentUrl($itemSeo->id, TourLocationFragmentService::SECTION_COMBO, $locale),
            'air'     => $fragments->fragmentUrl($itemSeo->id, TourLocationFragmentService::SECTION_AIR, $locale),
            'service' => $fragments->fragmentUrl($itemSeo->id, TourLocationFragmentService::SECTION_SERVICE, $locale),
            'ship'    => $fragments->fragmentUrl($itemSeo->id, TourLocationFragmentService::SECTION_SHIP, $locale),
        ];

        $arrayIdDestination = $item->destinations->map(fn($d) => $d->infoCategory->id ?? null)->filter()->values()->all();
        $destinationList = Blog::select('*')
            ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdDestination))
            ->with('seo')->get();

        $arrayIdSpecial = $item->specials->map(fn($s) => $s->infoCategory->id ?? null)->filter()->values()->all();
        $specialList = Blog::select('*')
            ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdSpecial))
            ->with('seo')->get();

        $content    = $this->renderContentBlade('tour_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_tour_location_v2')) {
            $page = app(\App\Services\TourLocation\TourLocationPageService::class)->forPage($item, $locale);

            return view('main.tourLocation-v2.index', compact(
                'item',
                'breadcrumb',
                'content',
                'schemaOffer',
                'fragmentUrls',
                'locale',
                'page',
            ))->render();
        }

        return view('main.tourLocation.index', compact(
            'item',
            'breadcrumb',
            'destinationList',
            'specialList',
            'content',
            'schemaOffer',
            'dataToursNoscript',
            'fragmentUrls',
            'locale'
        ))->render();
    }

    private function renderTour($itemSeo, string $locale = 'vi'): ?string {
        $item = Tour::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'tour_info')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'tour_info')])
            ->with('seo', 'locations', 'staffs.infoStaff', 'options.prices', 'departure', 'content', 'timetables')
            ->first();
        if (!$item) return null;

        $arrayIdTourLocation = $item->locations->map(fn($l) => $l->infoLocation->id ?? null)->filter()->values()->all();
        $related = Tour::select('*')
            ->where('id', '!=', $item->id)
            ->where('status_show', 1)
            ->whereHas('locations.infoLocation', fn($q) => $q->whereIn('id', $arrayIdTourLocation))
            ->with('seo')->get();

        $content    = $this->renderContentBlade('tour_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_tour_v2')) {
            $pageService = app(\App\Services\Tour\TourPageService::class);
            $page = $pageService->forPage($item, $locale, $related);
            $schemaOffer = $pageService->schemaOfferPrices($item, $locale);

            return view('main.tour-v2.index', compact(
                'item',
                'breadcrumb',
                'content',
                'related',
                'locale',
                'page',
                'schemaOffer',
            ))->render();
        }

        return view('main.tour.index', compact('item', 'breadcrumb', 'content', 'related'))->render();
    }

  private function renderShipLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = ShipLocation::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'ship_location')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'ship_location')])
            ->with('seo', 'district', 'ships.seo', 'ships.prices.times', 'ships.portDeparture', 'ships.departure.district', 'ships.departure.province', 'ships.portLocation', 'categories.infoCategory.seo')
            ->with([
                'TourLocations.infoTourLocation.seo',
                'TourLocations.infoTourLocation.shipLocations.infoShipLocation.seo',
                'TourLocations.infoTourLocation.airLocations.infoAirLocation.seo',
                'TourLocations.infoTourLocation.comboLocations.infoComboLocation.seo',
                'TourLocations.infoTourLocation.serviceLocations.infoServiceLocation.seo',
                'TourLocations.infoTourLocation.hotelLocations.infoHotelLocation.seo',
                'TourLocations.infoTourLocation.carrentalLocations.infoCarrentalLocation.seo',
                'TourLocations.infoTourLocation.guides.infoGuide.seo',
            ])
            ->first();
        if (!$item) return null;

        $schedule   = $this->renderContentBlade('ship_schedule', $item->seo->getRawOriginal('slug'), $locale);
        $content    = $this->renderContentBlade('ship_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_ship_location_v2')) {
            $pageService = app(\App\Services\ShipLocation\ShipLocationPageService::class);
            $page = $pageService->forPage($item, $locale);
            $schemaOffer = $pageService->schemaOfferPrices($item, $locale);

            return view('main.shipLocation-v2.index', compact(
                'item',
                'schedule',
                'content',
                'breadcrumb',
                'locale',
                'page',
                'schemaOffer',
            ))->render();
        }

        $i = 0;
        foreach ($item->categories as $category) {
            $arrayIdCategoryChild = Category::getArrayCategoryChildByIdSeo($category->infoCategory->seo->id);
            $arrayIdCategory = array_merge($arrayIdCategoryChild, [$category->infoCategory->id]);
            $blogs = Blog::select('*')
                ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdCategory))
                ->with('seo')->get();
            $item->categories[$i]->blogs = $blogs;
            ++$i;
        }

        $fragments = app(ShipLocationFragmentService::class);
        $fragmentUrls = [
            'ships' => $fragments->fragmentUrl($itemSeo->id, ShipLocationFragmentService::SECTION_LIST, $locale),
        ];

        return view('main.shipLocation.index', compact(
            'item',
            'schedule',
            'content',
            'breadcrumb',
            'fragmentUrls',
            'locale'
        ))->render();
    }

    private function renderShipPartner($itemSeo, string $locale = 'vi'): ?string {
        $item = ShipPartner::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['questions' => fn($q) => $q->where('relation_table', 'ship_partner')])
            ->with('seo')
            ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('ship_partner', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.shipPartner.index', compact('item', 'content', 'breadcrumb'))->render();
    }

    private function renderShip($itemSeo, string $locale = 'vi'): ?string {
        $item = Ship::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'ship_info')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'ship_info')])
            ->with('seo', 'partners.infoPartner.seo', 'portDeparture', 'portLocation', 'location', 'departure', 'prices.times')
            ->first();
        if (!$item) return null;

        $schedule   = $this->renderContentBlade('ship_schedule', $item->seo->getRawOriginal('slug'), $locale);
        $content    = $this->renderContentBlade('ship_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_ship_v2')) {
            $page = app(\App\Services\Ship\ShipPageService::class)->forPage($item, $locale);

            return view('main.ship-v2.index', compact(
                'item',
                'schedule',
                'content',
                'breadcrumb',
                'locale',
                'page',
            ))->render();
        }

        return view('main.ship.index', compact('item', 'schedule', 'content', 'breadcrumb'))->render();
    }

    private function renderGuide($itemSeo, string $locale = 'vi'): ?string {
        $item = Guide::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files' => fn($q) => $q->where('relation_table', 'guide_info')])
            ->with('seo', 'tourLocations.infoTourLocation.seo')
            ->with([
                'tourLocations.infoTourLocation.shipLocations.infoShipLocation.seo',
                'tourLocations.infoTourLocation.airLocations.infoAirLocation.seo',
                'tourLocations.infoTourLocation.comboLocations.infoComboLocation.seo',
                'tourLocations.infoTourLocation.serviceLocations.infoServiceLocation.seo',
                'tourLocations.infoTourLocation.hotelLocations.infoHotelLocation.seo',
                'tourLocations.infoTourLocation.carrentalLocations.infoCarrentalLocation.seo',
                'tourLocations.infoTourLocation.guides.infoGuide.seo',
            ])
            ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('guide_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_guide_v2')) {
            $page = app(\App\Services\Guide\GuidePageService::class)->forPage($item, $locale);

            return view('main.guide-v2.index', compact(
                'item',
                'content',
                'breadcrumb',
                'locale',
                'page',
            ))->render();
        }

        return view('main.guide.index', compact('item', 'content', 'breadcrumb'))->render();
    }

    private function renderService($itemSeo, string $locale = 'vi'): ?string {
        $item = Service::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'service_info')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'service_info')])
            ->with('seo', 'serviceLocation', 'options.prices')
            ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('service_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_service_v2')) {
            $pageService = app(\App\Services\Service\ServicePageService::class);
            $page = $pageService->forPage($item, $locale);
            $schemaOffer = $pageService->schemaOfferPrices($item, $locale);

            return view('main.service-v2.index', compact(
                'item',
                'content',
                'breadcrumb',
                'locale',
                'page',
                'schemaOffer',
            ))->render();
        }

        return view('main.service.index', compact('item', 'content', 'breadcrumb'))->render();
    }

    private function renderServiceLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = ServiceLocation::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'service_location')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'service_location')])
            ->with('seo', 'district', 'services.seo', 'services.comments', 'services.options', 'services.serviceLocation')
            ->with([
                'tourLocations.infoTourLocation.seo',
                'tourLocations.infoTourLocation.shipLocations.infoShipLocation.seo',
                'tourLocations.infoTourLocation.airLocations.infoAirLocation.seo',
                'tourLocations.infoTourLocation.comboLocations.infoComboLocation.seo',
                'tourLocations.infoTourLocation.serviceLocations.infoServiceLocation.seo',
                'tourLocations.infoTourLocation.hotelLocations.infoHotelLocation.seo',
                'tourLocations.infoTourLocation.carrentalLocations.infoCarrentalLocation.seo',
                'tourLocations.infoTourLocation.guides.infoGuide.seo',
            ])
            ->first();
        if (!$item) return null;

        $fragments = app(ServiceLocationFragmentService::class);
        $schemaOffer = $fragments->schemaOfferPrices($item, $locale);
        $dataServicesNoscript = $fragments->servicesForList($item);
        $fragmentUrls = [
            'services' => $fragments->fragmentUrl($itemSeo->id, ServiceLocationFragmentService::SECTION_LIST, $locale),
        ];

        $content    = $this->renderContentBlade('service_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_service_location_v2')) {
            $page = app(\App\Services\ServiceLocation\ServiceLocationPageService::class)->forPage($item, $locale);

            return view('main.serviceLocation-v2.index', compact(
                'item',
                'content',
                'breadcrumb',
                'schemaOffer',
                'locale',
                'page',
            ))->render();
        }

        return view('main.serviceLocation.index', compact(
            'item',
            'content',
            'breadcrumb',
            'fragmentUrls',
            'schemaOffer',
            'dataServicesNoscript',
            'locale'
        ))->render();
    }

    private function renderCarrentalLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = CarrentalLocation::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'carrental_location')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'carrental_location')])
            ->with('seo', 'tourLocations.infoTourLocation.seo')
            ->with([
                'tourLocations.infoTourLocation.shipLocations.infoShipLocation.seo',
                'tourLocations.infoTourLocation.airLocations.infoAirLocation.seo',
                'tourLocations.infoTourLocation.comboLocations.infoComboLocation.seo',
                'tourLocations.infoTourLocation.serviceLocations.infoServiceLocation.seo',
                'tourLocations.infoTourLocation.hotelLocations.infoHotelLocation.seo',
                'tourLocations.infoTourLocation.carrentalLocations.infoCarrentalLocation.seo',
                'tourLocations.infoTourLocation.guides.infoGuide.seo',
            ])
            ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('carrental_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_carrental_location_v2')) {
            $page = app(\App\Services\CarrentalLocation\CarrentalLocationPageService::class)->forPage($item, $locale);

            return view('main.carrentalLocation-v2.index', compact(
                'item',
                'breadcrumb',
                'content',
                'locale',
                'page',
            ))->render();
        }

        return view('main.carrentalLocation.index', compact('item', 'breadcrumb', 'content'))->render();
    }

    private function renderAirLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = AirLocation::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'air_location')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'air_location')])
            ->with('seo', 'airs.seo', 'tourLocations')
            ->first();
        if (!$item) return null;

        $fragments = app(AirLocationFragmentService::class);
        $fragmentUrls = [
            'airs' => $fragments->fragmentUrl($itemSeo->id, AirLocationFragmentService::SECTION_LIST, $locale),
        ];

        $content    = $this->renderContentBlade('air_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.airLocation.index', compact(
            'item',
            'breadcrumb',
            'content',
            'fragmentUrls',
            'locale'
        ))->render();
    }

    private function renderAir($itemSeo, string $locale = 'vi'): ?string {
        $item = Air::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'air_info')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'air_info')])
            ->with('seo', 'airLocation')
            ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('air_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.air.index', compact('item', 'breadcrumb', 'content'))->render();
    }

    private function renderTourContinent($itemSeo, string $locale = 'vi'): ?string {
        $item = TourContinent::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'tour_continent')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'tour_continent')])
            ->with('seo')
            ->with(['tourCountries.tours.infoTourForeign' => fn($q) => $q->where('status_show', 1)])
            ->with([
                'airLocations.infoAirLocation.seo',
                'serviceLocations.infoServiceLocation.seo',
            ])
            ->with('guides.infoGuide.seo')
            ->first();
        if (!$item) return null;

        $fragments = app(TourContinentFragmentService::class);
        $schemaOffer = $fragments->schemaOfferPrices($item, $locale);
        $dataToursNoscript = $fragments->toursForList($item);
        $fragmentUrls = [
            'tours'   => $fragments->fragmentUrl($itemSeo->id, TourContinentFragmentService::SECTION_TOURS, $locale),
            'air'     => $fragments->fragmentUrl($itemSeo->id, TourContinentFragmentService::SECTION_AIR, $locale),
            'service' => $fragments->fragmentUrl($itemSeo->id, TourContinentFragmentService::SECTION_SERVICE, $locale),
        ];

        $content    = $this->renderContentBlade('tour_continent', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.tourContinent.index', compact(
            'item',
            'breadcrumb',
            'content',
            'schemaOffer',
            'dataToursNoscript',
            'fragmentUrls',
            'locale'
        ))->render();
    }

    private function renderTourCountry($itemSeo, string $locale = 'vi'): ?string {
        $item = TourCountry::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'tour_country')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'tour_country')])
            ->with('seo')
            ->with(['tours.infoTourForeign' => fn($q) => $q->where('status_show', 1)])
            ->with([
                'airLocations.infoAirLocation.seo',
                'serviceLocations.infoServiceLocation.seo',
            ])
            ->with('guides.infoGuide.seo')
            ->first();
        if (!$item) return null;

        $fragments = app(TourCountryFragmentService::class);
        $schemaOffer = $fragments->schemaOfferPrices($item, $locale);
        $dataToursNoscript = $fragments->toursForList($item);
        $fragmentUrls = [
            'tours'   => $fragments->fragmentUrl($itemSeo->id, TourCountryFragmentService::SECTION_TOURS, $locale),
            'air'     => $fragments->fragmentUrl($itemSeo->id, TourCountryFragmentService::SECTION_AIR, $locale),
            'service' => $fragments->fragmentUrl($itemSeo->id, TourCountryFragmentService::SECTION_SERVICE, $locale),
        ];

        $content    = $this->renderContentBlade('tour_country', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.tourCountry.index', compact(
            'item',
            'breadcrumb',
            'content',
            'schemaOffer',
            'dataToursNoscript',
            'fragmentUrls',
            'locale'
        ))->render();
    }

    private function renderTourInfoForeign($itemSeo, string $locale = 'vi'): ?string {
        $item = TourInfoForeign::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'tour_info_foreign')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'tour_info_foreign')])
            ->with('seo', 'staffs.infoStaff', 'options.prices', 'departure', 'content', 'timetables')
            ->first();
        if (!$item) return null;

        $arrayIdTourCountry = $item->tourCountries->map(fn($c) => $c->infoCountry->id ?? null)->filter()->values()->all();
        $related = TourInfoForeign::select('*')
            ->where('id', '!=', $item->id)
            ->whereHas('tourCountries.infoCountry', fn($q) => $q->whereIn('id', $arrayIdTourCountry))
            ->with('seo')->get();

        $content    = $this->renderContentBlade('tour_info', $item->seo->getRawOriginal('slug'), $locale); // dùng chung folder
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.tourInfoForeign.index', compact('item', 'breadcrumb', 'content', 'related'))->render();
    }

    private function renderCategory($itemSeo, string $locale = 'vi'): ?string {
        $item = Category::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with('seo', 'tourLocations')
            ->first();
        if (!$item) return null;

        $listCategoryLv1 = Category::select('*')
            ->whereHas('seo', fn($q) => $q->where('level', 1))
            ->with('seo')->get();

        $arrayIdCategory = array_merge([$item->id], Category::getArrayCategoryChildByIdSeo($item->seo->id));

        $list = Blog::select('*')
            ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdCategory))
            ->with('seo')->get();

        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        $infoCategoryChilds = Category::select('*')
            ->whereHas('seo', fn($q) => $q->where('parent', $item->seo->id))
            ->with('seo', 'tourLocations')->get();

        if (config('modules.use_category_v2')) {
            $pageService = app(\App\Services\Blog\CategoryPageService::class);

            if (!empty($infoCategoryChilds) && $infoCategoryChilds->isNotEmpty()) {
                foreach ($infoCategoryChilds as $infoCategoryChild) {
                    $infoCategoryChild->childs = Blog::select('*')
                        ->whereHas('categories.infoCategory', fn($q) => $q->where('id', $infoCategoryChild->id))
                        ->with('seo')->get();
                }

                $page = $pageService->forParentPage($item, $infoCategoryChilds, $locale);

                return view('main.category-v2.index-parent', compact(
                    'item',
                    'breadcrumb',
                    'list',
                    'listCategoryLv1',
                    'locale',
                    'page',
                ))->render();
            }

            $blogs = Blog::select('*')
                ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdCategory))
                ->with('seo')->get();
            $page = $pageService->forPage($item, $blogs, $listCategoryLv1, $locale);

            return view('main.category-v2.index', compact(
                'item',
                'breadcrumb',
                'blogs',
                'list',
                'listCategoryLv1',
                'locale',
                'page',
            ))->render();
        }

        if (!empty($infoCategoryChilds) && $infoCategoryChilds->isNotEmpty()) {
            foreach ($infoCategoryChilds as $infoCategoryChild) {
                $infoCategoryChild->childs = Blog::select('*')
                    ->whereHas('categories.infoCategory', fn($q) => $q->where('id', $infoCategoryChild->id))
                    ->with('seo')->get();
            }
            return view('main.category.indexParent', compact('item', 'breadcrumb', 'infoCategoryChilds', 'list', 'listCategoryLv1'))->render();
        }

        $blogs = Blog::select('*')
            ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdCategory))
            ->with('seo')->get();
        return view('main.category.index', compact('item', 'breadcrumb', 'blogs', 'list', 'listCategoryLv1'))->render();
    }

    private function renderBlog($itemSeo, string $locale = 'vi'): ?string {
        $item = Blog::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with('seo')->first();
        if (!$item) return null;

        $idParent = $item->seo->parent ?? 0;
        $parent = Category::select('*')
            ->whereHas('seo', fn($q) => $q->where('id', $idParent))
            ->with('seo', 'tourLocations')->first();

        $categoryRelates = $parent ? Category::select('*')
            ->whereHas('seo', fn($q) => $q->where('parent', $parent->seo->parent))
            ->with('seo', 'tourLocations')->get() : collect();

        $arrayIdCategory = $parent
            ? array_merge([$parent->id], Category::getArrayCategoryChildByIdSeo($parent->seo->id))
            : [];
        $blogRelates = Blog::select('*')
            ->whereHas('categories.infoCategory', fn($q) => $q->whereIn('id', $arrayIdCategory))
            ->where('id', '!=', $item->id)
            ->with('seo')->get();

        $content    = $this->renderContentBlade('blog_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_blog_v2')) {
            $page = app(\App\Services\Blog\BlogPageService::class)->forPage($item, $parent, $blogRelates, $locale);

            return view('main.blog-v2.index', compact(
                'item',
                'content',
                'breadcrumb',
                'parent',
                'blogRelates',
                'categoryRelates',
                'locale',
                'page',
            ))->render();
        }

        return view('main.blog.index', compact('item', 'breadcrumb', 'parent', 'blogRelates', 'categoryRelates', 'content'))->render();
    }

    private function renderPage($itemSeo, string $locale = 'vi'): ?string {
        $item = Page::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with('seo')->first();
        if (!$item) return null;

        $content      = $this->renderContentBlade('page_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb   = Url::buildBreadcrumb($itemSeo->slug_full);
        $shipPartners = ShipPartner::select('*')->with('seo')->get();
        $airPartners  = AirPartner::select('*')->with('seo')->get();

        return view('main.page.index', compact('item', 'breadcrumb', 'shipPartners', 'airPartners', 'content'))->render();
    }

    private function renderComboLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = ComboLocation::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'combo_location')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'combo_location')])
            ->with('seo', 'combos.infoCombo.seo')
            ->first();
        if (!$item) return null;

        $fragments = app(ComboLocationFragmentService::class);
        $schemaOffer = $fragments->schemaOfferPrices($item, $locale);
        $fragmentUrls = [
            'combos' => $fragments->fragmentUrl($itemSeo->id, ComboLocationFragmentService::SECTION_LIST, $locale),
        ];

        $content    = $this->renderContentBlade('combo_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        return view('main.comboLocation.index', compact(
            'item',
            'content',
            'breadcrumb',
            'fragmentUrls',
            'schemaOffer',
            'locale'
        ))->render();
    }

    private function renderCombo($itemSeo, string $locale = 'vi'): ?string {
        $item = Combo::select('*')
            ->where('seo_id', $itemSeo->id)
            ->with(['files'     => fn($q) => $q->where('relation_table', 'combo_info')])
            ->with(['questions' => fn($q) => $q->where('relation_table', 'combo_info')])
            ->with('seo', 'locations', 'staffs.infoStaff', 'options.prices')
            ->first();
        if (!$item) return null;

        $arrayIdComboLocation = $item->locations->map(fn($l) => $l->infoLocation->id ?? null)->filter()->values()->all();
        $related = Combo::select('*')
            ->where('status_show', 1)
            ->whereHas('locations.infoLocation', fn($q) => $q->whereIn('id', $arrayIdComboLocation))
            ->with('seo')->get();

        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);
        return view('main.combo.index', compact('item', 'breadcrumb', 'related'))->render();
    }

    private function renderHotelLocation($itemSeo, string $locale = 'vi'): ?string {
        $item = HotelLocation::with([
                    'seo',
                    'files' => fn($q) => $q->where('relation_table', 'hotel_location'),
                    'questions' => fn($q) => $q->where('relation_table', 'hotel_location'),
                    'hotels' => fn ($q) => $q->whereHas('rooms'),
                    'hotels.seo',
                    'hotels.rooms.prices',
                    'hotels.comments',
                    'tourLocations.infoTourLocation.seo',
                    'tourLocations.infoTourLocation.shipLocations.infoShipLocation.seo',
                    'tourLocations.infoTourLocation.airLocations.infoAirLocation.seo',
                    'tourLocations.infoTourLocation.comboLocations.infoComboLocation.seo',
                    'tourLocations.infoTourLocation.serviceLocations.infoServiceLocation.seo',
                    'tourLocations.infoTourLocation.hotelLocations.infoHotelLocation.seo',
                    'tourLocations.infoTourLocation.carrentalLocations.infoCarrentalLocation.seo',
                    'tourLocations.infoTourLocation.guides.infoGuide.seo',
                ])
                ->where('seo_id', $itemSeo->id)
                ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('hotel_location', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_hotel_location_v2')) {
            $pageService = app(\App\Services\HotelLocation\HotelLocationPageService::class);
            $page = $pageService->forPage($item, $locale);
            $schemaOffer = $pageService->schemaOfferPrices($item, $locale);

            return view('main.hotelLocation-v2.index', compact(
                'item',
                'content',
                'breadcrumb',
                'locale',
                'page',
                'schemaOffer',
            ))->render();
        }

        return view('main.hotelLocation.index', compact('item', 'content', 'breadcrumb'))->render();
    }

    private function renderHotel($itemSeo, string $locale = 'vi'): ?string {
        $item = Hotel::query()
            ->where('seo_id', $itemSeo->id)
            ->with([
                'seo',
                'files' => fn ($q) => $q->where('relation_table', 'hotel_info'),
                'questions' => fn ($q) => $q->where('relation_table', 'hotel_info'),
                'comments',
                'images',
                'location.province',
                'location.district',
                'facilities.infoFacility',
                'rooms.prices.beds.infoHotelBed',
                'rooms.images',
                'rooms.facilities.infoHotelRoomFacility',
                'contents',
            ])
            ->first();
        if (!$item) return null;

        $content    = $this->renderContentBlade('hotel_info', $item->seo->getRawOriginal('slug'), $locale);
        $breadcrumb = Url::buildBreadcrumb($itemSeo->slug_full);

        if (config('modules.use_hotel_v2')) {
            $pageService = app(\App\Services\Hotel\HotelPageService::class);
            $page = $pageService->forPage($item, $locale);
            $schemaOffer = $pageService->schemaOfferPrices($item, $locale);

            return view('main.hotel-v2.index', compact(
                'item',
                'content',
                'breadcrumb',
                'locale',
                'page',
                'schemaOffer',
            ))->render();
        }

        return view('main.hotel.index', compact('item', 'content', 'breadcrumb'))->render();
    }

    // ================== HELPERS ==================

    /**
     * Đọc & render content theo type, slug, locale (V3.0).
     *
     * Cơ chế đọc theo thứ tự ưu tiên:
     *   1. seo_content_translations(seo_id, language_id=current).content   [DB - bản dịch]
     *   2. seo_content_translations(seo_id, language_id=default).content   [DB - bản gốc default]
     *   3. <dir>/<slug>.<locale>.blade.php                                  [file legacy theo locale]
     *   4. <dir>/<slug>.<default>.blade.php                                 [file legacy theo default]
     *   5. <dir>/<slug>.blade.php                                           [file legacy mặc định cũ]
     *
     * Trả về '' nếu không có nguồn nào (an toàn cho view).
     */
    private function renderContentBlade(string $type, ?string $slug, ?string $locale = null): string {
        if (empty($slug)) return '';
        $locale ??= app()->getLocale();
        $defaultCode = config('language.default_code', 'vi');

        // 1+2) Ưu tiên DB seo_content_translations (V3.0 multilingual)
        try {
            $seoRow = \App\Models\Seo::where('type', $type)->where('slug', $slug)->first();
            if ($seoRow) {
                $body = \App\Models\SeoContentTranslation::getContent($seoRow->id, $locale);
                if (!empty($body)) {
                    return Blade::render($body);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('renderContentBlade DB lookup failed: ' . $e->getMessage(), ['type' => $type, 'slug' => $slug]);
        }

        // 3-5) Fallback file blade legacy
        $cfg = config('tablemysql.' . $type);
        $map = [
            'ship_schedule' => 'public/contents/shipSchedule/',
        ];
        $dir = $map[$type] ?? ($cfg['content_dir'] ?? null);
        if (empty($dir)) return '';

        $candidates = [
            $dir . $slug . '.' . $locale . '.blade.php',
        ];
        if ($locale !== $defaultCode) {
            $candidates[] = $dir . $slug . '.' . $defaultCode . '.blade.php';
        }
        $candidates[] = $dir . $slug . '.blade.php';

        try {
            foreach ($candidates as $path) {
                if (Storage::exists($path)) {
                    return Blade::render(Storage::get($path));
                }
            }
            return '';
        } catch (\Throwable $e) {
            \Log::warning('renderContentBlade file fallback failed: ' . $e->getMessage(), ['type' => $type, 'slug' => $slug]);
            return '';
        }
    }

    /**
     * @deprecated Dùng HtmlCacheService::buildKey thay thế.
     */
    public static function buildNameCache($slugFull) {
        return HtmlCacheService::buildKey((string) $slugFull);
    }
}
