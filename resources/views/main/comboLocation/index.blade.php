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
    $dataList       = null;
    if(!empty($item->services)&&$item->services->isNotEmpty()) $dataList = $item->services;
@endphp
<!-- STRAT:: Article Schema -->
@include('main.schema.itemlist', ['data' => $dataList])
<!-- END:: Article Schema -->

<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.form.sortBooking', [
        'item'      => $item,
        'active'    => 'combo'
    ])

    @include('main.snippets.breadcrumb')

    <div class="pageContent">

        <!-- Combo box -->
        <div class="sectionBox backgroundPrimaryGradiend">
            <div class="container">
                <h1 class="titlePage">{{ t('combo_location_title', ['name' => $item->display_name ?? '']) }}</h1>
                <p class="sectionBox_desc">{!! t('combo_location_desc', ['name' => $item->display_name ?? '', 'brand' => config('main.name')]) !!}</p>
                {{-- @include('main.tourLocation.filterBox') --}}
                @include('main.snippets.pageFragment', [
                    'fragmentKind' => 'combo-location',
                    'url' => $fragmentUrls['combos'] ?? '',
                    'seoId' => $item->seo_id,
                    'locale' => $locale ?? app()->getLocale(),
                    'section' => 'combos',
                    'minHeight' => 400,
                    'skeleton' => 'tourGrid',
                    'ariaLabel' => t('combo_location_title', ['name' => $item->display_name ?? '']),
                ])
            </div>
        </div>

        <!-- Giới thiệu Combo du lịch -->
        <div class="sectionBox" style="padding-top:0;">
            <div class="container">
                <!-- title -->
                <h2 class="sectionBox_title">{{ t('combo_location_intro_title', ['name' => $item->display_name ?? '']) }}</h2>
                <!-- content -->
                @if(!empty($content))
                    <div id="js_showHideFullContent_content" class="contentBox maxLine_4">
                        {!! $content !!}
                    </div>
                    <div class="viewMore">
                        <div onClick="showHideFullContent(this, 'maxLine_4');">
                            <i class="fa-solid fa-arrow-down-long"></i>{{ t('read_more') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        

        {{-- <!-- Hướng dẫn đặt Vé -->
        @include('main.comboLocation.guideBook', ['title' => 'Hướng dẫn đặt Combo '.$item->display_name]) --}}

        <!-- START:: Video -->
        @include('main.tourLocation.videoBox', [
            'item'  => $item,
            'title' => t('combo_video_title', ['name' => $item->display_name])
        ])
        <!-- END:: Video -->

        <!-- Câu hỏi thường gặp -->
        @if(!empty($item->questions)&&$item->questions->isNotEmpty())
            <div class="sectionBox withBorder">
                <div class="container" style="border-bottom:none !important;">
                    @include('main.snippets.faq', ['list' => $item->questions, 'title' => $item->name])
                </div>
            </div>
        @endif

    </div>
@endsection
@include('main.snippets.pageFragmentsScript')
@push('bottom')
    <!-- button book tour mobile -->
    @php
        $linkBooking = booking_route('serviceBooking.form', [
            'service_location_id'   => $item->id ?? 0
        ]);
    @endphp 
    <div class="show-990">
        <div class="callBookTourMobile">
            <a href="tel:{{ \App\Helpers\Charactor::removeSpecialCharacters(config('main.company.hotline')) }}" class="callBookTourMobile_phone maxLine_1">{{ config('main.company.hotline') }}</a>
            <a href="{{ $linkBooking ?? '/' }}" class="callBookTourMobile_button"><h2 style="margin:0;">{{ t('book_combo_mobile') }}</h2></a>
        </div>
    </div>
@endpush
@push('scripts-custom')
    <script type="text/javascript">
        buildTocContentMain('js_buildTocContentSidebar_element');

        function showHideFullContent(elementButton, classCheck){
            const contentBox = $('#js_showHideFullContent_content');
            if(contentBox.hasClass(classCheck)){
                contentBox.removeClass(classCheck);
                $(elementButton).html('<i class="fa-solid fa-arrow-up-long"></i>{{ t('collapse') }}');
            }else {
                contentBox.addClass(classCheck);
                $(elementButton).html('<i class="fa-solid fa-arrow-down-long"></i>{{ t('read_more') }}');
            }
        }
    </script>
@endpush