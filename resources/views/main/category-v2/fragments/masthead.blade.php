@php
  $title = $title ?? '';
  $desc = $desc ?? '';
  $count = (int) ($count ?? 0);
  $eyebrow = $eyebrow ?? 'Blog & tin tức';
  $titleId = $titleId ?? 'blog-category-title';
@endphp

<header class="sd-blog-masthead" aria-labelledby="{{ $titleId }}">
  <div class="sd-blog-masthead__copy">
    <span class="sd-blog-masthead__eyebrow">{{ $eyebrow }}</span>
    <h1 id="{{ $titleId }}" class="sd-blog-masthead__title">{{ $title }}</h1>
    @if($desc !== '')
      <p class="sd-blog-masthead__desc">{{ $desc }}</p>
    @endif
  </div>

  @if($count > 0)
    <div class="sd-blog-masthead__stat" aria-label="{{ $count }} bài viết">
      <strong>{{ $count }}</strong>
      <span>{{ $count === 1 ? 'bài viết' : 'bài viết' }}</span>
    </div>
  @endif
</header>
