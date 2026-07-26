<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ShipLocation;
use App\Models\AirLocation;
use App\Models\ServiceLocation;
use App\Models\TourLocation;
use App\Models\HotelLocation;
use App\Models\ShipPartner;
use App\Models\AirPartner;
use App\Models\Seo;
use App\Models\TourTimetable;
use App\Models\TourTimetableForeign;
use App\Models\TourContent;
use App\Models\TourContentForeign;
use App\Models\HotelRoomFacility;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;

use App\Jobs\CheckSeo;
use App\Models\Redirect;

use App\Services\Fragments\HomeFragmentService;
use App\Services\HtmlCacheService;
use App\Services\HomeHero\HomeHeroService;
use App\Services\HomeIslandGallery\HomeIslandGalleryService;
use App\Services\HomeReviews\HomeReviewsService;
use App\Services\HomeFaq\HomeFaqService;
use App\Services\HomeNewsletter\HomeNewsletterService;
use App\Services\Island\HomeIslandService;
use App\Models\HomeHeroConfig;

use Illuminate\Support\Facades\File;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

use Illuminate\Support\Facades\Response;

class HomeController extends Controller {

    private $arrayData  = array();
    private $count      = 0;

    public function home(\Illuminate\Http\Request $request, HtmlCacheService $cache){
        if (config('modules.use_home_v2', false)) {
            return $this->renderHomeV2($cache);
        }

        /* Cache HTML dùng chung `HtmlCacheService::buildKeyFromRequest()` —
         * naming hoàn toàn đồng bộ với RoutingController. Trang chủ được
         * gán slug ảo `home`, sau đó áp dụng cùng luật prefix-locale +
         * suffix-currency:
         *   - `/`   (vi default) -> `home-{currency}.html`  (vd `home-vnd.html`)
         *   - `/en`              -> `en-home-{currency}.html` (vd `en-home-usd.html`)
         *   - `/cn`              -> `cn-home-{currency}.html` (vd `cn-home-cny.html`)
         * Xem docs/currency.md mục Cache HTML để biết quy ước đầy đủ.
         */
        /* Shell trang chủ cache theo locale, không suffix currency; grid vé vui chơi tải AJAX. */
        $locale   = app()->getLocale();
        $cacheKey = HtmlCacheService::homepageCacheKey($locale);

        $xhtml = $cache->getOrRender($cacheKey, function () use ($locale) {
            $item               = Seo::select('*')
                                    ->where('slug', '')
                                    ->first();
            $shipLocations      = ShipLocation::select('*')
                                    ->with('seo')
                                    ->get();
            $airLocations       = module_enabled('air')
                ? AirLocation::select('*')->with('seo')->get()
                : collect();
            $hotelLocations     = HotelLocation::select('*')
                                    ->with('seo')
                                    ->withCount('hotels')
                                    ->orderByDesc('hotels_count')
                                    ->limit(6)
                                    ->get();
            $serviceLocations   = ServiceLocation::select('*')
                                    ->where('district_id', '!=', '0') /* vé giải trí trong nước */
                                    ->with('seo')
                                    ->get();
            $islandLocations    = single_island_mode()
                ? collect()
                : TourLocation::select('*')
                                    ->where('island', '1')
                                    ->with('seo')
                                    ->get();
            $specialLocations   = TourLocation::select('*')
                                    ->where('special', '1')
                                    ->with('seo')
                                    ->get();
            $shipPartners       = ShipPartner::select('*')
                                    ->with('seo')
                                    ->get();
            $airPartners        = module_enabled('air')
                ? AirPartner::select('*')->with('seo')->get()
                : collect();
            $homeFragments      = app(HomeFragmentService::class);
            $fragmentUrls       = [
                'services' => $homeFragments->fragmentUrl(
                    (int) ($item->id ?? 0),
                    HomeFragmentService::SECTION_SERVICES,
                    $locale
                ),
            ];

            return view('main.home.home', compact(
                'item',
                'shipLocations',
                'airLocations',
                'hotelLocations',
                'serviceLocations',
                'islandLocations',
                'specialLocations',
                'shipPartners',
                'airPartners',
                'fragmentUrls',
                'locale'
            ))->render();
        }, true);

        echo $xhtml ?: '';
    }

