@if(!empty($tourTravelMegaNav))
@php
    $vnTotalDest = 0;
    if (!empty($vnZones)) {
        foreach ($vnZones as $z) {
            $vnTotalDest += is_countable($z['items'] ?? null) ? count($z['items']) : 0;
        }
    }
    $vnIntroKeys = [
        'north'   => 'mega_tour_north_intro',
        'central' => 'mega_tour_central_intro',
        'south'   => 'mega_tour_south_intro',
        'other'   => 'mega_tour_regions_other_intro',
    ];
    $vnRailImages = (array) config('mega_menu_vietnam_regions.images', []);
    $islandMega = (array) config('mega_menu_island', []);
    $islandHeroImage = trim((string) ($islandMega['image'] ?? ''));
    $islandIntroKey = trim((string) ($islandMega['intro'] ?? 'mega_tour_island_panel_intro'));
@endphp
<div class="megaMenu megaMenu--tourTravel" role="presentation">
    <div class="megaMenu_title megaMenu_title--tourTravel">
        <ul class="megaMenu_ttNavList" role="list" aria-label="{{ t('menu_tour_travel') }}">
            @foreach($tourTravelMegaNav as $nav)
                @php
                    $ttBadge = null;
                    if ($nav['type'] === 'vietnam' && $vnTotalDest > 0) {
                        $ttBadge = $vnTotalDest;
                    } elseif ($nav['type'] === 'continent') {
                        $cc = $nav['continent']->tourCountries->count();
                        $ttBadge = $cc > 0 ? $cc : null;
                    } elseif ($nav['type'] === 'island' && !empty($dataBD)) {
                        $ttBadge = count($dataBD);
                    }
                @endphp
                <li id="{{ $nav['id'] }}"
                    role="none"
                    class="{{ $loop->first ? 'selected' : '' }}"
                    @if($nav['type'] !== 'continent') tabindex="0" @endif
                    onmouseover="openMegaMenuTourTravel(this.id);"
                    @if($nav['type'] !== 'continent') onfocus="openMegaMenuTourTravel(this.id);" @endif>
                    @if($nav['type'] === 'continent')
                        <a href="{{ seo_url($nav['continent']) }}" class="megaMenu_ttNav megaMenu_ttNav--link" title="{{ t('tour_label', ['name' => $nav['continent']->display_name]) }}" onfocus="openMegaMenuTourTravel('{{ $nav['id'] }}');">
                            <span class="megaMenu_ttNav__icon megaMenu_ttNav__icon--{{ $nav['tone'] ?? 'default' }}" aria-hidden="true"><i class="fas fa-globe"></i></span>
                            <span class="megaMenu_ttNav__body">
                                <span class="megaMenu_ttNav__title">{{ t('tour_label', ['name' => $nav['continent']->display_name]) }}</span>
                            </span>
                            @if($ttBadge !== null)
                                <span class="megaMenu_ttNav__badge" aria-label="{{ $ttBadge }}">{{ $ttBadge }}</span>
                            @endif
                            <span class="megaMenu_ttNav__arrow" aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                        </a>
                    @elseif($nav['type'] === 'vietnam')
                        <div class="megaMenu_ttNav megaMenu_ttNav--vietnam">
                            <span class="megaMenu_ttNav__icon megaMenu_ttNav__icon--vietnam" aria-hidden="true"><i class="fas fa-map-marked-alt"></i></span>
                            <span class="megaMenu_ttNav__body">
                                <span class="megaMenu_ttNav__title">{{ t('mega_tour_vietnam') }}</span>
                            </span>
                            @if($ttBadge !== null)
                                <span class="megaMenu_ttNav__badge" aria-label="{{ $ttBadge }}">{{ $ttBadge }}</span>
                            @endif
                            <span class="megaMenu_ttNav__arrow" aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                        </div>
                    @else
                        <div class="megaMenu_ttNav megaMenu_ttNav--island">
                            <span class="megaMenu_ttNav__icon megaMenu_ttNav__icon--island" aria-hidden="true"><i class="fas fa-water"></i></span>
                            <span class="megaMenu_ttNav__body">
                                <span class="megaMenu_ttNav__title">{{ t('mega_tour_island') }}</span>
                            </span>
                            @if($ttBadge !== null)
                                <span class="megaMenu_ttNav__badge" aria-label="{{ $ttBadge }}">{{ $ttBadge }}</span>
                            @endif
                            <span class="megaMenu_ttNav__arrow" aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    <div class="megaMenu_content megaMenu_content--tourTravel">
        @foreach($tourTravelMegaNav as $nav)
            @if($nav['type'] === 'vietnam' && !empty($vnZones))
                @php
                    $vnFirstSlug = null;
                    foreach ($vnZones as $zz) {
                        $c = is_countable($zz['items'] ?? null) ? count($zz['items']) : 0;
                        if ($c > 0) {
                            $vnFirstSlug = $zz['slug'];
                            break;
                        }
                    }
                    if ($vnFirstSlug === null && !empty($vnZones)) {
                        $vnFirstSlug = $vnZones[0]['slug'] ?? null;
                    }
                @endphp
                <div class="megaMenu_tourTravelPanel megaMenu_tourTravelPanel--vietnam" data-menu-tour-travel="{{ $nav['id'] }}" data-vn-root="1" @if($loop->first) style="display:flex;" @else style="display:none;" @endif>
                    <div class="megaMenu_ttPanelSurface megaMenu_ttPanelSurface--vietnam megaMenu_ttPanelSurface--flat">
                        <div class="megaMenu_ttVietnamCard">
                            <div class="megaMenu_ttVietnamHead megaMenu_ttVietnamHead--rails">
                                <div class="megaMenu_ttVietnamTabWrap megaMenu_ttVietnamTabWrap--rails">
                                    <div class="megaMenu_ttVietnamRailRow" role="tablist" aria-label="{{ t('mega_tour_vietnam') }}">
                                        @foreach($vnZones as $zone)
                                            @php
                                                $zoneItems = $zone['items'];
                                                $zoneCount = is_countable($zoneItems) ? count($zoneItems) : 0;
                                                $slug = $zone['slug'];
                                                $tabId = 'vn_tab_'.$nav['id'].'_'.$slug;
                                                $panelId = 'vn_tabpanel_'.$nav['id'].'_'.$slug;
                                                $isSel = ($slug === $vnFirstSlug);
                                                $introKey = $vnIntroKeys[$slug] ?? 'mega_tour_regions_other_intro';
                                                $railImg = trim((string) ($vnRailImages[$slug] ?? ''));
                                            @endphp
                                            <button type="button"
                                                class="megaMenu_ttVietnamRail megaMenu_ttVietnamRail--{{ $slug }} @if($isSel) is-active @endif"
                                                id="{{ $tabId }}"
                                                role="tab"
                                                aria-selected="{{ $isSel ? 'true' : 'false' }}"
                                                aria-controls="{{ $panelId }}"
                                                tabindex="{{ $isSel ? '0' : '-1' }}"
                                                data-vn-tab="{{ $slug }}"
                                                aria-label="{{ t($zone['title']) }}@if($zoneCount > 0) — {{ t('mega_tt_zone_dest_count', ['count' => $zoneCount]) }}@endif"
                                                @if($zoneCount === 0) disabled aria-disabled="true" @endif
                                                onclick="switchVietnamTourTab(this);">
                                                <span
                                                    class="megaMenu_ttVietnamRail__media megaMenu_ttVietnamRail__media--placeholder{{ $railImg !== '' ? ' js-megaMenuTTBgLazy' : '' }}"
                                                    @if($railImg !== '') data-lazy-bg="{{ e($railImg) }}" @endif
                                                    aria-hidden="true"></span>
                                                <span class="megaMenu_ttVietnamRail__scrim" aria-hidden="true"></span>
                                                <span class="megaMenu_ttVietnamRail__compact" aria-hidden="true">
                                                    @if($zoneCount > 0)
                                                        <span class="megaMenu_ttVietnamRail__badge megaMenu_ttVietnamRail__badge--compact">
                                                            <span class="megaMenu_ttVietnamRail__badgeGlow" aria-hidden="true"></span>
                                                            <span class="megaMenu_ttVietnamRail__badgeDots" aria-hidden="true"></span>
                                                            <span class="megaMenu_ttVietnamRail__badgeNum">{{ $zoneCount }}</span>
                                                        </span>
                                                    @endif
                                                    <span class="megaMenu_ttVietnamRail__compactChev"><i class="fas fa-chevron-right"></i></span>
                                                </span>
                                                <span class="megaMenu_ttVietnamRail__full">
                                                    <span class="megaMenu_ttVietnamRail__fullInner">
                                                        <span class="megaMenu_ttVietnamRail__titleRow">
                                                            <span class="megaMenu_ttVietnamRail__titleLine">
                                                                <span class="megaMenu_ttVietnamRail__title">{{ t($zone['title']) }}</span>
                                                                @if($zoneCount > 0)
                                                                    <span class="megaMenu_ttVietnamRail__badge megaMenu_ttVietnamRail__badge--expanded" aria-hidden="true">
                                                                        <span class="megaMenu_ttVietnamRail__badgeCount">{{ $zoneCount }}</span>
                                                                        <span class="megaMenu_ttVietnamRail__badgeIco" aria-hidden="true"><i class="fas fa-location-dot"></i></span>
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </span>
                                                        <span class="megaMenu_ttVietnamRail__intro maxLine_1">{{ t($introKey) }}</span>
                                                    </span>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="megaMenu_ttVietnamBody megaMenu_ttVietnamBody--minimal">
                                @foreach($vnZones as $zone)
                                    @php
                                        $zoneItems = $zone['items'];
                                        $zoneCount = is_countable($zoneItems) ? count($zoneItems) : 0;
                                        $slug = $zone['slug'];
                                        $tabId = 'vn_tab_'.$nav['id'].'_'.$slug;
                                        $panelId = 'vn_tabpanel_'.$nav['id'].'_'.$slug;
                                        $isSel = ($slug === $vnFirstSlug);
                                    @endphp
                                    <div class="megaMenu_ttVietnamTabPanel"
                                        id="{{ $panelId }}"
                                        role="tabpanel"
                                        aria-labelledby="{{ $tabId }}"
                                        data-vn-tab-panel="{{ $slug }}"
                                        @if(!$isSel) hidden @endif>
                                        @if($zoneCount > 0)
                                            <nav class="megaMenu_vnDestGrid" aria-label="{{ t($zone['title']) }}">
                                                @foreach($zoneItems as $item)
                                                    <a class="megaMenu_vnDestLink" href="{{ seo_url($item) }}" title="{{ $item->name ?? $item->seo->title ?? null }}">
                                                        <span class="megaMenu_vnDestLink__dot" aria-hidden="true"></span>
                                                        @include('main.snippets.megaMenuTourDestTitle', ['item' => $item, 'textClass' => 'megaMenu_vnDestLink__text'])
                                                        <span class="megaMenu_vnDestLink__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                                                    </a>
                                                @endforeach
                                            </nav>
                                        @else
                                            <p class="megaMenu_ttVietnamTabPanel__empty">{{ t('mega_continent_empty_panel') }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($nav['type'] === 'continent')
                @php
                    $continent = $nav['continent'];
                    $countryCount = $continent->tourCountries->count();
                    $firstGallery = collect($continent->files ?? [])
                        ->where('file_type', 'gallery')
                        ->sortBy('id')
                        ->first();
                    $continentHeroImage = '';
                    if ($firstGallery) {
                        $gPath = trim((string) ($firstGallery->file_path ?? ''));
                        $gName = trim((string) ($firstGallery->file_name ?? ''));
                        if ($gPath !== '') {
                            $continentHeroImage = preg_match('#^https?://#i', $gPath)
                                ? $gPath
                                : (str_starts_with($gPath, '/') ? url($gPath) : url('/' . ltrim($gPath, '/')));
                        } elseif ($gName !== '') {
                            $continentHeroImage = \Illuminate\Support\Facades\Storage::url($gName);
                        }
                    }
                    if ($continentHeroImage === '' && !empty($continent->seo)) {
                        $seoImg = trim((string) ($continent->seo->image ?? ''));
                        if ($seoImg !== '') {
                            $continentHeroImage = preg_match('#^https?://#i', $seoImg)
                                ? $seoImg
                                : (str_starts_with($seoImg, '/') ? url($seoImg) : $seoImg);
                        }
                    }
                    $continentDesc = trim(strip_tags((string) ($continent->seo->description ?? '')));
                @endphp
                <div class="megaMenu_ttTravelPanel megaMenu_ttTravelPanel--continent" data-menu-tour-travel="{{ $nav['id'] }}" @if($loop->first) style="display:flex;" @else style="display:none;" @endif>
                    <div class="megaMenu_ttPanelSurface megaMenu_ttPanelSurface--continent megaMenu_ttPanelSurface--flat">
                        <div class="megaMenu_ttIslandCard">
                            <div class="megaMenu_ttIslandHead">
                                <div class="megaMenu_ttTourHero" role="region" aria-label="{{ t('tour_label', ['name' => $continent->display_name]) }}">
                                    <span
                                        class="megaMenu_ttTourHero__media megaMenu_ttTourHero__media--placeholder{{ $continentHeroImage !== '' ? ' js-megaMenuTTBgLazy' : '' }}"
                                        @if($continentHeroImage !== '') data-lazy-bg="{{ e($continentHeroImage) }}" @endif
                                        aria-hidden="true"></span>
                                    <span class="megaMenu_ttTourHero__scrim" aria-hidden="true"></span>
                                    <div class="megaMenu_ttTourHero__inner">
                                        <div class="megaMenu_ttTourHero__foot">
                                            <div class="megaMenu_ttTourHero__titleRow">
                                                <span class="megaMenu_ttTourHero__titleLine">
                                                    <span class="megaMenu_ttTourHero__title">{{ t('tour_label', ['name' => $continent->display_name]) }}</span>
                                                    @if($countryCount > 0)
                                                        <span class="megaMenu_ttTourHero__badge" aria-hidden="true">
                                                            <span class="megaMenu_ttTourHero__badgeCount">{{ $countryCount }}</span>
                                                            <span class="megaMenu_ttTourHero__badgeIco" aria-hidden="true"><i class="fas fa-globe"></i></span>
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                            @if($continentDesc !== '')
                                                <p class="megaMenu_ttTourHero__intro maxLine_1">{{ $continentDesc }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="megaMenu_ttIslandBody megaMenu_ttIslandBody--minimal">
                                @if($countryCount > 0)
                                    <nav class="megaMenu_islandDestGrid" aria-label="{{ t('tour_label', ['name' => $continent->display_name]) }}">
                                        @foreach($continent->tourCountries as $tourCountry)
                                            <a class="megaMenu_islandDestLink megaMenu_islandDestLink--continent" href="{{ seo_url($tourCountry) }}" title="{{ $tourCountry->name ?? $tourCountry->seo->title ?? null }}">
                                                <span class="megaMenu_islandDestLink__ico" aria-hidden="true"><i class="fas fa-location-dot"></i></span>
                                                @include('main.snippets.megaMenuTourDestTitle', ['item' => $tourCountry, 'textClass' => 'megaMenu_islandDestLink__text'])
                                                <span class="megaMenu_islandDestLink__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                                            </a>
                                        @endforeach
                                    </nav>
                                @else
                                    <p class="megaMenu_ttIslandBody__empty">{{ t('mega_continent_empty_panel') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $islandCount = is_countable($dataBD ?? null) ? count($dataBD) : 0;
                @endphp
                <div class="megaMenu_ttTravelPanel megaMenu_ttTravelPanel--island" data-menu-tour-travel="{{ $nav['id'] }}" @if($loop->first) style="display:flex;" @else style="display:none;" @endif>
                    <div class="megaMenu_ttPanelSurface megaMenu_ttPanelSurface--island megaMenu_ttPanelSurface--flat">
                        <div class="megaMenu_ttIslandCard">
                            <div class="megaMenu_ttIslandHead">
                                <div class="megaMenu_ttTourHero" role="region" aria-label="{{ t('mega_tour_island') }}">
                                    <span
                                        class="megaMenu_ttTourHero__media megaMenu_ttTourHero__media--placeholder{{ $islandHeroImage !== '' ? ' js-megaMenuTTBgLazy' : '' }}"
                                        @if($islandHeroImage !== '') data-lazy-bg="{{ e($islandHeroImage) }}" @endif
                                        aria-hidden="true"></span>
                                    <span class="megaMenu_ttTourHero__scrim" aria-hidden="true"></span>
                                    <div class="megaMenu_ttTourHero__inner">
                                        <div class="megaMenu_ttTourHero__foot">
                                            <div class="megaMenu_ttTourHero__titleRow">
                                                <span class="megaMenu_ttTourHero__titleLine">
                                                    <span class="megaMenu_ttTourHero__title">{{ t('mega_tour_island') }}</span>
                                                    @if($islandCount > 0)
                                                        <span class="megaMenu_ttTourHero__badge" aria-hidden="true">
                                                            <span class="megaMenu_ttTourHero__badgeCount">{{ $islandCount }}</span>
                                                            <span class="megaMenu_ttTourHero__badgeIco" aria-hidden="true"><i class="fas fa-umbrella-beach"></i></span>
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>
                                            <p class="megaMenu_ttTourHero__intro maxLine_1">{{ t($islandIntroKey) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="megaMenu_ttIslandBody megaMenu_ttIslandBody--minimal">
                                @if($islandCount > 0)
                                    <nav class="megaMenu_islandDestGrid" aria-label="{{ t('mega_tour_island') }}">
                                        @foreach($dataBD as $item)
                                            <a class="megaMenu_islandDestLink" href="{{ seo_url($item) }}" title="{{ $item->name ?? $item->seo->title ?? null }}">
                                                <span class="megaMenu_islandDestLink__ico" aria-hidden="true"><i class="fas fa-umbrella-beach"></i></span>
                                                @include('main.snippets.megaMenuTourDestTitle', ['item' => $item, 'textClass' => 'megaMenu_islandDestLink__text'])
                                                <span class="megaMenu_islandDestLink__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                                            </a>
                                        @endforeach
                                    </nav>
                                @else
                                    <p class="megaMenu_ttIslandBody__empty">{{ t('mega_continent_empty_panel') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif
