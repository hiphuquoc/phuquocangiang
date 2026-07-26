@php
  $name = $name ?? 'area';
  $label = $label ?? 'Khu vực';
  $options = $options ?? [];
  $value = $value ?? array_key_first($options);
@endphp
<div class="sd-fctrl sd-fctrl--chips sd-fctrl--full" data-sd-chips>
  <span class="sd-fctrl__label">{{ $label }}</span>
  <div class="sd-chips" role="radiogroup" aria-label="{{ $label }}">
    @foreach($options as $optVal => $optLabel)
      <label @class(['sd-chip', 'is-selected' => (string) $optVal === (string) $value])>
        <input type="radio" name="{{ $name }}" value="{{ $optVal }}" @checked((string) $optVal === (string) $value)>
        {{ $optLabel }}
      </label>
    @endforeach
  </div>
</div>
