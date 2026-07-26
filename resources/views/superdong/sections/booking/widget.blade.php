<form
  @class(['sd-booking', 'sd-booking--' . ($variant ?? '') => !empty($variant)])
  id="booking"
  action="#"
  method="get"
  novalidate
  @if(!empty($defaultTab)) data-default-tab="{{ $defaultTab }}" @endif
>
  <div class="sd-booking__head">
    <div class="sd-booking__head-text">
      <strong>Tìm &amp; đặt dịch vụ</strong>
    </div>
    <span class="sd-booking__head-badge">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
      Xác nhận tức thì
    </span>
  </div>

  <div class="sd-booking__tabs" role="tablist" aria-label="Loại dịch vụ">
    <button type="button" class="sd-booking__tab is-active" data-tab="ship" role="tab" aria-selected="true" aria-controls="panel-ship">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1M13 4v8m0 0l3-3m-3 3L10 9"/></svg>
      Vé tàu
    </button>
    <button type="button" class="sd-booking__tab" data-tab="tour" role="tab" aria-selected="false" aria-controls="panel-tour">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      Tour
    </button>
    <button type="button" class="sd-booking__tab" data-tab="hotel" role="tab" aria-selected="false" aria-controls="panel-hotel">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
      Khách sạn
    </button>
    <button type="button" class="sd-booking__tab" data-tab="combo" role="tab" aria-selected="false" aria-controls="panel-combo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"/><path d="M12 2v10M8 6l4-4 4 4"/></svg>
      Combo
    </button>
    <button type="button" class="sd-booking__tab" data-tab="ticket" role="tab" aria-selected="false" aria-controls="panel-ticket">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg>
      Vé vui chơi
    </button>
  </div>

  @php
    $shipUnavailable = ['2026-06-18', '2026-06-22', '2026-06-25'];
    $shipDisabled = ['2026-06-20'];
    $tourUnavailable = ['2026-06-17', '2026-06-21'];
    $hotelUnavailable = ['2026-06-16', '2026-06-19'];
  @endphp

  {{-- VÉ TÀU --}}
  <div class="sd-booking__panel is-active" id="panel-ship" data-panel="ship" role="tabpanel">
    <div class="sd-booking__body sd-booking__body--ship">
      @include('superdong.form.fields.field-route', [
        'fromOptions' => ['tran-de' => 'Trần Đề · Sóc Trăng', 'vung-tau' => 'Vũng Tàu · Cầu Đá'],
        'toOptions' => ['con-dao' => 'Côn Đảo · Bến Đầm'],
        'fromValue' => 'tran-de',
        'toValue' => 'con-dao',
      ])

      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-date-picker', [
          'id' => 'ship-date',
          'mode' => 'single',
          'name' => 'ship_date',
          'label' => 'Ngày khởi hành',
          'value' => '2026-06-15',
          'min' => date('Y-m-d'),
          'unavailableDates' => $shipUnavailable,
          'disabledDates' => $shipDisabled,
          'full' => true,
        ])
      </div>

      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-passengers', [
          'id' => 'ship-passengers',
          'namePrefix' => 'ship',
          'label' => 'Hành khách',
          'adult' => 2,
          'child' => 0,
          'senior' => 0,
          'maxTotal' => 8,
        ])
      </div>
    </div>
    <button type="submit" class="sd-booking__submit">Tìm chuyến tàu <span aria-hidden="true">→</span></button>
  </div>

  {{-- TOUR --}}
  <div class="sd-booking__panel" id="panel-tour" data-panel="tour" role="tabpanel" hidden>
    <div class="sd-booking__body">
      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-date-picker', [
          'id' => 'tour-date',
          'mode' => 'single',
          'name' => 'tour_date',
          'label' => 'Ngày khởi hành',
          'value' => '2026-06-16',
          'min' => date('Y-m-d'),
          'unavailableDates' => $tourUnavailable,
          'full' => true,
        ])
      </div>

      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-passengers', [
          'id' => 'tour-passengers',
          'namePrefix' => 'tour',
          'label' => 'Hành khách',
          'adult' => 2,
          'child' => 0,
          'senior' => 0,
          'maxTotal' => 12,
          'minAdult' => 1,
        ])
      </div>
    </div>
    <button type="submit" class="sd-booking__submit">Xem tour phù hợp <span aria-hidden="true">→</span></button>
  </div>

  {{-- KHÁCH SẠN --}}
  <div class="sd-booking__panel" id="panel-hotel" data-panel="hotel" role="tabpanel" hidden>
    <div class="sd-booking__body">
      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-date-picker', [
          'id' => 'hotel-stay',
          'mode' => 'range',
          'nameStart' => 'hotel_checkin',
          'nameEnd' => 'hotel_checkout',
          'label' => 'Nhận phòng – Trả phòng',
          'value' => '2026-06-15',
          'valueEnd' => '2026-06-18',
          'min' => date('Y-m-d'),
          'unavailableDates' => $hotelUnavailable,
          'full' => true,
        ])
      </div>

      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-passengers', [
          'id' => 'hotel-passengers',
          'namePrefix' => 'hotel',
          'label' => 'Hành khách',
          'adult' => 2,
          'child' => 0,
          'senior' => 0,
          'maxTotal' => 10,
          'minAdult' => 1,
        ])
      </div>
    </div>
    <button type="submit" class="sd-booking__submit">Tìm phòng trống <span aria-hidden="true">→</span></button>
  </div>

  {{-- COMBO --}}
  <div class="sd-booking__panel" id="panel-combo" data-panel="combo" role="tabpanel" hidden>
    <div class="sd-booking__body">
      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-date-picker', [
          'id' => 'combo-date',
          'mode' => 'single',
          'name' => 'combo_date',
          'label' => 'Ngày đi',
          'value' => '2026-06-15',
          'min' => date('Y-m-d'),
          'full' => true,
        ])
      </div>

      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-passengers', [
          'id' => 'combo-passengers',
          'namePrefix' => 'combo',
          'label' => 'Hành khách',
          'adult' => 2,
          'child' => 0,
          'senior' => 0,
          'maxTotal' => 8,
          'minAdult' => 2,
        ])
      </div>
    </div>
    <button type="submit" class="sd-booking__submit">Xem combo tiết kiệm <span aria-hidden="true">→</span></button>
  </div>

  {{-- VÉ VUI CHƠI --}}
  <div class="sd-booking__panel" id="panel-ticket" data-panel="ticket" role="tabpanel" hidden>
    <div class="sd-booking__body">
      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-date-picker', [
          'id' => 'ticket-date',
          'mode' => 'single',
          'name' => 'ticket_date',
          'label' => 'Ngày sử dụng',
          'value' => '2026-06-16',
          'min' => date('Y-m-d'),
          'full' => true,
        ])
      </div>

      <div class="sd-booking__row sd-booking__row--full">
        @include('superdong.form.fields.field-passengers', [
          'id' => 'ticket-passengers',
          'namePrefix' => 'ticket',
          'label' => 'Hành khách',
          'adult' => 2,
          'child' => 0,
          'senior' => 0,
          'maxTotal' => 10,
          'minAdult' => 1,
        ])
      </div>
    </div>
    <button type="submit" class="sd-booking__submit">Đặt vé ngay <span aria-hidden="true">→</span></button>
  </div>
</form>
