@php
  $content = $content ?? '';
  $hasContent = trim(strip_tags((string) $content)) !== '';
@endphp

@if($hasContent)
  <div class="sd-blog-article-body" data-blog-article>
    <article
      id="js_buildTocContentSidebar_element"
      class="sd-blog-article-body__prose sd-listing-content__prose"
    >
      {!! $content !!}
    </article>
  </div>
@endif
