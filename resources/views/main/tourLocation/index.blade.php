@extends('main.layouts.main')
@push('head-custom')
<!-- ===== START:: SCHEMA ===== -->
@php
    $dataSchema = $item->seo ?? null;
@endphp
<!-- STRAT:: Title - Description - Social -->
@include('main.schema.social', ['data' => $dataSchema])
<!-- END:: Title - Description - Social -->

<!-- STRAT:: Organization Schema -->
@include('main.schema.organization')
<!-- END:: Organization Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.article', ['data' => $dataSchema])
<!-- END:: Article Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
<!-- END:: Article Schema -->

<!-- STRAT:: Product Schema (giá theo currency đại diện của locale, không theo cookie) -->
@include('main.schema.product', [
    'data' => $dataSchema,
    'files' => $item->files,
    'lowPrice' => $schemaOffer['low'] ?? 3000000,
    'highPrice' => $schemaOffer['high'] ?? 5000000,
    'priceCurrency' => $schemaOffer['currency'] ?? schema_currency(),
])
<!-- END:: Product Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
<!-- END:: Article Schema -->

<!-- STRAT:: FAQ Schema -->
@include('main.schema.faq', ['data' => $item->questions])
<!-- END:: FAQ Schema -->

@php
    $dataList           = new \Illuminate\Support\Collection();
    if(!empty($item->tours)&&$item->tours->isNotEmpty()){
        foreach($item->tours as $tour){
            $dataList[] = $tour->infoTour;
        }
    }
@endphp
<!-- STRAT:: Article Schema -->
@include('main.schema.itemlist', ['data' => $dataList])
<!-- END:: Article Schema -->

<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

