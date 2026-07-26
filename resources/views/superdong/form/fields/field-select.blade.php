@php
  $id = $id ?? 'sd-select-' . uniqid();
  $name = $name ?? $id;
  $label = $label ?? '';
  $options = $options ?? [];
  $value = $value ?? array_key_first($options);
  $icon = $icon ?? null;
  $full = $full ?? false;
  $hint = $hint ?? null;
@endphp
<div @class(['sd-fctrl', 'sd-fctrl--select', 'sd-fctrl--full' => $full]) data-sd-select>
  @if($label)
    <label class="sd-fctrl__label" for="{{ $id }}-trigger">{{ $label }}</label>
  @endif
  <div class="sd-fctrl__box">
    @if($icon)
      <span class="sd-fctrl__lead" aria-hidden="true">{!! $icon !!}</span>
    @endif
    <button
      type="button"
      class="sd-fctrl__trigger"
      id="{{ $id }}-trigger"
      aria-haspopup="listbox"
      aria-expanded="false"
      aria-labelledby="{{ $id }}-trigger"
    >
      <span class="sd-fctrl__value">{{ $options[$value] ?? reset($options) }}</span>
      <svg class="sd-fctrl__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </button>
    <ul class="sd-fctrl__menu" role="listbox" hidden>
      @foreach($options as $optVal => $optLabel)
        <li
          role="option"
          data-value="{{ $optVal }}"
          @class(['is-selected' => (string) $optVal === (string) $value])
          aria-selected="{{ (string) $optVal === (string) $value ? 'true' : 'false' }}"
        >{{ $optLabel }}</li>
      @endforeach
    </ul>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
  </div>
  @if($hint)
    <p class="sd-fctrl__hint">{{ $hint }}</p>
  @endif
</div>
