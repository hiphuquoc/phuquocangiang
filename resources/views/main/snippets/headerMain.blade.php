@php
    /* Vé máy bay */
    $dataAir            = module_enabled('air')
        ? \App\Models\AirLocation::select('*')
                            ->with('seo', 'airs.seo')
                            ->get()
        : collect();
    /* Tàu cao tốc */
    $dataShip           = \App\Models\ShipLocation::select('*')
                            ->with('seo', 'ships.seo')
                            ->get();
    /* Vé vui chơi */
    $dataService        = \App\Models\ServiceLocation::select('*')
                            ->whereHas('services', function(){
                                
                            })
                            ->with('seo')
                            ->get();
    /* Tour trong nước & biển đảo — tất cả điểm đến; tours_count = tour đang hiển thị */
    $dataMB             = [];
    $dataMT             = [];
    $dataMN             = [];
    $dataMOther         = [];
    $dataBD             = [];
    $infoMenuByRegion   = \App\Models\TourLocation::select('*')
                            ->with('seo', 'region')
                            ->orderBy('name')
                            ->withCount(['tours as tours_count' => function ($q) {
                                $q->whereHas('infoTour', function ($q2) {
                                    $q2->where('status_show', 1);
                                });
                            }])
                            ->get();
    foreach ($infoMenuByRegion as $item) {
        $rid = (int) ($item->region_id ?? 0);
        switch ($rid) {
            case 1:
                $dataMB[] = $item;
                break;
            case 2:
                $dataMT[] = $item;
                break;
            case 3:
                $dataMN[] = $item;
                break;
            default:
                if (empty($item->island)) {
                    $dataMOther[] = $item;
                }
                break;
        }
        if (!empty($item->island)) {
            $dataBD[] = $item;
        }
    }
    if (single_island_mode() && empty($dataBD)) {
        foreach ($infoMenuByRegion as $item) {
            $dataBD[] = $item;
        }
    }
    $vnZones = array_values(array_filter([
        !empty($dataMN) ? ['items' => $dataMN, 'title' => 'mega_tour_south', 'slug' => 'south'] : null,
        !empty($dataMT) ? ['items' => $dataMT, 'title' => 'mega_tour_central', 'slug' => 'central'] : null,
        !empty($dataMB) ? ['items' => $dataMB, 'title' => 'mega_tour_north', 'slug' => 'north'] : null,
        !empty($dataMOther) ? ['items' => $dataMOther, 'title' => 'mega_tour_regions_other', 'slug' => 'other'] : null,
    ]));
    if (single_island_mode()) {
        $dataMB = $dataMT = $dataMN = $dataMOther = [];
        $vnZones = [];
    }
    /* Tour nước ngoài — tất cả quốc gia; tours_count = tour đang hiển thị (status_show) */
    $dataTourContinent  = module_enabled('tour_foreign')
        ? \App\Models\TourContinent::select('*')
                            ->with(['tourCountries' => function ($query) {
                                $query->with('seo')
                                    ->orderBy('name')
                                    ->withCount(['tours as tours_count' => function ($q) {
                                        $q->whereHas('infoTourForeign', function ($q2) {
                                            $q2->where('status_show', 1);
                                        });
                                    }]);
                            }])
                            ->with(['files' => function ($query) {
                                $query->where('relation_table', 'tour_continent');
                            }])
                            ->with('seo')
                            ->get()
        : collect();
    /* Thứ tự châu lục trong mega menu: Châu Á → Âu → Mỹ → Úc → Phi (nhận diện đa ngôn ngữ) */
    $continentDisplayRank = static function ($c) {
        $t = mb_strtolower((string)($c->display_name ?? $c->name ?? ''), 'UTF-8');
        if (str_contains($t, 'châu á') || str_contains($t, 'asia') || str_contains($t, '亚洲') || str_contains($t, '亞洲') || str_contains($t, 'アジア') || str_contains($t, '아시아')) {
            return 1;
        }
        if (str_contains($t, 'châu âu') || str_contains($t, 'europe') || str_contains($t, '欧洲') || str_contains($t, '歐洲') || str_contains($t, 'europa') || str_contains($t, 'ヨーロッパ') || str_contains($t, '유럽')) {
            return 2;
        }
        if (str_contains($t, 'châu mỹ') || str_contains($t, 'americ') || str_contains($t, '拉丁') || str_contains($t, 'latin') || str_contains($t, '中南米')) {
            return 3;
        }
        if (str_contains($t, 'châu úc') || str_contains($t, 'oceania') || str_contains($t, 'australia') || str_contains($t, '大洋洲') || str_contains($t, '澳大利') || str_contains($t, 'オセアニア') || str_contains($t, '호주')) {
            return 4;
        }
        if (str_contains($t, 'châu phi') || str_contains($t, 'africa') || str_contains($t, '非洲') || str_contains($t, 'アフリカ') || str_contains($t, '아프리카')) {
            return 5;
        }
        return 50;
    };
    $dataTourContinent = $dataTourContinent->sortBy($continentDisplayRank)->values();
    /* Màu / nhóm icon cột trái theo tên châu (đồng bộ nhận diện với sort) */
    $continentToneFromName = static function ($c) {
        $t = mb_strtolower((string) ($c->display_name ?? $c->name ?? ''), 'UTF-8');
        if (str_contains($t, 'châu á') || str_contains($t, 'asia') || str_contains($t, '亚洲') || str_contains($t, '亞洲') || str_contains($t, 'アジア') || str_contains($t, '아시아')) {
            return 'asia';
        }
        if (str_contains($t, 'châu âu') || str_contains($t, 'europe') || str_contains($t, '欧洲') || str_contains($t, '歐洲') || str_contains($t, 'europa') || str_contains($t, 'ヨーロッパ') || str_contains($t, '유럽')) {
            return 'europe';
        }
        if (str_contains($t, 'châu mỹ') || str_contains($t, 'americ') || str_contains($t, '拉丁') || str_contains($t, 'latin') || str_contains($t, '中南米')) {
            return 'americas';
        }
        if (str_contains($t, 'châu úc') || str_contains($t, 'oceania') || str_contains($t, 'australia') || str_contains($t, '大洋洲') || str_contains($t, '澳大利') || str_contains($t, 'オセアニア') || str_contains($t, '호주')) {
            return 'oceania';
        }
        if (str_contains($t, 'châu phi') || str_contains($t, 'africa') || str_contains($t, '非洲') || str_contains($t, 'アフリカ') || str_contains($t, '아프리카')) {
            return 'africa';
        }
        return 'default';
    };
    /* Cột trái mega menu Tour du lịch (Việt Nam → các châu → Biển Đảo) */
    $tourTravelMegaNav = [];
    $tourTravelMenuIdx = 0;
    if (!empty($dataMN) || !empty($dataMT) || !empty($dataMB) || !empty($dataMOther)) {
        $tourTravelMegaNav[] = ['type' => 'vietnam', 'id' => 'menuTourTravel_'.$tourTravelMenuIdx++, 'tone' => 'vietnam'];
    }
    foreach ($dataTourContinent as $tourContinent) {
        $tourTravelMegaNav[] = [
            'type'      => 'continent',
            'continent' => $tourContinent,
            'id'        => 'menuTourTravel_'.$tourTravelMenuIdx++,
            'tone'      => $continentToneFromName($tourContinent),
        ];
    }
    if (!empty($dataBD)) {
        $tourTravelMegaNav[] = ['type' => 'island', 'id' => 'menuTourTravel_'.$tourTravelMenuIdx++, 'tone' => 'island'];
    }
    /* khách sạn */
    $dataHotelLocation  = \App\Models\HotelLocation::select('*')
                            ->with('hotels', function($query){

                            })
                            ->get();
    $hotelMB            = [];
    $hotelMT            = [];
    $hotelMN            = [];
    $hotelBD            = [];
    foreach($dataHotelLocation as $item){
        /* vùng miền */
        switch($item->region->id){
            case 1:
                $hotelMB[]  = $item;
                break;
            case 2:
                $hotelMT[]  = $item;
                break;
            case 3:
                $hotelMN[]  = $item;
                break;
            default:
                break;
        }
        /* biển đảo */
        if($item->island==true) $hotelBD[] = $item;
    }
    /* Combo du lịch */
    $dataComboLocation  = \App\Models\ComboLocation::select('*')
                            ->whereHas('combos', function($query){
                                
                            })
                            ->with('seo', 'region', 'combos')
                            ->get();
    $combosMB           = [];
    $combosMT           = [];
    $combosMN           = [];
    $combosBD           = [];
    foreach($dataComboLocation as $item){
        /* vùng miền */
        switch($item->region->id){
            case 1:
                $combosMB[] = $item;
                break;
            case 2:
                $combosMT[] = $item;
                break;
            case 3:
                $combosMN[] = $item;
                break;
            default:
                break;
        }
        /* biển đảo */
        if($item->island==true) $combosBD[] = $item;
    }
    if (single_island_mode()) {
        $hotelMB = $hotelMT = $hotelMN = [];
        $combosMB = $combosMT = $combosMN = [];
    }
     /* Tour trong nước */
     $guideMenuByRegion = \App\Models\Guide::select('*')
                            ->with('seo', 'region')
                            ->get();
    $guideMB            = [];
    $guideMT            = [];
    $guideMN            = [];
    foreach($guideMenuByRegion as $item){
        /* vùng miền */
        switch($item->region->id){
            case 1:
                $guideMB[]   = $item;
                break;
            case 2:
                $guideMT[]   = $item;
                break;
            case 3:
                $guideMN[]   = $item;
                break;
            default:
                break;
        }
    }