    /**
     * Trang chủ giao diện mới (home-v2) — Blade + Vite SCSS/JS.
     */
    private function renderHomeV2(HtmlCacheService $cache): void
    {
        request()->attributes->set('superdong_page', [
            'type' => 'home',
            'slug_full' => '',
        ]);

        $locale = app()->getLocale();
        $viewPath = resource_path('views/main/home-v2/index.blade.php');
        $version = (string) (@filemtime($viewPath) ?: time());
        $heroStamp = (string) (HomeHeroConfig::query()->where('locale', $locale)->value('updated_at') ?? '0');
        $quickStamp = app(HomeIslandService::class)->cacheStamp();
        $galleryStamp = app(HomeIslandGalleryService::class)->cacheStamp($locale);
        $reviewsStamp = app(HomeReviewsService::class)->cacheStamp($locale);
        $faqStamp = app(HomeFaqService::class)->cacheStamp($locale);
        $newsletterStamp = app(HomeNewsletterService::class)->cacheStamp($locale);
        $cacheKey = 'home-v2-blade-' . $locale . '-' . $version . '-' . md5($heroStamp . '|' . $quickStamp . '|' . $galleryStamp . '|' . $reviewsStamp . '|' . $faqStamp . '|' . $newsletterStamp);

        $html = $cache->getOrRender($cacheKey, function () use ($locale): string {
            $hero = app(HomeHeroService::class)->forFrontend($locale);
            $island = app(HomeIslandService::class)->forHomePage($locale);
            $islandGallery = app(HomeIslandGalleryService::class)->forFrontend($locale);
            $homeReviews = app(HomeReviewsService::class)->forFrontend($locale);
            $homeFaq = app(HomeFaqService::class)->forFrontend($locale);
            $homeNewsletter = app(HomeNewsletterService::class)->forFrontend($locale);

            return view('main.home-v2.index', compact('hero', 'island', 'islandGallery', 'homeReviews', 'homeFaq', 'homeNewsletter'))->render();
        }, true);

        echo $html ?: '';
    }

