@php
  $fromOptions = $fromOptions ?? [];
  $toOptions = $toOptions ?? [];
  $fromValue = $fromValue ?? array_key_first($fromOptions);
  $toValue = $toValue ?? array_key_first($toOptions);
@endphp
<div class="sd-fctrl sd-fctrl--route sd-fctrl--full" data-sd-route>
  <div class="sd-route">
    <div class="sd-route__col" data-sd-select>
      <div class="sd-fctrl__box sd-fctrl__box--stacked">
        <span class="sd-fctrl__lead sd-fctrl__lead--origin" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
        </span>
        <button type="button" class="sd-fctrl__trigger" aria-haspopup="listbox" aria-expanded="false">
          <div class="sd-fctrl__text">
            <span class="sd-fctrl__micro">Điểm đi</span>
            <span class="sd-fctrl__value">{{ $fromOptions[$fromValue] ?? reset($fromOptions) }}</span>
          </div>
          <svg class="sd-fctrl__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul class="sd-fctrl__menu" role="listbox" hidden>
          @foreach($fromOptions as $val => $lbl)
            <li role="option" data-value="{{ $val }}" @class(['is-selected' => (string) $val === (string) $fromValue])>{{ $lbl }}</li>
          @endforeach
        </ul>
        <input type="hidden" name="ship_from" value="{{ $fromValue }}">
      </div>
    </div>

    <button type="button" class="sd-route__swap" data-sd-route-swap aria-label="Đảo chiều hành trình" title="Đảo chiều hành trình">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M7 16V4M7 4L3 8M7 4l4 4M17 8v12M17 20l4-4M17 20l-4-4"/></svg>
    </button>

    <div class="sd-route__col" data-sd-select>
      <div class="sd-fctrl__box sd-fctrl__box--stacked">
        <span class="sd-fctrl__lead sd-fctrl__lead--dest" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <button type="button" class="sd-fctrl__trigger" aria-haspopup="listbox" aria-expanded="false">
          <div class="sd-fctrl__text">
            <span class="sd-fctrl__micro">Điểm đến</span>
            <span class="sd-fctrl__value">{{ $toOptions[$toValue] ?? reset($toOptions) }}</span>
          </div>
          <svg class="sd-fctrl__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul class="sd-fctrl__menu" role="listbox" hidden>
          @foreach($toOptions as $val => $lbl)
            <li role="option" data-value="{{ $val }}" @class(['is-selected' => (string) $val === (string) $toValue])>{{ $lbl }}</li>
          @endforeach
        </ul>
        <input type="hidden" name="ship_to" value="{{ $toValue }}">
      </div>
    </div>
  </div>
</div>

