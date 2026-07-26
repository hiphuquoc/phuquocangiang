@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $locationName = $item->display_name ?? $item->name ?? '';
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.product', [
    'data' => $dataSchema,
    'files' => $item->files,
    'lowPrice' => $schemaOffer['low'] ?? 3000000,
    'highPrice' => $schemaOffer['high'] ?? 5000000,
    'priceCurrency' => $schemaOffer['currency'] ?? schema_currency(),
])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.faq', ['data' => $item->questions])
@php
  $dataList = !empty($item->services) && $item->services->isNotEmpty() ? $item->services : null;
@endphp
@include('main.schema.itemlist', ['data' => $dataList])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'service',
])

<main id="main" class="sd-listing-page">
  <section class="sd-section sd-section--listing-primary" id="services" aria-labelledby="service-list-title">
    <div class="sd-section__inner">
      @php $servicesHead = $page['services']['head'] ?? []; @endphp
      @include('superdong.ui.section-head', [
        'eyebrow' => $servicesHead['eyebrow'] ?? '',
        'title' => $servicesHead['title'] ?? '',
        'desc' => $servicesHead['desc'] ?? '',
        'titleId' => 'service-list-title',
        'titleTag' => 'h2',
        'reveal' => false,
        'compact' => true,
      ])

      @include('main.serviceLocation-v2.sections.service-filter')

      <div class="sd-listing-services" data-service-list>
        @include('main.serviceLocation.fragments.services-v2', [
          'items' => $page['services']['items'] ?? [],
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
    'titleId' => 'service-location-content-title',
    'tocSub' => t('kicker_entertainment'),
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootServiceLocationPage() {
    if (typeof window.initServiceLocationFilter === 'function') {
      window.initServiceLocationFilter(document);
    }
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootServiceLocationPage);
  } else {
    bootServiceLocationPage();
  }
})();
</script>
@endpush
@endsection