    /* ===== tiện nghi thay tất cả các ảnh hỗ trợ Loading ===== */
    public static function changeImageInContentWithLoading(){
        $data           = glob(Storage::path('/public/contents').'/*');
        $fileSuccess    = [];
        $fileError      = [];
        foreach($data as $child){
            $dataChild  = glob($child.'/*');
            foreach($dataChild as $fileName){
                $flag   = self::actionChangeImageInContentWithLoading($fileName);
                if($flag==true) {
                    $fileSuccess[]  = $fileName;
                }else {
                    $fileError[]    = $fileName;
                }
            }
        }
        dd($fileSuccess);
    }
    public static function actionChangeImageInContentWithLoading($fileName){
        if(!empty($fileName)){
            $content        = file_get_contents($fileName);
            $content        = AdminImageController::replaceImageInContentWithLoading($content);
            return file_put_contents($fileName, $content);
        }
        return false;
    }
    public static function changeImageInContentWithLoadingTourInfo(){
        /* cập nhật content bảng tour_timetable */
        $data           = TourTimetable::select('*')
                            ->get();
        foreach($data as $item){
            $params     = [
                'content'           => AdminImageController::replaceImageInContentWithLoading($item->content),
                'content_sort'      => AdminImageController::replaceImageInContentWithLoading($item->content_sort)
            ];
            TourTimetable::updateItem($item->id, $params);
        }
        /* cập nhật content bảng tour_timetable_foreign */
        $data           = TourTimetableForeign::select('*')
                            ->get();
        foreach($data as $item){
            $params     = [
                'content'           => AdminImageController::replaceImageInContentWithLoading($item->content),
                'content_sort'      => AdminImageController::replaceImageInContentWithLoading($item->content_sort)
            ];
            TourTimetableForeign::updateItem($item->id, $params);
        }
        /* cập nhật content bảng tour_content */
        $data           = TourContent::select('*')
                            ->get();
        foreach($data as $item){
            $params     = [
                'special_content'   => AdminImageController::replaceImageInContentWithLoading($item->special_content),
                'special_list'      => AdminImageController::replaceImageInContentWithLoading($item->special_list),
                'include'           => AdminImageController::replaceImageInContentWithLoading($item->include),
                'not_include'       => AdminImageController::replaceImageInContentWithLoading($item->not_include),
                'policy_child'      => AdminImageController::replaceImageInContentWithLoading($item->policy_child),
                'menu'              => AdminImageController::replaceImageInContentWithLoading($item->menu),
                'hotel'             => AdminImageController::replaceImageInContentWithLoading($item->hotel),
                'policy_cancel'     => AdminImageController::replaceImageInContentWithLoading($item->policy_cancel)
            ];
            TourContent::updateItem($item->id, $params);
        }
        /* cập nhật content bảng tour_content_foreign */
        $data           = TourContentForeign::select('*')
                            ->get();
        foreach($data as $item){
            $params     = [
                'special_content'   => AdminImageController::replaceImageInContentWithLoading($item->special_content),
                'special_list'      => AdminImageController::replaceImageInContentWithLoading($item->special_list),
                'include'           => AdminImageController::replaceImageInContentWithLoading($item->include),
                'not_include'       => AdminImageController::replaceImageInContentWithLoading($item->not_include),
                'policy_child'      => AdminImageController::replaceImageInContentWithLoading($item->policy_child),
                'menu'              => AdminImageController::replaceImageInContentWithLoading($item->menu),
                'hotel'             => AdminImageController::replaceImageInContentWithLoading($item->hotel),
                'policy_cancel'     => AdminImageController::replaceImageInContentWithLoading($item->policy_cancel)
            ];
            TourContentForeign::updateItem($item->id, $params);
        }
    }
    /* reset tất cả checkOnpage đưa vào Job */
    public static function checkOnpageAll(){
        $seos   = Seo::select('id')
                    ->get();
        foreach($seos as $seo){
            CheckSeo::dispatch($seo->id);
        }
        return \Illuminate\Support\Facades\Redirect::to(route('main.home'), 301);
    }

    public function readWebPage(Request $request) {
        
    }

    private function getComment($url, $number, $count){
        if(!empty($url)){
            $client         = new HttpBrowser(HttpClient::create());
            $everyTime      = 5;
            // Gửi yêu cầu GET đến URL cần lấy dữ liệu
            $url            = explode('Reviews', $url);
            $url            = implode('Reviews-or'.$number, $url);
            $crawlerContent = $client->request('GET', $url);
            /* lấy comment */
            $this->count    = $count;
            if($crawlerContent->filter('[data-test-target=reviews-tab] .YibKl')->count()>0){
                $crawlerContent->filter('[data-test-target=reviews-tab] .YibKl')->each(function($node){
                    /* số sao */
                    $number         = preg_replace("/[^0-9]/", '', $node->filter('[data-test-target=review-rating] > span')->attr('class'));
                    $this->arrayData['comments'][$this->count]['stars']     = $number/10;
                    /* người đánh giá + lúc đánh giá */
                    $this->arrayData['comments'][$this->count]['person']    = $node->filter('.cRVSd')->text();
                    /* tiêu đề đánh giá */
                    $this->arrayData['comments'][$this->count]['title']     = $node->filter('.Qwuub')->text();
                    /* nội dung đánh giá */
                    $this->arrayData['comments'][$this->count]['content']   = $node->filter('.fIrGe')->text();
    
                    $this->count    += 1;
                });
                $this->getComment($url, ($number + $everyTime), $this->count);
            }else {
                return true;
            }
        }
        return true;
    }
    
}


