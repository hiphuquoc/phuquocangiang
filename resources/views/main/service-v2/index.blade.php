@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['intro']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $serviceName = $page['intro']['title'] ?? ($item->name ?? '');
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.product', [
    'data' => $dataSchema,
    'files' => $item->files ?? null,
    'lowPrice' => $schemaOffer['low'] ?? 200000,
    'highPrice' => $schemaOffer['high'] ?? 1000000,
    'priceCurrency' => $schemaOffer['currency'] ?? schema_currency(),
])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.faq', ['data' => $item->questions ?? null])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'service',
])

<main id="main" class="sd-hotel-detail-page sd-product-detail-page">
  @include('main.product-v2.sections.intro', [
    'intro' => $page['intro'] ?? [],
    'gallery' => $page['gallery'] ?? [],
    'galleryId' => 'service-intro',
  ])

  @include('main.product-v2.sections.options', [
    'options' => $page['options'] ?? [],
    'sectionId' => 'service-options',
  ])

  @if(!empty($content))
    @include('main.listing-v2.sections.content-prose', [
      'content' => $content,
      'eyebrow' => t('kicker_entertainment'),
      'title' => $serviceName,
      'lede' => '',
      'titleId' => 'service-detail-content-title',
      'tocSub' => t('kicker_entertainment'),
    ])
  @endif

  @include('main.hotel-v2.sections.faq', [
    'faq' => $page['faq'] ?? ['active' => false, 'items' => []],
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@include('main.product-v2.sections.sticky-book', [
  'bookingHref' => $page['bookingHref'] ?? '#',
  'ctaLabel' => t('service_book_this_ticket') ?? t('ship_book_mobile'),
  'priceFormatted' => $page['intro']['priceFormatted'] ?? null,
])

@if(!empty($page['gallery']))
  @include('main.hotel-v2.fragments.gallery-lightbox', [
    'gallery' => $page['gallery'],
    'hotelName' => $serviceName,
  ])
@endif

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootServiceDetailPage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
    if (typeof window.initHotelDetailGallery === 'function') {
      window.initHotelDetailGallery(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootServiceDetailPage);
  } else {
    bootServiceDetailPage();
  }
})();
</script>
@endpush
@endsection
