@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['intro']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $hotelName = $page['intro']['title'] ?? ($item->name ?? '');
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
@php
  $dataList = !empty($item->rooms) && $item->rooms->isNotEmpty() ? $item->rooms : null;
@endphp
@include('main.schema.itemlist', ['data' => $dataList])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'hotel',
])

<main id="main" class="sd-hotel-detail-page">
  @include('main.hotel-v2.sections.intro', [
    'intro' => $page['intro'] ?? [],
    'gallery' => $page['gallery'] ?? [],
  ])

  @if(!empty($page['facilities']['tripadvisor']) || !empty($page['facilities']['traveloka']))
    @include('main.hotel-v2.sections.facilities', [
      'facilities' => $page['facilities'] ?? [],
    ])
  @endif

  @if(!empty($page['rooms']['items']))
    @include('main.hotel-v2.sections.rooms', [
      'rooms' => $page['rooms'] ?? [],
    ])
  @endif

  @include('main.listing-v2.sections.content-prose', [
    'content' => $content ?? '',
    'eyebrow' => t('kicker_hotel'),
    'title' => t('tour_guide_intro_title', ['name' => $hotelName]),
    'lede' => t('tour_guide_intro_lede'),
    'titleId' => 'hotel-detail-content-title',
    'tocSub' => t('kicker_hotel'),
  ])

  @if(!empty($page['policy']['html']))
    @include('main.hotel-v2.sections.policy', [
      'policy' => $page['policy'],
    ])
  @endif

  @include('main.hotel-v2.sections.faq', [
    'faq' => $page['faq'] ?? ['active' => false, 'items' => []],
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@include('main.product-v2.sections.sticky-book', [
  'bookingHref' => $page['intro']['roomsAnchor'] ?? '#hotel-rooms',
  'ctaLabel' => t('hotel_choose_room'),
  'priceFormatted' => $page['intro']['priceFormatted'] ?? null,
])

@if(!empty($page['gallery']))
  @include('main.hotel-v2.fragments.gallery-lightbox', [
    'gallery' => $page['gallery'],
    'hotelName' => $hotelName,
  ])
@endif

<div class="sd-hotel-room-modal" id="hotel-room-modal" hidden aria-hidden="true">
  <div class="sd-hotel-room-modal__backdrop" data-hotel-room-close></div>
  <div class="sd-hotel-room-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="hotel-room-modal-title">
    <button type="button" class="sd-hotel-room-modal__close" data-hotel-room-close aria-label="Đóng">&times;</button>
    <div data-hotel-room-modal-body></div>
  </div>
</div>

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootHotelDetailPage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
    if (typeof window.initHotelDetailGallery === 'function') {
      window.initHotelDetailGallery(document);
    }
    if (typeof window.initHotelRoomModal === 'function') {
      window.initHotelRoomModal(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootHotelDetailPage);
  } else {
    bootHotelDetailPage();
  }
})();
</script>
@endpush
@endsection
