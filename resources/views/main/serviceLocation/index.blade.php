@extends('main.layouts.main')
@push('head-custom')
<!-- ===== START:: SCHEMA ===== -->
@php
    $dataSchema = $item->seo ?? null;
    $locationName = $item->display_name ?? $item->name ?? '';
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
    $dataList = !empty($dataServicesNoscript) && $dataServicesNoscript->isNotEmpty()
        ? $dataServicesNoscript
        : null;
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
        'active'    => 'service'
    ])

    @include('main.snippets.breadcrumb')

    <div class="pageContent">

        <div class="sectionBox backgroundPrimaryGradiend">
            <div class="container">
                @include('main.snippets.listingHeadRow', [
                    'kicker' => t('kicker_entertainment'),
                    'title' => t('service_location_title', ['name' => $locationName]),
                    'tag' => 'h1',
                    'titleClass' => 'titlePage',
                    'withSectionTitleClass' => false,
                ])
                <p class="sectionBox_desc">{!! t('service_location_desc', ['name' => e($locationName), 'brand' => e(config('main.name'))]) !!}</p>
                @include('main.serviceLocation.filterBox')
                @include('main.tourLocation.loadingGridBox', ['loaderClass' => 'loadingGridBox--filter'])
                @include('main.snippets.pageFragment', [
                    'fragmentKind' => 'service-location',
                    'url' => $fragmentUrls['services'] ?? '',
                    'seoId' => $item->seo_id,
                    'locale' => $locale ?? app()->getLocale(),
                    'section' => 'services',
                    'minHeight' => 440,
                    'skeleton' => 'tourGrid',
                    'ariaLabel' => t('service_location_title', ['name' => $locationName]),
                ])
                @if(!empty($dataServicesNoscript) && $dataServicesNoscript->isNotEmpty())
                    <noscript>
                        @include('main.serviceLocation.serviceItem', [
                            'list' => $dataServicesNoscript,
                            'catalogId' => 'js_serviceFilter_parent',
                            'hiddenId' => 'js_serviceFilter_hidden',
                        ])
                    </noscript>
                @endif
            </div>
        </div>

        <!-- Hướng dẫn đặt Vé -->
        @include('main.serviceLocation.guideBook', ['title' => t('service_guide_book_title', ['name' => $item->display_name])])

        <!-- START:: Video -->
        @include('main.tourLocation.videoBox', [
            'item'  => $item,
            'title' => t('service_video_title', ['name' => $item->display_name])
        ])
        <!-- END:: Video -->

    </div><!-- /.pageContent -->
</div><!-- /.pageListing -->

@endsection
@include('main.snippets.pageFragmentsScript')
@push('bottom')
    @php
        $linkBooking = booking_route('serviceBooking.form', [
            'service_location_id'   => $item->id ?? 0
        ]);
    @endphp
    <div class="show-990">
        <div class="callBookTourMobile">
            <a href="tel:0868684868" class="callBookTourMobile_phone maxLine_1">{{ config('company.hotline') }}</a>
            <a href="{{ $linkBooking ?? '/' }}" class="callBookTourMobile_button"><h2 style="margin:0;">{{ t('ship_book_mobile') }}</h2></a>
        </div>
    </div>
@endpush
