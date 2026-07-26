@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $locationName = $item->display_name ?? $item->name ?? island_name();
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.product', [
    'data' => $dataSchema,
    'files' => $item->files ?? null,
    'lowPrice' => $schemaOffer['low'] ?? 3000000,
    'highPrice' => $schemaOffer['high'] ?? 5000000,
    'priceCurrency' => $schemaOffer['currency'] ?? schema_currency(),
])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.faq', ['data' => $item->questions ?? null])
@php $dataList = !empty($item->hotels) && $item->hotels->isNotEmpty() ? $item->hotels : null; @endphp
@include('main.schema.itemlist', ['data' => $dataList])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'hotel',
])

<main id="main" class="sd-listing-page">
  @php $hotelsHead = $page['hotels']['head'] ?? []; @endphp
  <section class="sd-section sd-section--listing-primary" id="hotels" aria-labelledby="hotel-list-title">
    <div class="sd-section__inner">
      @include('superdong.ui.section-head', [
        'eyebrow' => $hotelsHead['eyebrow'] ?? '',
        'title' => $hotelsHead['title'] ?? '',
        'desc' => $hotelsHead['desc'] ?? '',
        'titleId' => 'hotel-list-title',
        'titleTag' => 'h2',
        'reveal' => false,
        'compact' => true,
      ])

      @include('main.hotelLocation-v2.sections.hotel-filter', [
        'filters' => $page['hotels']['filters'] ?? [],
      ])

      <div class="sd-listing-hotels" data-hotel-list>
        @include('main.hotelLocation.fragments.hotels-v2', [
          'items' => $page['hotels']['items'] ?? [],
          'locationName' => $locationName,
        ])
      </div>
    </div>
  </section>

  @include('main.tourLocation-v2.sections.related-services', [
    'relatedServices' => $page['relatedServices'] ?? ['head' => [], 'items' => []],
    'locationName' => $locationName,
  ])

  @include('main.listing-v2.sections.content-prose', [
    'content' => $content ?? '',
    'eyebrow' => t('tour_guide_intro_kicker'),
    'title' => t('tour_guide_intro_title', ['name' => $locationName]),
    'lede' => t('tour_guide_intro_lede'),
    'titleId' => 'hotel-location-content-title',
    'tocSub' => t('kicker_hotel'),
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootHotelLocationPage() {
    if (typeof window.initHotelLocationFilter === 'function') {
      window.initHotelLocationFilter(document);
    }
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootHotelLocationPage);
  } else {
    bootHotelLocationPage();
  }
})();
</script>
@endpush
@endsection
