{{--
  Section header dùng chung — đồng bộ typography theo design system.

  Props:
  - $eyebrow   (string)  Nhãn trên tiêu đề
  - $title     (string)  H2 — có thể chứa HTML (gradient span)
  - $desc      (string?) Mô tả ngắn
  - $linkHref  (string?) URL nút phụ bên phải
  - $linkLabel (string)  Nhãn link, mặc định "Xem thêm"
  - $align     (string)  start | center — mặc định start
  - $compact   (bool)    Giảm margin dưới (khối lồng nhau)
  - $titleTag  (string)  h1 | h2 — mặc định h2
--}}
@php
  $align = $align ?? 'start';
  $compact = !empty($compact);
  $reveal = $reveal ?? true;
  $linkLabel = $linkLabel ?? 'Xem thêm';
  $titleTag = in_array($titleTag ?? 'h2', ['h1', 'h2'], true) ? ($titleTag ?? 'h2') : 'h2';
  $headClass = 'sd-section-head sd-section-head--' . $align . ($compact ? ' sd-section-head--compact' : '');
@endphp

<header class="{{ $headClass }}" @if($reveal) data-reveal @endif>
  <div class="sd-section-head__main">
    @if(!empty($eyebrow))
      <div class="sd-section-head__eyebrow-wrapper">
        <span class="sd-section-head__accent-dot" aria-hidden="true"></span>
        <span class="sd-section-head__eyebrow">{{ $eyebrow }}</span>
      </div>
    @endif
    <{{ $titleTag }} class="sd-section-head__title"@if(!empty($titleId)) id="{{ $titleId }}"@endif>{!! $title !!}</{{ $titleTag }}>
    @if(!empty($desc))
      <p class="sd-section-head__desc">{{ $desc }}</p>
    @endif
  </div>
  @if(!empty($linkHref))
    <a class="sd-section-head__link" href="{{ $linkHref }}">
      <span>{{ $linkLabel }}</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  @endif
</header>
