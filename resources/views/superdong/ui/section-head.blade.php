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
      <span class="sd-section-head__eyebrow">{{ $eyebrow }}</span>
    @endif
    <{{ $titleTag }} class="sd-section-head__title"@if(!empty($titleId)) id="{{ $titleId }}"@endif>{!! $title !!}</{{ $titleTag }}>
    @if(!empty($desc))
      <p class="sd-section-head__desc">{{ $desc }}</p>
    @endif
  </div>
  @if(!empty($linkHref))
    <a class="sd-section-head__link" href="{{ $linkHref }}">{{ $linkLabel }}</a>
  @endif
</header>
