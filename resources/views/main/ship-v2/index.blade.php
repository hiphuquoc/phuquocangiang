@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['intro']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $shipName = $item->name ?? '';
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.faq', ['data' => $item->questions ?? null])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'ship',
])

<main id="main" class="sd-hotel-detail-page sd-product-detail-page">
  @include('main.product-v2.sections.intro', [
    'intro' => $page['intro'] ?? [],
    'gallery' => $page['gallery'] ?? [],
    'galleryId' => 'ship-intro',
  ])

  @if(!empty($schedule))
    @include('main.listing-v2.sections.content-prose', [
      'content' => '',
      'articleTop' => $schedule,
      'eyebrow' => t('kicker_ship'),
      'title' => t('ship_schedule_title', ['name' => $shipName]),
      'lede' => '',
      'titleId' => 'ship-schedule-title',
      'tocSub' => t('kicker_ship'),
      'sectionClass' => 'sd-listing-content sd-ship-schedule',
    ])
  @endif

  @if(!empty($page['partners']))
    <section class="sd-section sd-ship-partners" aria-label="{{ t('kicker_ship') }}">
      <div class="sd-section__inner">
        <div class="sd-ship-partners__list">
          @foreach($page['partners'] as $partner)
            <div class="sd-ship-partners__item">
              @if(!empty($partner['logo']))
                <img src="{{ $partner['logo'] }}" alt="{{ $partner['name'] }}" width="120" height="48" loading="lazy">
              @endif
              <span>{{ $partner['name'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if(!empty($content))
    @include('main.listing-v2.sections.content-prose', [
      'content' => $content,
      'eyebrow' => t('kicker_ship'),
      'title' => t('ship_detail_title', ['name' => $shipName]),
      'lede' => '',
      'titleId' => 'ship-detail-content-title',
      'tocSub' => t('kicker_ship'),
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
  'ctaLabel' => t('ship_book_mobile'),
])

@if(!empty($page['gallery']))
  @include('main.hotel-v2.fragments.gallery-lightbox', [
    'gallery' => $page['gallery'],
    'hotelName' => $shipName,
  ])
@endif

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootShipDetailPage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
    if (typeof window.initHotelDetailGallery === 'function') {
      window.initHotelDetailGallery(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootShipDetailPage);
  } else {
    bootShipDetailPage();
  }
})();
</script>
@endpush
@endsection
