@php
  $title = $section['title'] ?? '';
  $href = $section['href'] ?? '#';
  $items = $section['items'] ?? [];
  $count = count($items);
  $sectionId = $sectionId ?? 'blog-section-' . ($index ?? 0);
@endphp

<section class="sd-blog-section-block" aria-labelledby="{{ $sectionId }}">
  <div class="sd-blog-section-block__head">
    <div class="sd-blog-section-block__titles">
      <span class="sd-blog-section-block__kicker">Chuyên mục con</span>
      <h2 id="{{ $sectionId }}" class="sd-blog-section-block__title">
        <a href="{{ $href }}">{{ $title }}</a>
      </h2>
    </div>
    @if($count > 0)
      <div class="sd-blog-section-block__meta">
        <span class="sd-blog-section-block__count">{{ $count }} bài</span>
        <a href="{{ $href }}" class="sd-blog-section-block__all">
          Xem tất cả
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8-8-8z"/></svg>
        </a>
      </div>
    @endif
  </div>

  @include('main.category-v2.fragments.blog-list', [
    'items' => $items,
    'showFeatured' => true,
  ])
</section>
