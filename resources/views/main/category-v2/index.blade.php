@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $categoryName = $item->name ?? '';
  $posts = $page['posts'] ?? [];
  $postItems = $posts['items'] ?? [];
  $postCount = (int) ($posts['count'] ?? count($postItems));
  $categoryDesc = strip_tags((string) ($item->description ?? ''));
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.itemlist', ['data' => $blogs ?? null])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'tour',
])

<main id="main" class="sd-listing-page sd-blog-category-page">
  <section class="sd-section sd-section--listing-primary" aria-labelledby="blog-category-title">
    <div class="sd-section__inner sd-blog-layout sd-blog-layout--category">
      <div class="sd-blog-layout__main">
        @include('main.category-v2.fragments.masthead', [
          'title' => $categoryName,
          'desc' => $categoryDesc,
          'count' => $postCount,
          'eyebrow' => $posts['head']['eyebrow'] ?? 'Blog & tin tức',
          'titleId' => 'blog-category-title',
        ])

        @include('main.category-v2.fragments.blog-list', [
          'items' => $postItems,
          'showFeatured' => true,
        ])
      </div>

      @include('main.blog-v2.sections.sidebar', [
        'sidebar' => $page['sidebar'] ?? [],
        'postCount' => $postCount,
      ])
    </div>
  </section>
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')
@endsection
