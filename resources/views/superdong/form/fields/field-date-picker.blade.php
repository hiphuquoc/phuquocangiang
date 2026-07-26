@php
  $id = $id ?? 'sd-dp-' . uniqid();
  $mode = $mode ?? 'single';
  $label = $label ?? '';
  $placeholder = $placeholder ?? ($mode === 'range' ? 'Chọn ngày nhận – trả phòng' : 'Chọn ngày');
  $value = $value ?? '';
  $valueEnd = $valueEnd ?? '';
  $min = $min ?? null;
  $max = $max ?? null;
  $full = $full ?? false;
  $showLegend = $showLegend ?? true;
  $name = $name ?? $id;
  $nameStart = $nameStart ?? ($mode === 'range' ? 'date_start' : $name);
  $nameEnd = $nameEnd ?? 'date_end';
  $disabledDates = $disabledDates ?? [];
  $unavailableDates = $unavailableDates ?? [];
  $availableDates = $availableDates ?? [];
  $icon = $icon ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>';
@endphp
<div
  @class(['sd-fctrl', 'sd-fctrl--date-picker', 'sd-fctrl--full' => $full])
  id="{{ $id }}"
  data-sd-date-picker
  data-mode="{{ $mode }}"
  @if($min) data-min="{{ $min }}" @endif
  @if($max) data-max="{{ $max }}" @endif
  data-value="{{ $value }}"
  @if($mode === 'range') data-value-end="{{ $valueEnd }}" @endif
  data-disabled-dates='@json($disabledDates)'
  data-unavailable-dates='@json($unavailableDates)'
  data-available-dates='@json($availableDates)'
  data-show-legend="{{ $showLegend ? 'true' : 'false' }}"
>
  <div class="sd-fctrl__box sd-fctrl__box--date sd-fctrl__box--stacked">
    <span class="sd-fctrl__lead sd-fctrl__lead--calendar" aria-hidden="true">{!! $icon !!}</span>
    <button
      type="button"
      class="sd-fctrl__trigger"
      data-date-trigger
      aria-haspopup="dialog"
      aria-expanded="false"
      aria-controls="{{ $id }}-panel"
    >
      <div class="sd-fctrl__text">
        <span class="sd-fctrl__micro">{{ $label ?: ($mode === 'range' ? 'Lịch đặt phòng' : 'Ngày khởi hành') }}</span>
        <span class="sd-fctrl__value" data-date-summary aria-live="polite">{{ $placeholder }}</span>
      </div>
      <svg class="sd-fctrl__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </button>

    <div
      class="sd-cal"
      id="{{ $id }}-panel"
      data-date-panel
      hidden
      role="dialog"
      aria-label="{{ $mode === 'range' ? 'Chọn khoảng ngày' : 'Chọn ngày' }}"
    >
      <div class="sd-cal__quick" data-date-quick>
        <button type="button" class="sd-cal__quick-btn" data-date-shortcut="today">Hôm nay</button>
        <button type="button" class="sd-cal__quick-btn" data-date-shortcut="tomorrow">Ngày mai</button>
        <button type="button" class="sd-cal__quick-btn" data-date-shortcut="weekend">Cuối tuần</button>
      </div>

      <div class="sd-cal__head">
        <button type="button" class="sd-cal__nav" data-date-prev aria-label="Tháng trước">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <strong class="sd-cal__title" data-date-title aria-live="polite"></strong>
        <button type="button" class="sd-cal__nav" data-date-next aria-label="Tháng sau">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>

      <div class="sd-cal__weekdays" aria-hidden="true">
        <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
      </div>

      <div class="sd-cal__grid" data-date-grid role="grid" aria-label="Lịch tháng"></div>

      @if($showLegend)
        <div class="sd-cal__legend" aria-hidden="true">
          <span class="sd-cal__legend-item sd-cal__legend-item--available">Có dịch vụ</span>
          <span class="sd-cal__legend-item sd-cal__legend-item--unavailable">Khóa / Hết vé</span>
        </div>
      @endif

      <div class="sd-cal__foot">
        <button type="button" class="sd-cal__clear" data-date-clear>Xóa chọn</button>
        <button type="button" class="sd-cal__done" data-date-done>Xác nhận</button>
      </div>
    </div>

    @if($mode === 'range')
      <input type="hidden" name="{{ $nameStart }}" value="{{ $value }}" data-date-input="start">
      <input type="hidden" name="{{ $nameEnd }}" value="{{ $valueEnd }}" data-date-input="end">
    @else
      <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-date-input="single">
    @endif
  </div>
</div>

