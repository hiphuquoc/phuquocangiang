{{--
  Content + TOC sidebar — dùng chung tour / ship / service listing v2.

  Props:
  - $content       (string?) HTML bài đọc
  - $articleTop    (string?) HTML chèn trước content (lịch tàu, v.v.)
  - $articleBottom (string?) HTML chèn sau content (FAQ, v.v.)
  - $eyebrow, $title, $lede, $titleId, $tocSub
  - $sectionClass  (string) modifier section — mặc định sd-listing-content
--}}
@php
  $sectionClass = $sectionClass ?? 'sd-listing-content';
  $titleId = $titleId ?? 'listing-content-title';
  $tocSub = $tocSub ?? ($eyebrow ?? '');
  $hasArticle = !empty($articleTop) || !empty($content) || !empty($articleBottom);
@endphp

@if($hasArticle)
<section class="sd-section {{ $sectionClass }}" aria-labelledby="{{ $titleId }}">
  <div class="sd-section__inner sd-listing-content__shell">
    @include('superdong.ui.section-head', [
      'eyebrow' => $eyebrow ?? '',
      'title' => $title ?? '',
      'desc' => $lede ?? '',
      'titleId' => $titleId,
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    <div class="sd-listing-content__layout">
      <div class="sd-listing-content__main">
        <article
          id="js_buildTocContentSidebar_element"
          class="sd-listing-content__article sd-listing-content__prose"
        >
          @if(!empty($articleTop))
            {!! $articleTop !!}
          @endif
          @if(!empty($content))
            {!! $content !!}
          @endif
          @if(!empty($articleBottom))
            {!! $articleBottom !!}
          @endif
        </article>
      </div>

      <aside class="sd-listing-content__aside" aria-label="{{ t('toc_index') }}">
        <div class="sd-content-toc" data-content-toc>
          <div class="sd-content-toc__head">
            <span class="sd-content-toc__head-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </span>
            <div class="sd-content-toc__head-text">
              <strong>{{ t('toc_index') }}</strong>
              <span class="sd-content-toc__head-sub">{{ $tocSub }}</span>
            </div>
          </div>
          <div class="sd-content-toc__body">
            <div class="sd-content-toc__progress" aria-hidden="true">
              <span class="sd-content-toc__progress-bar" data-toc-progress></span>
            </div>
            <nav id="js_buildTocContentSidebar_idWrite" class="sd-content-toc__nav" aria-label="{{ t('toc_index') }}"></nav>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
@endif
