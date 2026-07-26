@php
  $article = $article ?? [];
  $title = $article['title'] ?? '';
  $desc = $article['lede'] ?? '';
  $eyebrow = $article['eyebrow'] ?? 'Blog & tin tức';
  $titleId = $titleId ?? 'blog-article-title';
  $date = $article['date'] ?? null;
  $dateIso = $article['dateIso'] ?? null;
  $author = $article['author'] ?? null;
  $category = $article['category'] ?? null;
@endphp

<header class="sd-blog-masthead sd-blog-masthead--article" aria-labelledby="{{ $titleId }}">
  <div class="sd-blog-masthead__copy">
    <span class="sd-blog-masthead__eyebrow">{{ $eyebrow }}</span>
    <h1 id="{{ $titleId }}" class="sd-blog-masthead__title">{{ $title }}</h1>
    @if($desc !== '')
      <p class="sd-blog-masthead__desc">{{ $desc }}</p>
    @endif

    @if($date || $author || $category)
      <ul class="sd-blog-masthead__meta">
        @if($date)
          <li>
            <time @if($dateIso) datetime="{{ $dateIso }}" @endif>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg>
              {{ $date }}
            </time>
          </li>
        @endif
        @if($author)
          <li>
            <span class="sd-blog-masthead__author">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              {{ $author }}
            </span>
          </li>
        @endif
        @if(!empty($category['label']))
          <li>
            <a href="{{ $category['href'] ?? '#' }}" class="sd-blog-masthead__category">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8l-2-2z"/></svg>
              {{ $category['label'] }}
            </a>
          </li>
        @endif
      </ul>
    @endif
  </div>
</header>
