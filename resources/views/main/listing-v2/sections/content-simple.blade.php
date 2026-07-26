{{--
  Content đơn giản — chỉ sd-section__inner + prose (không TOC sidebar).
  Dùng cho trang chi tiết vé tàu.
--}}
@php
  $sectionClass = $sectionClass ?? 'sd-listing-content sd-listing-content--simple';
  $titleId = $titleId ?? 'listing-content-title';
  $hasArticle = !empty($articleTop) || !empty($content) || !empty($articleBottom);
@endphp

@if($hasArticle)
<section class="sd-section {{ $sectionClass }}" @if(!empty($title)) aria-labelledby="{{ $titleId }}" @endif>
  <div class="sd-section__inner">
    @if(!empty($title) || !empty($eyebrow))
      @include('superdong.ui.section-head', [
        'eyebrow' => $eyebrow ?? '',
        'title' => $title ?? '',
        'desc' => $lede ?? '',
        'titleId' => $titleId,
        'titleTag' => 'h2',
        'reveal' => false,
        'compact' => true,
      ])
    @endif

    <article class="sd-listing-content__article sd-listing-content__prose">
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
</section>
@endif