<div class="pageListing">

    @include('main.form.sortBooking', [
        'item'      => $item,
        'active'    => 'tour'
    ])

    @include('main.snippets.breadcrumb')

    <div class="pageContent">

        <!-- Tour box -->
        <div class="sectionBox backgroundPrimaryGradiend">
            <div class="container">
                @include('main.snippets.listingHeadRow', [
                    'kicker' => t('kicker_tour_list'),
                    'title' => t('tour_list_title', ['name' => $item->display_name ?? '']),
                    'tag' => 'h1',
                    'titleClass' => 'titlePage',
                    'withSectionTitleClass' => false,
                ])
                <p class="sectionBox_desc">{!! t('tour_list_desc', ['name' => e($item->display_name ?? ''), 'brand' => e(config('main.name'))]) !!}</p>
                @include('main.tourLocation.filterBox')
                @include('main.snippets.pageFragment', [
                    'fragmentKind' => 'tour-location',
                    'url' => $fragmentUrls['tours'] ?? '',
                    'seoId' => $item->seo_id,
                    'locale' => $locale ?? app()->getLocale(),
                    'section' => 'tours',
                    'minHeight' => 440,
                    'skeleton' => 'tourGrid',
                    'ariaLabel' => t('tour_list_title', ['name' => $item->display_name ?? '']),
                ])
                @if(!empty($dataToursNoscript) && $dataToursNoscript->isNotEmpty())
                    <noscript>
                        @include('main.tourLocation.tourItem', ['list' => $dataToursNoscript])
                    </noscript>
                @endif
            </div>
        </div>

        <!-- START:: Video -->
        @include('main.tourLocation.videoBox', [
            'item'  => $item,
            'title' => t('tour_video_title', ['name' => $item->display_name ?? ''])
        ])
        <!-- END:: Video -->

        <!-- Bài đọc — kinh nghiệm (sau video) -->
        @include('main.tourLocation.tourGuideIntro', ['item' => $item, 'content' => $content ?? null])

        <!-- Hướng dẫn đặt Tour -->
        @include('main.tourLocation.guideBook', [
            'title' => t('tour_guide_title', ['name' => $item->display_name ?? '']),
            'kicker' => t('kicker_book_tour'),
        ])

        <!-- Combo du lịch -->
        @if(!empty($item->comboLocations) && $item->comboLocations->isNotEmpty())
            <div class="sectionBox">
                <div class="container">
                    @if(!empty($item->comboLocations[0]->infoComboLocation->seo->slug_full))
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_combo'),
                            'title' => t('tour_combo_title', ['name' => $item->display_name ?? '']),
                            'titleUrl' => '/'.$item->comboLocations[0]->infoComboLocation->seo->slug_full,
                            'titleAttr' => t('tour_combo_title', ['name' => $item->display_name ?? '']),
                        ])
                    @else
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_combo'),
                            'title' => t('tour_combo_title', ['name' => $item->display_name ?? '']),
                        ])
                    @endif
                    <p class="sectionBox_desc">{{ t('tour_combo_desc', ['name' => $item->display_name ?? '']) }}</p>
                    @include('main.snippets.pageFragment', [
                        'fragmentKind' => 'tour-location',
                        'url' => $fragmentUrls['combo'] ?? '',
                        'seoId' => $item->seo_id,
                        'locale' => $locale ?? app()->getLocale(),
                        'section' => 'combo',
                        'minHeight' => 380,
                        'skeleton' => 'tourGrid',
                        'ariaLabel' => t('tour_combo_title', ['name' => $item->display_name ?? '']),
                    ])
                </div>
            </div>
        @endif

        <!-- Cho thuê xe -->
        @if(!empty($item->carrentalLocations[0]->infoCarrentalLocation))
            <div class="sectionBox withBorder">
                <div class="container">
                    @include('main.snippets.listingHeadRow', [
                        'kicker' => t('kicker_transport'),
                        'title' => t('tour_carrental_title', ['name' => $item->display_name ?? '']),
                    ])
                    <p class="sectionBox_desc">{!! t('tour_carrental_desc', ['name' => e($item->display_name ?? ''), 'brand' => e(config('main.name'))]) !!}</p>
                    <div class="guideList">
                        @foreach($item->carrentalLocations as $carrentalLocation)
                            <div class="guideList_item">
                                <i class="fa-solid fa-angles-right"></i>{{ t('tour_carrental_see_more') }} <a href="/{{ $carrentalLocation->infoCarrentalLocation->seo->slug_full }}" title="{{ $carrentalLocation->infoCarrentalLocation->name }}">{{ $carrentalLocation->infoCarrentalLocation->name }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Vé máy bay -->
        @if(!empty($item->airLocations) && $item->airLocations->isNotEmpty())
            <div class="sectionBox">
                <div class="container">
                    @if(!empty($item->airLocations[0]->infoAirLocation->seo->slug_full))
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_air'),
                            'title' => t('tour_air_title', ['name' => $item->display_name ?? '']),
                            'titleUrl' => '/'.$item->airLocations[0]->infoAirLocation->seo->slug_full,
                            'titleAttr' => t('tour_air_title', ['name' => $item->display_name ?? '']),
                        ])
                    @else
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_air'),
                            'title' => t('tour_air_title', ['name' => $item->display_name ?? '']),
                        ])
                    @endif
                    <p class="sectionBox_desc">{!! t('tour_air_desc', ['name' => e($item->display_name ?? '')]) !!}</p>
                    @include('main.snippets.pageFragment', [
                        'fragmentKind' => 'tour-location',
                        'url' => $fragmentUrls['air'] ?? '',
                        'seoId' => $item->seo_id,
                        'locale' => $locale ?? app()->getLocale(),
                        'section' => 'air',
                        'minHeight' => 320,
                        'skeleton' => 'tourGrid',
                        'ariaLabel' => t('tour_air_title', ['name' => $item->display_name ?? '']),
                    ])
                </div>
            </div>
        @endif

        <!-- Vé tàu cao tốc -->
        @if(!empty($item->shipLocations[0]->infoShipLocation))
            <div class="sectionBox">
                <div class="container">
                    @if(!empty($item->shipLocations[0]->infoShipLocation->seo->slug_full))
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_ship'),
                            'title' => t('tour_ship_title', ['name' => $item->display_name ?? '']),
                            'titleUrl' => '/'.$item->shipLocations[0]->infoShipLocation->seo->slug_full,
                            'titleAttr' => t('tour_ship_title', ['name' => $item->display_name ?? '']),
                        ])
                    @else
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_ship'),
                            'title' => t('tour_ship_title', ['name' => $item->display_name ?? '']),
                        ])
                    @endif
                    <p class="sectionBox_desc">{!! t('tour_ship_desc', ['name' => e($item->display_name ?? ''), 'year' => date('Y', time())]) !!}</p>
                    @include('main.snippets.pageFragment', [
                        'fragmentKind' => 'tour-location',
                        'url' => $fragmentUrls['ship'] ?? '',
                        'seoId' => $item->seo_id,
                        'locale' => $locale ?? app()->getLocale(),
                        'section' => 'ship',
                        'minHeight' => 360,
                        'skeleton' => 'shipGrid',
                        'ariaLabel' => t('tour_ship_title', ['name' => $item->display_name ?? '']),
                    ])
                </div>
            </div>
        @endif

        <!-- Vé vui chơi & giải trí -->
        @if(!empty($item->serviceLocations) && $item->serviceLocations->isNotEmpty())
            <div class="sectionBox">
                <div class="container">
                    @if(!empty($item->serviceLocations[0]->infoServiceLocation->seo->slug_full))
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_entertainment'),
                            'title' => t('tour_service_title', ['name' => $item->display_name ?? '']),
                            'titleUrl' => '/'.$item->serviceLocations[0]->infoServiceLocation->seo->slug_full,
                            'titleAttr' => t('tour_service_title', ['name' => $item->display_name ?? '']),
                        ])
                    @else
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_entertainment'),
                            'title' => t('tour_service_title', ['name' => $item->display_name ?? '']),
                        ])
                    @endif
                    <p class="sectionBox_desc">{!! t('tour_service_desc', ['name' => e($item->display_name ?? '')]) !!}</p>
                    @include('main.snippets.pageFragment', [
                        'fragmentKind' => 'tour-location',
                        'url' => $fragmentUrls['service'] ?? '',
                        'seoId' => $item->seo_id,
                        'locale' => $locale ?? app()->getLocale(),
                        'section' => 'service',
                        'minHeight' => 380,
                        'skeleton' => 'tourGrid',
                        'ariaLabel' => t('tour_service_title', ['name' => $item->display_name ?? '']),
                    ])
                </div>
            </div>
        @endif

        <!-- Cẩm nang du lịch -->
        @if(!empty($item->guides[0]->infoGuide))
            @php
                /* Lấy tối đa 3 ảnh từ guides đầu tiên cho hero collage. Fallback
                 * sang ảnh location hoặc default nếu guide chưa có ảnh. */
                $collageImages = [];
                foreach($item->guides as $guide){
                    $img = $guide->infoGuide->seo->image_small ?? $guide->infoGuide->seo->image ?? null;
                    if(!empty($img) && !in_array($img, $collageImages)) $collageImages[] = $img;
                    if(count($collageImages) >= 3) break;
                }
                /* Pad ảnh thiếu bằng ảnh seo hiện tại / default — collage luôn 3 cards */
                $fallbackImg = $item->seo->image ?? config('admin.images.default_750x460');
                while(count($collageImages) < 3) $collageImages[] = $fallbackImg;

                $tgSectionId = 'travel-guide-heading-'.uniqid();
                $tgLocName   = $item->display_name ?? null;
            @endphp
            <section class="sectionBox withBorder travelGuide" aria-labelledby="{{ $tgSectionId }}">
                <div class="container">
                    <div class="travelGuide_inner">
                        {{-- =========================================================
                             LEFT: Polaroid collage stage.
                             3 cards stacked với góc nghiêng khác nhau + tape giấy decor,
                             compass sticker (top-right) + "TRAVEL GUIDE" stamp (bottom-left).
                             Aria-hidden vì decorative — nội dung ngữ nghĩa ở cột phải.
                        ========================================================= --}}
                        <div class="travelGuide_collage" aria-hidden="true">
                            <div class="travelGuide_collage_stage" data-tg-stage>
                                <figure class="travelGuide_collage_card travelGuide_collage_card--back">
                                    <img
                                        src="{{ config('main.svg.loading_main') }}"
                                        data-src="{{ $collageImages[0] }}"
                                        alt=""
                                        loading="lazy"
                                        decoding="async"
                                    />
                                    <span class="travelGuide_collage_tape"></span>
                                </figure>
                                <figure class="travelGuide_collage_card travelGuide_collage_card--mid">
                                    <img
                                        src="{{ config('main.svg.loading_main') }}"
                                        data-src="{{ $collageImages[1] }}"
                                        alt=""
                                        loading="lazy"
                                        decoding="async"
                                    />
                                    <span class="travelGuide_collage_tape"></span>
                                </figure>
                                <figure class="travelGuide_collage_card travelGuide_collage_card--front">
                                    <img
                                        src="{{ config('main.svg.loading_main') }}"
                                        data-src="{{ $collageImages[2] }}"
                                        alt=""
                                        loading="lazy"
                                        decoding="async"
                                    />
                                    <span class="travelGuide_collage_tape"></span>
                                </figure>

                                {{-- Compass sticker — dashed outer ring + needle filled north + light south + N label --}}
                                <span class="travelGuide_collage_compass">
                                    <svg viewBox="0 0 64 64" fill="none" aria-hidden="true" focusable="false">
                                        <circle cx="32" cy="32" r="29" stroke="currentColor" stroke-width="1.4" stroke-dasharray="2 4" opacity="0.55"/>
                                        <circle cx="32" cy="32" r="22" fill="#fff" stroke="currentColor" stroke-width="1.8"/>
                                        <line x1="32" y1="11.5" x2="32" y2="14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        <line x1="32" y1="50" x2="32" y2="52.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        <line x1="11.5" y1="32" x2="14" y2="32" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        <line x1="50" y1="32" x2="52.5" y2="32" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        <path d="M32 15.5 L36 32 L32 40 L28 32 Z" fill="currentColor"/>
                                        <path d="M32 48.5 L28 32 L32 24 L36 32 Z" fill="currentColor" opacity="0.28"/>
                                        <circle cx="32" cy="32" r="2.4" fill="#fff" stroke="currentColor" stroke-width="1.4"/>
                                    </svg>
                                </span>

                                {{-- Travel Guide stamp — circular dashed badge với 2 dòng chữ --}}
                                <span class="travelGuide_collage_stamp">
                                    <span class="travelGuide_collage_stamp_inner">
                                        <span>{{ t('travel_guide_stamp_a') }}</span>
                                        <span class="travelGuide_collage_stamp_divider"></span>
                                        <span>{{ t('travel_guide_stamp_b') }}</span>
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- =========================================================
                             RIGHT: Content column (header + desc + editorial list).
                        ========================================================= --}}
                        <div class="travelGuide_content">
                            @include('main.snippets.listingHeadRow', [
                                'kicker' => t('kicker_guide'),
                                'title' => t('tour_guide_section_title', ['name' => $tgLocName ?? '']),
                                'tag' => 'h2',
                                'id' => $tgSectionId,
                                'withSectionTitleClass' => false,
                            ])
                            <p class="travelGuide_desc">{!! t('tour_guide_desc', ['name' => e($tgLocName ?? ''), 'brand' => e(config('main.name'))]) !!}</p>
                            <ul class="travelGuide_list" role="list">
                                @foreach($item->guides as $idx => $guide)
                                    <li class="travelGuide_list_item" style="--cg-delay: {{ $idx * 70 }}ms">
                                        <a
                                            href="/{{ $guide->infoGuide->seo->slug_full }}"
                                            class="travelGuide_list_link"
                                            title="{{ $guide->infoGuide->name }}"
                                        >
                                            <span class="travelGuide_list_index">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="travelGuide_list_meta">
                                                <span class="travelGuide_list_eyebrow">{{ t('tour_guide_eyebrow') }}</span>
                                                <span class="travelGuide_list_title">{{ $guide->infoGuide->name }}</span>
                                            </span>
                                            <span class="travelGuide_list_arrow" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                                    <path d="M5 12h14M13 6l6 6-6 6"/>
                                                </svg>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            @once
            @push('scripts-custom')
            <script type="text/javascript">
                (function(){
                    'use strict';
                    if (window.__travelGuideInit) return;
                    window.__travelGuideInit = true;

                    function ready(fn){
                        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
                        else fn();
                    }

                    ready(function(){
                        var sections = document.querySelectorAll('.travelGuide');
                        if (!sections.length) return;
                        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                        sections.forEach(function(section){
                            /* === Reveal cascade khi section vào viewport === */
                            if (!reduce && 'IntersectionObserver' in window) {
                                var io = new IntersectionObserver(function(entries){
                                    entries.forEach(function(entry){
                                        if (entry.isIntersecting) {
                                            entry.target.classList.add('is-revealed');
                                            io.unobserve(entry.target);
                                        }
                                    });
                                }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' });
                                io.observe(section);
                            } else {
                                section.classList.add('is-revealed');
                            }

                            /* === Mouse parallax tilt — chỉ desktop, KHÔNG khi reduced-motion === */
                            if (reduce) return;
                            var stage = section.querySelector('[data-tg-stage]');
                            var inner = section.querySelector('.travelGuide_inner');
                            if (!stage || !inner) return;
                            if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;

                            var rafId = null, lastDx = 0, lastDy = 0;
                            function applyParallax(){
                                rafId = null;
                                stage.style.setProperty('--pxr', lastDx.toFixed(3));
                                stage.style.setProperty('--pyr', lastDy.toFixed(3));
                            }
                            function onMove(e){
                                var rect = stage.getBoundingClientRect();
                                var cx = rect.left + rect.width / 2;
                                var cy = rect.top + rect.height / 2;
                                lastDx = Math.max(-0.5, Math.min(0.5, (e.clientX - cx) / rect.width));
                                lastDy = Math.max(-0.5, Math.min(0.5, (e.clientY - cy) / rect.height));
                                if (rafId == null) rafId = requestAnimationFrame(applyParallax);
                            }
                            function onLeave(){
                                lastDx = 0; lastDy = 0;
                                if (rafId == null) rafId = requestAnimationFrame(applyParallax);
                            }
                            inner.addEventListener('mousemove', onMove, { passive: true });
                            inner.addEventListener('mouseleave', onLeave, { passive: true });
                        });
                    });
                })();
            </script>
            @endpush
            @endonce
        @endif

        <!-- Điểm đến -->
        @if(!empty($destinationList[0]))
            <section class="tourExploreSection tourExploreSection--destinations" aria-labelledby="tour-explore-dest-heading">
                <div class="container">
                    <header class="tourExploreSection_head">
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_explore'),
                            'title' => t('tour_destination_title', ['name' => $item->display_name ?? '']),
                            'tag' => 'h2',
                            'id' => 'tour-explore-dest-heading',
                            'withSectionTitleClass' => false,
                        ])
                        <p class="tourExploreSection_desc">{!! t('tour_destination_desc', ['name' => e($item->display_name ?? '')]) !!}</p>
                    </header>
                    <div class="tourExploreSection_body">
                        @include('main.tourLocation.tourExploreGrid', [
                            'list'          => $destinationList,
                            'kind'          => 'destination',
                            'collapsible'   => true,
                            'countNoun'     => t('noun_destination'),
                        ])
                    </div>
                </div>
            </section>
        @endif
        <!-- Đặc sản -->
        @if(!empty($specialList[0]))
            <section class="tourExploreSection tourExploreSection--specialties" aria-labelledby="tour-explore-spec-heading">
                <div class="container">
                    <header class="tourExploreSection_head">
                        @include('main.snippets.listingHeadRow', [
                            'kicker' => t('kicker_food_gift'),
                            'title' => t('tour_special_title', ['name' => $item->display_name ?? '']),
                            'tag' => 'h2',
                            'id' => 'tour-explore-spec-heading',
                            'withSectionTitleClass' => false,
                        ])
                        <p class="tourExploreSection_desc">{!! t('tour_special_desc', ['name' => e($item->display_name ?? '')]) !!}</p>
                    </header>
                    <div class="tourExploreSection_body">
                        @include('main.tourLocation.tourExploreGrid', [
                            'list'          => $specialList,
                            'kind'          => 'specialty',
                            'collapsible'   => true,
                            'countNoun'     => t('noun_specialty'),
                        ])
                    </div>
                </div>
            </section>
        @endif

        <!-- faq -->
        @if(!empty($item->questions)&&$item->questions->isNotEmpty())
            <div class="sectionBox withBorder">
                <div class="container">
                    @include('main.snippets.faq', [
                        'list'          => $item->questions,
                        'title'         => $item->name,
                        'faqSubject'    => t('book_tour').' '.$item->display_name,
                        'kicker'        => t('kicker_support'),
                        'faqLead'       => t('tour_faq_lead', ['name' => $item->display_name ?? '', 'brand' => config('main.name')]),
                    ])
                </div>
            </div>
        @endif
    </div><!-- /.pageContent -->
