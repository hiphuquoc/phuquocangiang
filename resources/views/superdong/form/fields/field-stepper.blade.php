@php
  $id = $id ?? 'sd-step-' . uniqid();
  $name = $name ?? $id;
  $label = $label ?? '';
  $value = (int) ($value ?? 1);
  $min = (int) ($min ?? 1);
  $max = (int) ($max ?? 9);
  $unit = $unit ?? 'khách';
  $full = $full ?? false;
@endphp
<div @class(['sd-fctrl', 'sd-fctrl--stepper', 'sd-fctrl--full' => $full]) data-sd-stepper data-min="{{ $min }}" data-max="{{ $max }}">
  @if($label)
    <span class="sd-fctrl__label">{{ $label }}</span>
  @endif
  <div class="sd-fctrl__box sd-fctrl__box--stepper">
    <button type="button" class="sd-fctrl__step" data-step="down" aria-label="Giảm số lượng">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg>
    </button>
    <span class="sd-fctrl__step-val" data-step-value aria-live="polite">{{ $value }}</span>
    <span class="sd-fctrl__step-unit">{{ $unit }}</span>
    <button type="button" class="sd-fctrl__step" data-step="up" aria-label="Tăng số lượng">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
    </button>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
  </div>
</div>
