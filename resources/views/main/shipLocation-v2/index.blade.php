@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
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
@php $dataList = !empty($item->ships) && $item->ships->isNotEmpty() ? $item->ships : null; @endphp
@include('main.schema.itemlist', ['data' => $dataList])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'ship',
])

<main id="main" class="sd-listing-page">
  @php $routesHead = $page['routes']['head'] ?? []; @endphp
  <section class="sd-section sd-section--listing-primary" id="ferry" aria-labelledby="ship-routes-title">
    <div class="sd-section__inner">
      @include('superdong.ui.section-head', [
        'eyebrow' => $routesHead['eyebrow'] ?? '',
        'title' => $routesHead['title'] ?? '',
        'desc' => $routesHead['desc'] ?? '',
        'titleId' => 'ship-routes-title',
        'titleTag' => 'h2',
        'reveal' => false,
        'compact' => true,
      ])

      @include('superdong.sections.listing.ferry-routes', [
        'routes' => $page['routes']['items'] ?? [],
      ])
    </div>
  </section>

  @include('main.tourLocation-v2.sections.related-services', [
    'relatedServices' => $page['relatedServices'] ?? ['head' => [], 'items' => []],
    'locationName' => $item->display_name ?? island_name(),
  ])

  @php
    $scheduleHtml = view('main.shipLocation.schedule', [
      'item' => $item,
      'keyWord' => $item->name,
      'schedule' => $schedule ?? null,
    ])->render();

    $faqHtml = '';
    if (!empty($item->questions) && $item->questions->isNotEmpty()) {
      $faqHtml = '<div id="cau-hoi-thuong-gap"><h2>' . e(t('ship_faq_about', ['name' => $item->name ?? ''])) . '</h2>';
      $faqHtml .= view('main.snippets.faq', ['list' => $item->questions, 'title' => $item->name, 'hiddenTitle' => true])->render();
      $faqHtml .= '</div>';
    }
  @endphp

  @include('main.listing-v2.sections.content-prose', [
    'content' => $content ?? '',
    'articleTop' => $scheduleHtml,
    'articleBottom' => $faqHtml,
    'eyebrow' => t('tour_guide_intro_kicker'),
    'title' => t('ship_schedule_title', ['name' => $item->display_name ?? $item->name ?? '']),
    'lede' => t('tour_guide_intro_lede'),
    'titleId' => 'ship-location-content-title',
    'tocSub' => t('kicker_ship'),
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootShipLocationPage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootShipLocationPage);
  } else {
    bootShipLocationPage();
  }
})();
</script>
@endpush
@endsection
