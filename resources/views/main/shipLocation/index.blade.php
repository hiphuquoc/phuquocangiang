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

<!-- STRAT:: Product Schema -->
@php
    $arrayPrice = [];
    if(!empty($item->ships)&&$item->ships->isNotEmpty()){
        foreach($item->ships as $ship){
            if(!empty($ship->prices)&&$ship->prices->isNotEmpty()){
                foreach($ship->prices as $price){
                    if(!empty($price->price_adult)) $arrayPrice[] = $price->price_adult;
                    if(!empty($price->price_child)) $arrayPrice[] = $price->price_child;
                    if(!empty($price->price_old)) $arrayPrice[] = $price->price_old;
                    if(!empty($price->price_vip)) $arrayPrice[] = $price->price_vip;
                }
            }
        }
    }
    $highPrice  = !empty($arrayPrice) ? max($arrayPrice) : 5000000;
    $lowPrice   = !empty($arrayPrice) ? min($arrayPrice) : 3000000;
@endphp
@include('main.schema.product', ['data' => $dataSchema, 'files' => $item->files, 'lowPrice' => $lowPrice, 'highPrice' => $highPrice])
<!-- END:: Product Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
<!-- END:: Article Schema -->

<!-- STRAT:: FAQ Schema -->
@include('main.schema.faq', ['data' => $item->questions])
<!-- END:: FAQ Schema -->

@php
    $dataList       = null;
    if(!empty($item->ships)&&$item->ships->isNotEmpty()) $dataList = $item->ships;
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
        'active'    => 'ship'
    ])

    @include('main.snippets.breadcrumb')

    <div class="pageContent">

        <div class="sectionBox backgroundPrimaryGradiend">
            <div class="container">
                <!-- title -->
                <h1 class="titlePage">{{ $item->name }}{{ !empty($item->district->district_name) ? ' - '.t('ship_location_suffix', ['district' => $item->district->district_name]) : null}}</h1>
                <!-- content -->
                @if(!empty($item->description))
                    <div class="contentBox">
                        <p>{!! $item->description !!}</p>
                    </div>
                @endif
                <!-- ship box -->
                @include('main.snippets.pageFragment', [
                    'fragmentKind' => 'ship-location',
                    'url' => $fragmentUrls['ships'] ?? '',
                    'seoId' => $item->seo_id,
                    'locale' => $locale ?? app()->getLocale(),
                    'section' => 'ships',
                    'minHeight' => 400,
                    'skeleton' => 'shipGrid',
                    'ariaLabel' => $item->name ?? '',
                ])
            </div>
        </div>

        <!-- Hướng dẫn đặt Vé -->
        @include('main.shipLocation.guideBook', ['title' => t('ship_guide_book_title', ['name' => $item->display_name])])

        <!-- START:: Video -->
        @include('main.tourLocation.videoBox', [
            'item'  => $item,
            'title' => t('ship_video_title', ['name' => $item->display_name])
        ])
        <!-- END:: Video -->
        
        <div class="sectionBox noBackground">
            <div class="container">
                <div class="pageContent_body">
                    <div class="pageContent_body_content">
                        <div id="js_buildTocContentSidebar_element" class="contentShip">
                            <!-- tocContent main -->
                            <div id="tocContentMain"></div>
                            <!-- Lịch tàu -->
                            @include('main.shipLocation.schedule', [
                                'item'      => $item,
                                'keyWord'   => $item->name,
                                'schedule'  => $schedule ?? null
                            ])
                            <!-- Hãng tàu -->
                            @include('main.shipLocation.brand', [
                                'item'  => $item,
                                'keyWord' => $item->name
                            ])
                            <!-- Nội dung tùy biến -->
                            {!! $content ?? null !!}
                            <!-- Câu hỏi thường gặp -->
                            @if(!empty($item->questions)&&$item->questions->isNotEmpty())
                                <div id="cau-hoi-thuong-gap" class="contentShip_item">
                                    <div class="contentShip_item_title">
                                        <i class="fa-solid fa-circle-question"></i>
                                        <h2>{{ t('ship_faq_about', ['name' => $item->name ?? '']) }}</h2>
                                    </div>
                                    <div class="contentShip_item_text">
                                        @include('main.snippets.faq', [
                                            'list' => $item->questions, 
                                            'title' => $item->name,
                                            'hiddenTitle'   => true
                                        ])
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="pageContent_body_sidebar">
                        @include('main.shipLocation.sidebar')
                    </div>
                </div>
            </div>
        </div>

        <!-- Chuyên mục blogs -->
        @if(!empty($item->categories))
            @foreach($item->categories as $category)
                <div class="sectionBox {{ $loop->index==0 ? 'backgroundSecondary' : null }}">
                    <div class="container">
                        <h2 class="sectionBox_title">{{ $category->infoCategory->name ?? null }}</h2>
                        <p class="sectionBox_desc">{{ t('ship_blog_desc', ['name' => $item->name ?? '']) }}</p>
                        @include('main.tourLocation.blogGridSlick', [
                            'list' => $category->blogs, 
                            'link' => $item->infoCategory->seo->slug_full ?? null, 
                            'limit' => 10])
                    </div>
                </div>
            @endforeach
        @endif
        
    </div><!-- /.pageContent -->
</div><!-- /.pageListing -->

@endsection
@include('main.snippets.pageFragmentsScript')
@push('bottom')
    @php
        $linkFull = booking_route('shipBooking.form', [
            'ship_port_departure_id'    => $item->ships[0]->ship_port_departure_id ?? 0,
            'ship_port_location_id'     => $item->ships[0]->ship_port_location_id ?? 0
        ]);
    @endphp
    <!-- button book vé mobile -->
    <div class="show-990">
        <div class="callBookTourMobile">
            <a href="tel:0868684868" class="callBookTourMobile_phone maxLine_1">{{ config('company.hotline') }}</a>
            <a href="{{ $linkFull ?? '/' }}" class="callBookTourMobile_button"><h2 style="margin:0;">{{ t('ship_book_mobile') }}</h2></a>
        </div>
    </div>
@endpush
@push('scripts-custom')
    <script type="text/javascript">
        buildTocContentMain('js_buildTocContentSidebar_element');
    </script>
@endpush