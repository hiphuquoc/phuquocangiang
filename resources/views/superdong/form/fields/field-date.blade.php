@php
  $id = $id ?? 'sd-date-' . uniqid();
  $name = $name ?? $id;
  $label = $label ?? '';
  $value = $value ?? '';
  $min = $min ?? null;
  $full = $full ?? false;
  $icon = $icon ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';
@endphp
<div @class(['sd-fctrl', 'sd-fctrl--date', 'sd-fctrl--full' => $full])>
  @if($label)
    <label class="sd-fctrl__label" for="{{ $id }}">{{ $label }}</label>
  @endif
  <div class="sd-fctrl__box">
    <span class="sd-fctrl__lead" aria-hidden="true">{!! $icon !!}</span>
    <input
      type="date"
      class="sd-fctrl__input"
      id="{{ $id }}"
      name="{{ $name }}"
      value="{{ $value }}"
      @if($min) min="{{ $min }}" @endif
    >
  </div>
</div>
