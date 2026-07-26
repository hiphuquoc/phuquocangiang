@php
  $related = $related ?? [];
  $items = $related['items'] ?? [];
  $title = $related['head']['title'] ?? t('blog_related');
  $eyebrow = $related['head']['eyebrow'] ?? 'Blog & tin tức';
@endphp

@if(!empty($items))
  <section class="sd-blog-related" aria-labelledby="blog-related-title">
    <div class="sd-blog-section-block__head">
      <div class="sd-blog-section-block__titles">
        <span class="sd-blog-section-block__kicker">{{ $eyebrow }}</span>
        <h2 id="blog-related-title" class="sd-blog-section-block__title">{{ $title }}</h2>
      </div>
      <div class="sd-blog-section-block__meta">
        <span class="sd-blog-section-block__count">{{ count($items) }} bài</span>
      </div>
    </div>

    @include('main.category-v2.fragments.blog-list', [
      'items' => $items,
      'showFeatured' => false,
    ])
  </section>
@endif
