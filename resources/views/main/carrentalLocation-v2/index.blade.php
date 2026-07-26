@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $locationName = $item->location_name ?? $item->name ?? island_name();
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.product', [
    'data' => $dataSchema,
    'files' => $item->files ?? null,
    'lowPrice' => 300000,
    'highPrice' => 5000000,
    'priceCurrency' => schema_currency(),
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

<main id="main" class="sd-listing-page">
  @include('superdong.sections.vehicle-rental')

  @include('main.tourLocation-v2.sections.related-services', [
    'relatedServices' => $page['relatedServices'] ?? ['head' => [], 'items' => []],
    'locationName' => $locationName,
  ])

  @php
    $faqHtml = '';
    if (!empty($item->questions) && $item->questions->isNotEmpty()) {
      $faqHtml = '<div id="cau-hoi-thuong-gap"><h2>' . e(t('tour_faq_about', ['name' => $item->name ?? ''])) . '</h2>';
      $faqHtml .= view('main.snippets.faq', ['list' => $item->questions, 'title' => $item->name, 'hiddenTitle' => true])->render();
      $faqHtml .= '</div>';
    }
  @endphp

  @include('main.listing-v2.sections.content-prose', [
    'content' => $content ?? '',
    'articleBottom' => $faqHtml,
    'eyebrow' => t('kicker_transport'),
    'title' => t('tour_carrental_title', ['name' => $locationName]),
    'lede' => t('tour_guide_intro_lede'),
    'titleId' => 'carrental-location-content-title',
    'tocSub' => t('kicker_transport'),
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootCarrentalLocationPage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootCarrentalLocationPage);
  } else {
    bootCarrentalLocationPage();
  }
})();
</script>
@endpush
@endsection
