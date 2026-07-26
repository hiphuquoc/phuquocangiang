@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
<!-- ===== SCHEMA ===== -->
@php $dataSchema = $item->seo ?? null; @endphp
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
    $dataList = new \Illuminate\Support\Collection();
    if (!empty($item->tours) && $item->tours->isNotEmpty()) {
        foreach ($item->tours as $tour) {
            $dataList[] = $tour->infoTour;
        }
    }
@endphp
@include('main.schema.itemlist', ['data' => $dataList])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', ['banner' => $page['banner'] ?? []])

<main id="main" class="sd-listing-page sd-tour-location-page">
  <section class="sd-section sd-section--tours-listing" id="tours" aria-labelledby="tour-list-title">
    <div class="sd-section__inner">
      @php
        $toursHead = $page['tours']['head'] ?? [];
        $toursHead['titleId'] = 'tour-list-title';
      @endphp
      @include('superdong.ui.section-head', [
        'eyebrow' => $toursHead['eyebrow'] ?? '',
        'title' => $toursHead['title'] ?? '',
        'desc' => $toursHead['desc'] ?? '',
        'linkHref' => null,
        'titleId' => 'tour-list-title',
        'titleTag' => 'h2',
        'reveal' => false,
        'compact' => true,
      ])

      @include('main.tourLocation-v2.sections.tour-filter')

      <div class="sd-tour-location-tours" data-tour-list>
        @include('main.tourLocation.fragments.tours-v2', [
          'item' => $item,
          'items' => $page['tours']['items'] ?? [],
        ])
      </div>
    </div>
  </section>

  @include('main.tourLocation-v2.sections.related-services', [
    'relatedServices' => $page['relatedServices'] ?? ['head' => [], 'items' => []],
    'locationName' => $item->display_name ?? island_name(),
  ])

  @include('main.tourLocation-v2.sections.content', [
    'content' => $content,
    'locationName' => $item->display_name ?? island_name(),
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootTourLocationPage() {
    if (typeof window.initTourLocationFilter === 'function') {
      window.initTourLocationFilter(document);
    }
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootTourLocationPage);
  } else {
    bootTourLocationPage();
  }
})();
</script>
@endpush
@endsection
