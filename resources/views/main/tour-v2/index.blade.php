@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['intro']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $tourName = $page['intro']['title'] ?? ($item->name ?? '');
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.product', [
    'data' => $dataSchema,
    'files' => $item->files ?? null,
    'lowPrice' => $schemaOffer['low'] ?? 500000,
    'highPrice' => $schemaOffer['high'] ?? 5000000,
    'priceCurrency' => $schemaOffer['currency'] ?? schema_currency(),
])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.faq', ['data' => $item->questions ?? null])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'tour',
])

<main id="main" class="sd-hotel-detail-page sd-product-detail-page">
  @include('main.product-v2.sections.intro', [
    'intro' => $page['intro'] ?? [],
    'gallery' => $page['gallery'] ?? [],
    'galleryId' => 'tour-intro',
  ])

  @include('main.product-v2.sections.options', [
    'options' => $page['options'] ?? [],
    'sectionId' => 'tour-options',
  ])

  @include('main.tour-v2.sections.details', ['item' => $item])

  @if(!empty($content))
    @include('main.listing-v2.sections.content-prose', [
      'content' => $content,
      'eyebrow' => t('kicker_book_tour'),
      'title' => t('tour_guide_intro_title', ['name' => $tourName]),
      'lede' => t('tour_guide_intro_lede'),
      'titleId' => 'tour-detail-content-title',
      'tocSub' => t('kicker_book_tour'),
    ])
  @endif

  @include('main.hotel-v2.sections.faq', [
    'faq' => $page['faq'] ?? ['active' => false, 'items' => []],
  ])

  @include('main.tour-v2.sections.related', [
    'related' => $page['related'] ?? [],
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@include('main.product-v2.sections.sticky-book', [
  'bookingHref' => $page['bookingHref'] ?? '#',
  'ctaLabel' => t('book_this_tour') ?? t('book_tour'),
  'priceFormatted' => $page['intro']['priceFormatted'] ?? null,
])

@if(!empty($page['gallery']))
  @include('main.hotel-v2.fragments.gallery-lightbox', [
    'gallery' => $page['gallery'],
    'hotelName' => $tourName,
  ])
@endif

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootTourDetailPage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
    if (typeof window.initHotelDetailGallery === 'function') {
      window.initHotelDetailGallery(document);
    }
    document.querySelectorAll('[data-tour-itinerary-tabs]').forEach(function (tabs) {
      var root = tabs.closest('.sd-product-details__block');
      if (!root) return;
      tabs.querySelectorAll('button[data-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          tabs.querySelectorAll('button').forEach(function (b) { b.classList.remove('is-active'); });
          btn.classList.add('is-active');
          root.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-tab-panel') !== btn.getAttribute('data-tab');
          });
        });
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootTourDetailPage);
  } else {
    bootTourDetailPage();
  }
})();
</script>
@endpush
@endsection
