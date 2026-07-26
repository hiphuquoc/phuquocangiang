@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $article = $page['article'] ?? [];
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

<main id="main" class="sd-listing-page sd-blog-detail-page">
  <section class="sd-section sd-section--listing-primary" aria-labelledby="blog-article-title">
    <div class="sd-section__inner sd-blog-layout sd-blog-layout--category sd-blog-layout--detail">
      <div class="sd-blog-layout__main">
        @include('main.blog-v2.fragments.article-masthead', [
          'article' => $article,
          'titleId' => 'blog-article-title',
        ])

        @include('main.blog-v2.sections.article-toc', [
          'tocSub' => $article['eyebrow'] ?? 'Blog',
        ])

        @include('main.blog-v2.sections.article-body', [
          'content' => $content ?? '',
        ])

        @include('main.blog-v2.sections.related', [
          'related' => $page['related'] ?? [],
        ])
      </div>

      @include('main.blog-v2.sections.sidebar', [
        'sidebar' => $page['sidebar'] ?? [],
      ])
    </div>
  </section>
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')

@push('scripts-custom')
<script type="text/javascript">
(function () {
  function bootBlogPage() {
    if (typeof window.initBlogArticleToc === 'function') {
      window.initBlogArticleToc(document);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootBlogPage);
  } else {
    bootBlogPage();
  }
})();
</script>
@endpush
@endsection
