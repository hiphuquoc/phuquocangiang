@php
  $categories = $sidebar['categories'] ?? ($islandBlogCategories ?? []);
  $parent = $sidebar['parent'] ?? null;
  $postCount = isset($postCount) ? (int) $postCount : null;
@endphp

<aside class="sd-blog-sidebar" aria-label="Chuyên mục blog">
  @if($postCount !== null && $postCount > 0)
    <div class="sd-blog-sidebar__stat">
      <span class="sd-blog-sidebar__stat-value">{{ $postCount }}</span>
      <span class="sd-blog-sidebar__stat-label">bài trong chuyên mục</span>
    </div>
  @endif

  @if(!empty($parent))
    <div class="sd-blog-sidebar__parent">
      <span class="sd-blog-sidebar__kicker">{{ isset($postCount) ? 'Chuyên mục' : 'Đang đọc' }}</span>
      <a href="{{ $parent['href'] ?? '#' }}">{{ $parent['label'] ?? '' }}</a>
    </div>
  @endif

  @if(!empty($categories))
    <div class="sd-blog-sidebar__box">
      <h2 class="sd-blog-sidebar__title">Khám phá thêm</h2>
      <nav class="sd-blog-sidebar__nav">
        @foreach($categories as $link)
          <a
            href="{{ $link['href'] ?? '#' }}"
            @class(['sd-blog-sidebar__link', 'is-active' => !empty($link['active'])])
          >{{ $link['label'] ?? '' }}</a>
        @endforeach
      </nav>
    </div>
  @endif
</aside>
