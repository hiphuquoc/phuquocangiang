@php
  $routes = $routes ?? [];
@endphp

@if(!empty($routes))
<div class="sd-ship-list">
  @foreach($routes as $route)
    @php
      $detailHref = $route['detailHref'] ?? null;
      $bookingHref = $route['bookingHref'] ?? ($route['href'] ?? '#booking');
      $hasDetail = !empty($detailHref) && $detailHref !== '#' && $detailHref !== '';
    @endphp
    <article class="sd-ship-row">
      <!-- Col 1: Journey (From -> Duration -> To) -->
      <div class="sd-ship-row__col sd-ship-row__col--journey">
        <div class="sd-ship-row__node sd-ship-row__node--from">
          <span class="sd-ship-row__city">{{ $route['from'] }}</span>
          @if(!empty($route['fromSub']))
            <span class="sd-ship-row__sub">{{ $route['fromSub'] }}</span>
          @endif
        </div>

        <div class="sd-ship-row__bridge">
          @if(!empty($route['duration']))
            <span class="sd-ship-row__duration">⏱ {{ $route['duration'] }}</span>
          @endif
          <div class="sd-ship-row__line" aria-hidden="true">
            <span class="sd-ship-row__ship-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M4 14.8s.8-3.8 8-3.8 8 3.8 8 3.8"/><path d="M6 11V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><path d="M9 8h6"/></svg>
            </span>
          </div>
        </div>

        <div class="sd-ship-row__node sd-ship-row__node--to">
          <span class="sd-ship-row__city">{{ $route['to'] }}</span>
          @if(!empty($route['toSub']))
            <span class="sd-ship-row__sub">{{ $route['toSub'] }}</span>
          @endif
        </div>
      </div>

      <!-- Col 2: Schedules (Hours) -->
      @if(!empty($route['schedules']))
        <div class="sd-ship-row__col sd-ship-row__col--schedules">
          <span class="sd-ship-row__col-label">Giờ xuất bến</span>
          <div class="sd-ship-row__times">
            @foreach($route['schedules'] as $time)
              <span class="sd-ship-row__time-pill">{{ $time }}</span>
            @endforeach
          </div>
        </div>
      @endif

      <!-- Col 3: Fare Chips -->
      @if(!empty($route['fareGroups']))
        <div class="sd-ship-row__col sd-ship-row__col--fares">
          <span class="sd-ship-row__col-label">Bảng giá vé</span>
          <div class="sd-ship-row__fare-chips">
            @foreach($route['fareGroups'] as $group)
              @if(!empty($group['tiers']))
                @foreach($group['tiers'] as $fare)
                  <div class="sd-ship-row__fare-chip">
                    <span class="sd-ship-row__fare-name">{{ $fare['label'] }}:</span>
                    <strong class="sd-ship-row__fare-price">{{ $fare['amount'] }}<small>₫</small></strong>
                  </div>
                @endforeach
              @elseif(!empty($group['uniform']))
                <div class="sd-ship-row__fare-chip sd-ship-row__fare-chip--vip">
                  <span class="sd-ship-row__fare-name">Ghế VIP:</span>
                  <strong class="sd-ship-row__fare-price">{{ $group['uniform'] }}<small>₫</small></strong>
                </div>
              @endif
            @endforeach
          </div>
        </div>
      @endif

      <!-- Col 4: Price & CTA -->
      <div class="sd-ship-row__col sd-ship-row__col--action">
        <div class="sd-ship-row__price">
          <small>Giá từ</small>
          <strong>{{ $route['priceFrom'] ?? 'Liên hệ' }}<em>₫</em></strong>
        </div>
        <div class="sd-ship-row__btns">
          <a href="{{ $bookingHref }}" class="sd-ship-row__btn-book" style="color: #ffffff !important;">
            <span style="color: #ffffff !important;">Đặt vé ngay</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #ffffff !important; stroke: #ffffff !important;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          @if($hasDetail)
            <a href="{{ $detailHref }}" class="sd-ship-row__btn-detail">Chi tiết ➔</a>
          @endif
        </div>
      </div>
    </article>
  @endforeach
</div>
@endif
