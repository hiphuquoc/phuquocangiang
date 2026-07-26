@php
  $name = $name ?? 'option';
  $label = $label ?? '';
  $options = $options ?? [];
  $value = $value ?? array_key_first($options);
  $full = $full ?? true;
@endphp
<div @class(['sd-fctrl', 'sd-fctrl--options', 'sd-fctrl--full' => $full]) data-sd-options>
  @if($label)
    <span class="sd-fctrl__label">{{ $label }}</span>
  @endif
  <div class="sd-optgrid" role="radiogroup" aria-label="{{ $label }}">
    @foreach($options as $optVal => $opt)
      @php
        $optLabel = is_array($opt) ? ($opt['label'] ?? $optVal) : $opt;
        $optMeta = is_array($opt) ? ($opt['meta'] ?? null) : null;
        $optBadge = is_array($opt) ? ($opt['badge'] ?? null) : null;
        $selected = (string) $optVal === (string) $value;
      @endphp
      <label @class(['sd-optcard', 'is-selected' => $selected])>
        <input type="radio" name="{{ $name }}" value="{{ $optVal }}" @checked($selected)>
        @if($optBadge)
          <span class="sd-optcard__badge">{{ $optBadge }}</span>
        @endif
        <span class="sd-optcard__title">{{ $optLabel }}</span>
        @if($optMeta)
          <span class="sd-optcard__meta">{{ $optMeta }}</span>
        @endif
      </label>
    @endforeach
  </div>
</div>