@endphp

<!-- START:: Menu Desktop -->
<div class="headerMain">
    <div class="container">
        <div class="headerMain_item">
            <ul>
                <li>
                    <a href="{{ home_url() }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}">
                        <img src="/images/main/svg/home-fff.svg" alt="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}" />
                    </a>
                </li>
                @if(!empty($tourTravelMegaNav))
                <li>
                    <div>
                        <div>{{ t('menu_tour_travel') }}</div>
                    </div>
                    @include('main.snippets.megaMenuTourTravel', compact('tourTravelMegaNav', 'dataMB', 'dataMT', 'dataMN', 'dataBD', 'vnZones'))
                </li>
                @endif
                <li>
                    <div>
                        <div>{{ t('menu_hotel') }}</div>
                    </div>
                    @include('main.snippets.megaMenuHotel', compact('hotelMB', 'hotelMT', 'hotelMN', 'hotelBD'))
                </li>
                @if(!empty($dataComboLocation)&&$dataComboLocation->isNotEmpty())
                    <li>
                        <div>
                            <div>{{ t('menu_combo') }}</div>
                        </div>
                        @include('main.snippets.megaMenuCombo', compact('combosMB', 'combosMT', 'combosMN', 'combosBD'))
                    </li>
                @endif
            </ul>
        </div>
        <div class="headerMain_item" style="flex:0 0 70px;">
            @if(Request::is('/') || request()->routeIs('main.home.locale'))
                <a href="{{ home_url() }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}" class="logoSquare"><h1 style="display:none;">{{ config('main.description') }}</h1></a>
            @else 
                <a href="{{ home_url() }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}" class="logoSquare"></a>
            @endif
        </div>
        <div class="headerMain_item">
            <ul style="justify-content:flex-end;">
                @if(module_enabled('air') && !empty($dataAir) && $dataAir->isNotEmpty())
                <li>
                    <div>
                        <div>{{ t('menu_air_ticket') }}</div>
                    </div>
                    <div class="megaMenu">
                            <div class="megaMenu_content" style="width:100%;">
                                <ul>
                                    @php
                                        $i = 0;
                                        $xhtml = null;
                                        foreach($dataAir as $air){
                                            if($i==0) $xhtml = '<li><ul>';
                                            if($i!=0&&$i%7==0) $xhtml .= '</ul></li><li><ul>';
                                            $xhtml .= '<li><a href="'.e(seo_url($air)).'" title="'.e($air->name).'">'.e($air->name).'</a></li>';
                                            if($i==($dataAir->count()-1)) $xhtml .= '</ul></li>';
                                            ++$i;
                                        }
                                        echo $xhtml;
                                    @endphp
                                </ul>
                            </div>
                        </div>
                </li>
                @endif
                <li>
                    <div>
                        <div>{{ t('menu_ship_ticket') }}</div>
                    </div>
                    @if(!empty($dataShip)&&$dataShip->isNotEmpty())
                    <div class="normalMenu">
                        <ul>
                            @foreach($dataShip as $shipLocation)
                            <li>
                                <a class="max-line_1" href="{{ seo_url($shipLocation) }}" title="{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}">{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}<i class="fas fa-angle-right"></i></a>
                                @if(!empty($shipLocation->ships))
                                    <ul class="submenu-flip-left">
                                    @foreach($shipLocation->ships as $ship)
                                        <li class="max-line_1">
                                            <a href="{{ seo_url($ship) }}" title="{{ $ship->name ?? $ship->seo->title ?? null }}">
                                                <div>{{ $ship->name ?? $ship->seo->title ?? null }}</div>
                                            </a>
                                        </li>
                                    @endforeach
                                    </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </li>
                <li>
                    <div>
                        <div>{{ t('menu_entertainment_ticket') }}</div>
                    </div>
                    <div class="normalMenu">
                        <ul>
                            @foreach($dataService as $serviceLocation)
                            <li>
                                <a class="max-line_1" href="{{ seo_url($serviceLocation) }}" title="{{ $serviceLocation->name ?? $serviceLocation->seo->title ?? null }}">{{ $serviceLocation->display_name ?? null }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                
                <li>
                    <div>
                        <i class="fa-solid fa-bars" style="font-size:1.4rem;margin-top:0.25rem;"></i>
                    </div>
                    <div class="normalMenu" style="margin-right:1.5rem;right:0;">
                        <ul>
                            {{-- <li>
                                <a href="#">
                                    <div>Cho thuê xe</div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div>Cẩm nang du lịch</div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div>Điểm đến</div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div>Đặc sản</div>
                                </a>
                            </li> --}}
                            <li>
                                <a href="{{ seo_url('lien-he-hitour') }}" title="{{ t('menu_contact_company', ['brand' => config('company.sortname')]) }}">
                                    <div>{{ t('contact') }}</div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- Background Hover -->
{{-- <div class="backgroundHover"></div> --}}
<!-- END:: Menu Desktop -->

<input type="hidden" id="language" value="{{ current_locale() }}">

<!-- START:: Header Mobile -->
<div class="header">
    <div class="container">
        <div class="header_logo">
            <a href="{{ home_url() }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}" class="logo">
                <h1 style="display:none;">{{ config('main.description') }}</h1>
            </a>
        </div>
        <div class="header_mobileActions">
            {{-- Region switcher dùng chung với headerTop nhưng variant mobile:
                 icon button 30x30 đồng bộ với các action button khác, click
                 mở 1 dialog fullscreen (cùng nội dung Ngôn ngữ + Đồng tiền). --}}
            <div class="header_mobileActions_item header_mobileActions_item--region">
                @include('main.snippets.regionSwitcher', ['variant' => 'mobile'])
            </div>
            <div id="js_checkLoginAndSetShow_buttonMobile" class="header_mobileActions_item"></div>
            <div class="header_mobileActions_item">
                <button type="button" class="header_mobileActions_btn" onclick="openCloseElemt('nav-mobile');" aria-label="{{ t('open_menu') }}">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END:: Header Mobile -->

<style>
    .header > .container {
        /* min-height: 3.45rem; */
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding-left: max(0.75rem, env(safe-area-inset-left));
        padding-right: max(0.75rem, env(safe-area-inset-right));
    }
    /* Ghi đè style.scss (.header .container > * margin) — tránh chồng margin + gap */
    .header > .container > *:not(:first-child) {
        margin-left: 0 !important;
    }
    .header .header_logo {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        align-items: center;
    }
    .header .header_logo .logo {
        display: inline-flex;
        align-items: center;
        height: 2rem;
    }
    .header_mobileActions {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        gap: 0.45rem;
        margin-left: auto;
    }
    .header_mobileActions_item {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    /* Cùng chiều cao với languageSwitcher_trigger (30px) trên desktop */
    .header_mobileActions_btn {
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        width: 30px;
        height: 30px;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background .15s ease, border-color .15s ease;
    }
    .header_mobileActions_btn:active,
    .header_mobileActions_btn:focus {
        outline: none;
    }
    .header_mobileActions_btn i { font-size: 0.95rem; line-height: 1; }
    #js_checkLoginAndSetShow_buttonMobile {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    #js_checkLoginAndSetShow_buttonMobile .headerBottom_item {
        margin: 0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.12);
        border-radius: 0.5rem;
        padding: 0;
    }
    #js_checkLoginAndSetShow_buttonMobile .headerBottom_item_text { display: none; }
    #js_checkLoginAndSetShow_buttonMobile .headerBottom_item_icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 0;
    }
    #js_checkLoginAndSetShow_buttonMobile .headerBottom_item_icon img { width: 0.9rem; height: 0.9rem; object-fit: contain; display: block; }
