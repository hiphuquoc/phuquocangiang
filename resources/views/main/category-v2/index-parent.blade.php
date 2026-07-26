@extends('superdong.layout.app')

@section('document-title')
{{ $item->seo->title ?? ($page['banner']['title'] ?? config('main.name')) }}
@endsection

@push('head-custom')
@php
  $dataSchema = $item->seo ?? null;
  $sections = $page['sections'] ?? [];
  $totalPosts = array_sum(array_map(fn ($section) => count($section['items'] ?? []), $sections));
  $categoryDesc = strip_tags((string) ($item->description ?? ''));
@endphp
@include('main.schema.social', ['data' => $dataSchema])
@include('main.schema.organization')
@include('main.schema.article', ['data' => $dataSchema])
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
@include('main.schema.itemlist', ['data' => $list ?? null])
@endpush

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.tour-location.banner', [
  'banner' => $page['banner'] ?? [],
  'defaultBookingTab' => 'tour',
])

<main id="main" class="sd-listing-page sd-blog-category-page sd-blog-category-page--parent">
  <section class="sd-section sd-section--listing-primary" aria-labelledby="blog-category-parent-title">
    <div class="sd-section__inner sd-blog-layout sd-blog-layout--category">
      <div class="sd-blog-layout__main">
        @include('main.category-v2.fragments.masthead', [
          'title' => $item->name ?? ($item->seo->title ?? ''),
          'desc' => $categoryDesc,
          'count' => $totalPosts,
          'eyebrow' => 'Blog & tin tức',
          'titleId' => 'blog-category-parent-title',
        ])

        <div class="sd-blog-sections">
          @forelse($sections as $section)
            @include('main.category-v2.fragments.section-block', [
              'section' => $section,
              'index' => $loop->index,
              'sectionId' => 'blog-group-' . $loop->index,
            ])
          @empty
            @include('main.category-v2.fragments.blog-list', [
              'items' => [],
              'showFeatured' => false,
            ])
          @endforelse
        </div>
      </div>

      @include('main.blog-v2.sections.sidebar', [
        'sidebar' => $page['sidebar'] ?? [],
        'postCount' => $totalPosts,
      ])
    </div>
  </section>
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')
@endsection
