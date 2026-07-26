@php
  $id = $id ?? 'sd-pax-' . uniqid();
  $label = $label ?? 'Hành khách';
  $namePrefix = $namePrefix ?? 'ship';
  $adult = (int) ($adult ?? 1);
  $child = (int) ($child ?? 0);
  $senior = (int) ($senior ?? 0);
  $maxTotal = (int) ($maxTotal ?? 9);
  $minAdult = (int) ($minAdult ?? 1);
@endphp
<div
  class="sd-fctrl sd-fctrl--passengers sd-fctrl--full"
  id="{{ $id }}"
  data-sd-passengers
  data-max-total="{{ $maxTotal }}"
  data-min-adult="{{ $minAdult }}"
>
  @if($label)
    <span class="sd-fctrl__label">{{ $label }}</span>
  @endif
  <div class="sd-fctrl__box sd-fctrl__box--passengers">
    <span class="sd-fctrl__lead" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </span>
    <button
      type="button"
      class="sd-fctrl__trigger"
      data-pax-trigger
      aria-haspopup="dialog"
      aria-expanded="false"
      aria-controls="{{ $id }}-panel"
    >
      <span class="sd-fctrl__value" data-pax-summary aria-live="polite"></span>
      <svg class="sd-fctrl__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </button>

    <div class="sd-pax" id="{{ $id }}-panel" data-pax-panel hidden role="dialog" aria-label="Chọn số lượng hành khách">
      <div class="sd-pax__row" data-pax-row="adult">
        <div class="sd-pax__info">
          <strong>Người lớn</strong>
          <span>Từ 12 tuổi</span>
        </div>
        <div class="sd-pax__stepper">
          <button type="button" class="sd-pax__btn" data-pax-step="down" data-pax-type="adult" aria-label="Giảm người lớn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14"/></svg>
          </button>
          <span class="sd-pax__val" data-pax-value="adult" aria-live="polite">{{ $adult }}</span>
          <button type="button" class="sd-pax__btn" data-pax-step="up" data-pax-type="adult" aria-label="Tăng người lớn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
          </button>
        </div>
        <input type="hidden" name="{{ $namePrefix }}_adult" value="{{ $adult }}" data-pax-input="adult">
      </div>

      <div class="sd-pax__row" data-pax-row="child">
        <div class="sd-pax__info">
          <strong>Trẻ em</strong>
          <span>6 – 11 tuổi</span>
        </div>
        <div class="sd-pax__stepper">
          <button type="button" class="sd-pax__btn" data-pax-step="down" data-pax-type="child" aria-label="Giảm trẻ em">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14"/></svg>
          </button>
          <span class="sd-pax__val" data-pax-value="child" aria-live="polite">{{ $child }}</span>
          <button type="button" class="sd-pax__btn" data-pax-step="up" data-pax-type="child" aria-label="Tăng trẻ em">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
          </button>
        </div>
        <input type="hidden" name="{{ $namePrefix }}_child" value="{{ $child }}" data-pax-input="child">
      </div>

      <div class="sd-pax__row" data-pax-row="senior">
        <div class="sd-pax__info">
          <strong>Người cao tuổi</strong>
          <span>Trên 60 tuổi</span>
        </div>
        <div class="sd-pax__stepper">
          <button type="button" class="sd-pax__btn" data-pax-step="down" data-pax-type="senior" aria-label="Giảm người cao tuổi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14"/></svg>
          </button>
          <span class="sd-pax__val" data-pax-value="senior" aria-live="polite">{{ $senior }}</span>
          <button type="button" class="sd-pax__btn" data-pax-step="up" data-pax-type="senior" aria-label="Tăng người cao tuổi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
          </button>
        </div>
        <input type="hidden" name="{{ $namePrefix }}_senior" value="{{ $senior }}" data-pax-input="senior">
      </div>

      <div class="sd-pax__foot">
        <button type="button" class="sd-pax__done" data-pax-done>Xong</button>
      </div>
    </div>
  </div>
</div>