</div><!-- /.pageListing -->

@endsection
@include('main.snippets.pageFragmentsScript')
@push('scripts-custom')
    <script type="text/javascript">
        $(window).on('load', function () {
            setSlick();
        });
        $(window).resize(function(){
            setSlick();
        })

        function showHideFullContent(elementButton, classCheck){
            const contentBox = $('#js_showHideFullContent_content');
            var $btn = $(elementButton);
            if (!$btn.hasClass('viewMorePill_btn')) {
                $btn = $btn.closest('.viewMorePill').find('.viewMorePill_btn').first();
            }
            var usePill = $btn.length && $btn.hasClass('viewMorePill_btn');

            if(contentBox.hasClass(classCheck)){
                contentBox.removeClass(classCheck);
                if (usePill) {
                    $btn.find('.viewMorePill_btn_icon').html('<i class="fa-solid fa-arrow-up-long" aria-hidden="true"></i>');
                    $btn.find('.viewMorePill_btn_label').text('{{ t('collapse') }}');
                } else {
                    $(elementButton).html('<i class="fa-solid fa-arrow-up-long"></i>{{ t('collapse') }}');
                }
            }else {
                contentBox.addClass(classCheck);
                if (usePill) {
                    $btn.find('.viewMorePill_btn_icon').html('<i class="fa-solid fa-arrow-down-long" aria-hidden="true"></i>');
                    $btn.find('.viewMorePill_btn_label').text('{{ t('read_more') }}');
                } else {
                    $(elementButton).html('<i class="fa-solid fa-arrow-down-long"></i>{{ t('read_more') }}');
                }
            }
        }

        function setSlick(){
            $('.slickBox').slick({
                infinite: false,
                slidesToShow: 3.01,
                slidesToScroll: 3,
                arrows: true,
                prevArrow: '<button class="slick-arrow slick-prev" aria-label="{{ t('previous_slide') }}"><i class="fa-solid fa-angle-left"></i></button>',
                nextArrow: '<button class="slick-arrow slick-next" aria-label="{{ t('next_slide') }}"><i class="fa-solid fa-angle-right"></i></button>',
                responsive: [
                    {
                        breakpoint: 1023,
                        settings: {
                            infinite: false,
                            slidesToShow: 2.6,
                            slidesToScroll: 2,
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 767,
                        settings: {
                            infinite: false,
                            slidesToShow: 1.7,
                            slidesToScroll: 1,
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 577,
                        settings: {
                            infinite: false,
                            slidesToShow: 1.3,
                            slidesToScroll: 1,
                            arrows: false,
                        }
                    }
                ]
            });
        }
    </script>
@endpush