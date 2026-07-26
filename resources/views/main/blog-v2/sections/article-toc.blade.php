@php
  $tocSub = $tocSub ?? 'Blog';
@endphp

<div class="sd-blog-toc" data-blog-toc-inline hidden>
  <div class="sd-blog-toc__title" data-blog-toc-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="blog-toc-inline-list">
    <span class="sd-blog-toc__title-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
    </span>
    <span class="sd-blog-toc__title-text">{{ t('toc_index') }}</span>
    <button type="button" class="sd-blog-toc__collapse" data-blog-toc-collapse aria-label="Thu gọn mục lục"></button>
  </div>
  <div id="blog-toc-inline-list" class="sd-blog-toc__list" data-blog-toc-list>
    <div class="sd-blog-toc__progress" aria-hidden="true">
      <span class="sd-blog-toc__progress-bar" data-toc-progress></span>
    </div>
    <nav id="js_buildTocContentSidebar_idWrite" class="sd-blog-toc__nav" aria-label="{{ t('toc_index') }}"></nav>
  </div>
</div>

<button type="button" class="sd-blog-toc-fab" data-blog-toc-fab aria-label="{{ t('toc_index') }}" hidden>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
</button>

<div class="sd-blog-toc sd-blog-toc--fixed" data-blog-toc-fixed hidden aria-hidden="true">
  <div class="sd-blog-toc__title">
    <span class="sd-blog-toc__title-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
    </span>
    <span class="sd-blog-toc__title-text">{{ t('toc_index') }}</span>
    <button type="button" class="sd-blog-toc__collapse" data-blog-toc-fixed-close aria-label="Đóng mục lục"></button>
  </div>
  <div class="sd-blog-toc__list sd-blog-toc__list--open">
    <nav id="js_buildTocContentSidebar_idWrite_fixed" class="sd-blog-toc__nav" aria-label="{{ t('toc_index') }}"></nav>
  </div>
</div>
