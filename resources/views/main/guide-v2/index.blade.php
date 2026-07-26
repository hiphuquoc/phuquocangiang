@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $guideName = $item->display_name ?? $item->name ?? '';
  $articleHead = $page['article'] ?? [];
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'tour',
])

<main id="main" class="sd-listing-page sd-guide-page">
  @include('main.listing-v2.sections.content-prose', [
    'content' => $content ?? '',
    'eyebrow' => $articleHead['eyebrow'] ?? t('kicker_guide'),
    'title' => $articleHead['title'] ?? $guideName,
    'lede' => $articleHead['lede'] ?? '',
    'titleId' => 'guide-article-title',
    'titleTag' => 'h2',
    'tocSub' => t('kicker_guide'),
    'sectionClass' => 'sd-listing-content sd-guide-article',
  ])

  @include('main.tourLocation-v2.sections.related-services', [
    'relatedServices' => $page['relatedServices'] ?? ['head' => [], 'items' => []],
    'locationName' => $guideName !== '' ? $guideName : island_name(),
  ])
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootGuidePage() {
    if (typeof window.initContentTocSidebar === 'function') {
      window.initContentTocSidebar(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootGuidePage);
  } else {
    bootGuidePage();
  }
})();
</script>
@endpush
@endsection