</style>

<!-- START:: Menu Mobile -->
<div id="nav-mobile" style="display:none;" aria-hidden="true">
    <div class="nav-mobile" role="dialog" aria-modal="true" aria-label="{{ t('nav_menu') }}">
        <div class="nav-mobile_bg" onclick="openCloseElemt('nav-mobile');"></div>
        <div class="nav-mobile_main customScrollBar-y">
            {{-- Sticky header bar: logo + close --}}
            <div class="nav-mobile_main__header">
                <a class="nav-mobile_main__brand" href="{{ home_url() }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}">
                    <span class="logoSquare" aria-hidden="true"></span>
                </a>
                <button type="button" class="nav-mobile_main__exit" onclick="openCloseElemt('nav-mobile');" aria-label="{{ t('close_menu') }}">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <ul>
                <li>
                    <a href="{{ home_url() }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span class="nav-mobile_main__title">{{ t('home') }}</span>
                    </a>
                </li>
                {{-- <li>
                    <div>
                        <i class="fas fa-umbrella-beach"></i>
                        Tour nước ngoài
                    </div>
                    <span class="right-icon" onclick="showHideListMenuMobile(this);"><i class="fas fa-chevron-right"></i></span>
                    <ul style="display:none;">
                    @foreach($dataTourContinent as $tourContinent)
                        <li>
                            <a href="{{ seo_url($tourContinent) }}" title="{{ $tourContinent->name ?? $tourContinent->seo->title ?? null }}">
                                <div>{{ $tourContinent->name ?? $tourContinent->seo->title ?? null }}</div>
                            </a>
                        </li>
                    @endforeach
                    </ul>
                </li> --}}
                <li>
                    <div onclick="showHideListMenuMobile(this);">
                        <i class="fas fa-umbrella-beach"></i>
                        <span class="nav-mobile_main__title">{{ t('mb_tour_travel') }}</span>
                        <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <ul style="display:none;">
                        @if(!single_island_mode() && (!empty($dataMN) || !empty($dataMT) || !empty($dataMB) || !empty($dataMOther)))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mega_tour_vietnam') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                    @if(!empty($dataMN))
                                        <li>
                                            <div onclick="showHideListMenuMobile(this);">
                                                <span class="nav-mobile_main__title">{{ t('mb_tour_south') }}</span>
                                                <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                            </div>
                                            <ul style="display:none;">
                                            @foreach($dataMN as $tourMN)
                                                <li>@include('main.snippets.navMobileTourDestLink', ['item' => $tourMN])</li>
                                            @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                    @if(!empty($dataMT))
                                        <li>
                                            <div onclick="showHideListMenuMobile(this);">
                                                <span class="nav-mobile_main__title">{{ t('mb_tour_central') }}</span>
                                                <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                            </div>
                                            <ul style="display:none;">
                                            @foreach($dataMT as $tourMT)
                                                <li>@include('main.snippets.navMobileTourDestLink', ['item' => $tourMT])</li>
                                            @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                    @if(!empty($dataMB))
                                        <li>
                                            <div onclick="showHideListMenuMobile(this);">
                                                <span class="nav-mobile_main__title">{{ t('mb_tour_north') }}</span>
                                                <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                            </div>
                                            <ul style="display:none;">
                                            @foreach($dataMB as $tourMB)
                                                <li>@include('main.snippets.navMobileTourDestLink', ['item' => $tourMB])</li>
                                            @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                    @if(!empty($dataMOther))
                                        <li>
                                            <div onclick="showHideListMenuMobile(this);">
                                                <span class="nav-mobile_main__title">{{ t('mega_tour_regions_other') }}</span>
                                                <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                            </div>
                                            <ul style="display:none;">
                                            @foreach($dataMOther as $tourO)
                                                <li>@include('main.snippets.navMobileTourDestLink', ['item' => $tourO])</li>
                                            @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if(module_enabled('tour_foreign'))
                        @foreach($dataTourContinent as $tourContinent)
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('tour_label', ['name' => $tourContinent->display_name]) }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                    <li>
                                        <a href="{{ seo_url($tourContinent) }}" title="{{ t('tour_label', ['name' => $tourContinent->display_name]) }}">
                                            <div>{{ t('view_all') }}</div>
                                        </a>
                                    </li>
                                    @foreach($tourContinent->tourCountries as $tourCountry)
                                        <li>@include('main.snippets.navMobileTourDestLink', ['item' => $tourCountry])</li>
                                    @endforeach
                                    @if($tourContinent->tourCountries->isEmpty())
                                        <li>
                                            <div style="padding:0.35rem 0.25rem 0.65rem;font-size:0.82rem;color:#64748b;line-height:1.45;">{{ t('mega_continent_empty_panel') }}</div>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endforeach
                        @endif
                        @if(!empty($dataBD))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_tour_island') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($dataBD as $tourBD)
                                    <li>@include('main.snippets.navMobileTourDestLink', ['item' => $tourBD])</li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
                
                
                @if(!empty($dataShip)&&$dataShip->isNotEmpty())
                    <li>
                        <div onclick="showHideListMenuMobile(this);">
                            <i class="fas fa-ship"></i>
                            <span class="nav-mobile_main__title">{{ t('mb_ship_speedboat') }}</span>
                            <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                        <ul style="display:none;">
                        @foreach($dataShip as $shipLocation)
                            <li>
                                <a href="{{ seo_url($shipLocation) }}" title="{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}">
                                    <div>{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}</div>
                                </a>
                            </li>
                        @endforeach
                        </ul>
                    </li>
                @endif

                <li>
                    <div onclick="showHideListMenuMobile(this);">
                        <i class="fa-solid fa-bed"></i>
                        <span class="nav-mobile_main__title">{{ t('mb_hotel') }}</span>
                        <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <ul style="display:none;">
                        @if(!empty($hotelBD))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_hotel_island') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($hotelBD as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ $h->name ?? $h->seo->title ?? null }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                        @if(!empty($hotelMB))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_hotel_north') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($hotelMB as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ $h->name ?? $h->seo->title ?? null }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                        @if(!empty($hotelMT))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_hotel_central') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($hotelMT as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ $h->name ?? $h->seo->title ?? null }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                        @if(!empty($hotelMN))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_hotel_south') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($hotelMN as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ $h->name ?? $h->seo->title ?? null }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>

                <li>
                    <div onclick="showHideListMenuMobile(this);">
                        <i class="fa-solid fa-star"></i>
                        <span class="nav-mobile_main__title">{{ t('mb_entertainment') }}</span>
                        <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <ul style="display:none;">
                        @foreach($dataService as $serviceLocation)
                            <li>
                                <a href="{{ seo_url($serviceLocation) }}" title="{{ $serviceLocation->display_name ?? null }}">
                                    <div>{{ $serviceLocation->display_name ?? null }}</div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                
                @if(module_enabled('air') && !empty($dataAir) && $dataAir->isNotEmpty())
                <li>
                    <div onclick="showHideListMenuMobile(this);">
                        <i class="fa-solid fa-plane-departure"></i>
                        <span class="nav-mobile_main__title">{{ t('mb_air') }}</span>
                        <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <ul style="display:none;">
                    @foreach($dataAir as $airLocation)
                        <li>
                            <a href="{{ seo_url($airLocation) }}" title="{{ $airLocation->name ?? $airLocation->seo->title ?? null }}">
                                <div>{{ $airLocation->name ?? $airLocation->seo->title ?? null }}</div>
                            </a>
                        </li>
                    @endforeach
                    </ul>
                </li>
                @endif
                
                <li>
                    <div onclick="showHideListMenuMobile(this);">
                        <i class="fa-solid fa-book"></i>
                        <span class="nav-mobile_main__title">{{ t('mb_travel_guide') }}</span>
                        <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <ul style="display:none;">
                        @if(!empty($guideMB))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_guide_north') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($guideMB as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ t('guide_region', ['name' => $h->display_name ?? '']) }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                        @if(!empty($guideMT))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_guide_central') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($guideMT as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ t('guide_region', ['name' => $h->display_name ?? '']) }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                        @if(!empty($guideMN))
                            <li>
                                <div onclick="showHideListMenuMobile(this);">
                                    <span class="nav-mobile_main__title">{{ t('mb_guide_south') }}</span>
                                    <span class="nav-mobile_main__arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <ul style="display:none;">
                                @foreach($guideMN as $h)
                                    <li>
                                        <a href="{{ seo_url($h) }}" title="{{ $h->name ?? $h->seo->title ?? null }}">
                                            <div>{{ t('guide_region', ['name' => $h->display_name ?? '']) }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>

                <li>
                    <a href="{{ seo_url('lien-he-hitour') }}" title="{{ t('menu_contact_company', ['brand' => config('company.sortname')]) }}">
                        <i class="fa-solid fa-phone"></i>
                        <span class="nav-mobile_main__title">{{ t('mb_contact') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- END:: Menu Mobile -->