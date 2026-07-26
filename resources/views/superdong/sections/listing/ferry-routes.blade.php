@php
  $routes = $routes ?? [];
@endphp

@if(!empty($routes))
<div class="sd-ship-route">
  @foreach($routes as $route)
    @php
      $detailHref = $route['detailHref'] ?? null;
      $bookingHref = $route['bookingHref'] ?? ($route['href'] ?? '#booking');
      $hasDetail = !empty($detailHref) && $detailHref !== '#' && $detailHref !== '';
      $title = $route['title'] ?? trim(($route['from'] ?? '') . ' → ' . ($route['to'] ?? ''));
      $image = $route['image'] ?? '';
    @endphp
    <article class="sd-ship-route__item">
      @if($image !== '' || $hasDetail)
        <div class="sd-ship-route__media">
          @if($hasDetail)
            <a href="{{ $detailHref }}" class="sd-ship-route__media-link" tabindex="-1" aria-hidden="true">
              <img
                src="{{ $image !== '' ? $image : config('admin.images.default_750x460') }}"
                alt="{{ $route['imageAlt'] ?? $title }}"
                width="640"
                height="360"
                loading="lazy"
                decoding="async"
              >
            </a>
          @elseif($image !== '')
            <img
              src="{{ $image }}"
              alt="{{ $route['imageAlt'] ?? $title }}"
              width="640"
              height="360"
              loading="lazy"
              decoding="async"
            >
          @endif

          @if(!empty($route['priceFrom']))
            <span class="sd-ship-route__price-badge">
              {{ t('price_from') }}
              <strong>{{ $route['priceFrom'] }}<span>₫</span></strong>
            </span>
          @endif
        </div>
      @endif

      <header class="sd-ship-route__head">
        @if($title !== '')
          <h3 class="sd-ship-route__title">
            @if($hasDetail)
              <a href="{{ $detailHref }}">{{ $title }}</a>
            @else
              {{ $title }}
            @endif
          </h3>
        @endif

        <div class="sd-ship-route__journey">
          <div class="sd-ship-route__port">
            <strong>{{ $route['from'] }}</strong>
            @if(!empty($route['fromSub']))
              <span>{{ $route['fromSub'] }}</span>
            @endif
          </div>

          <div class="sd-ship-route__bridge" aria-hidden="true">
            <div class="sd-ship-route__bridge-track">
              <span class="sd-ship-route__dot"></span>
              <div class="sd-ship-route__bridge-line">
                <svg class="sd-ship-route__wave" viewBox="0 0 100 14" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M0,7 C8.3,4.8 16.7,9.2 25,7 S41.7,4.8 50,7 S66.7,9.2 75,7 S91.7,4.8 100,7" />
                </svg>
                <span class="sd-ship-route__ship">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                    <path d="M13 4v8m0 0 3-3m-3 3-3-3"/>
                  </svg>
                </span>
              </div>
              <span class="sd-ship-route__dot"></span>
            </div>
            @if(!empty($route['duration']))
              <time class="sd-ship-route__duration">{{ $route['duration'] }}</time>
            @endif
          </div>

          <div class="sd-ship-route__port sd-ship-route__port--end">
            <strong>{{ $route['to'] }}</strong>
            @if(!empty($route['toSub']))
              <span>{{ $route['toSub'] }}</span>
            @endif
          </div>
        </div>
      </header>

      @if(!empty($route['schedules']))
        <div class="sd-ship-route__schedules">
          <span class="sd-ship-route__block-label">{{ t('ship_depart_arrive') }}</span>
          <ul class="sd-ship-route__times" role="list">
            @foreach($route['schedules'] as $time)
              <li>
                <span class="sd-ship-route__time">{{ $time }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      @endif

      @if(!empty($route['fareGroups']))
        <div class="sd-ship-route__fares-wrap">
          @foreach($route['fareGroups'] as $group)
            <div class="sd-ship-route__fares{{ !empty($group['uniform']) ? ' sd-ship-route__fares--vip' : '' }}">
              <span class="sd-ship-route__block-label">{{ t('ship_price_label') }} · {{ $group['label'] }}</span>

              @if(!empty($group['uniform']))
                <div class="sd-ship-route__fare-uniform">
                  <div class="sd-ship-route__fare-uniform-copy">
                    <strong>{{ $group['label'] }}</strong>
                    @if(!empty($group['uniformHint']))
                      <small>{{ $group['uniformHint'] }}</small>
                    @endif
                  </div>
                  <span class="sd-ship-route__fare-price">
                    {{ $group['uniform'] }}<span class="sd-ship-route__fare-unit">₫</span>
                  </span>
                </div>
              @elseif(!empty($group['tiers']))
                <ul class="sd-ship-route__fare-list" role="list">
                  @foreach($group['tiers'] as $fare)
                    <li class="sd-ship-route__fare">
                      <span class="sd-ship-route__fare-type">
                        <strong>{{ $fare['label'] }}</strong>
                        @if(!empty($fare['hint']))
                          <small>{{ $fare['hint'] }}</small>
                        @endif
                      </span>
                      <span class="sd-ship-route__fare-price">{{ $fare['amount'] }}<span class="sd-ship-route__fare-unit">₫</span></span>
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          @endforeach
        </div>
      @endif

      <footer class="sd-ship-route__foot">
        <div class="sd-ship-route__actions{{ $hasDetail ? '' : ' sd-ship-route__actions--solo' }}">
          @if($hasDetail)
            <a href="{{ $detailHref }}" class="sd-ship-route__detail">
              {{ t('view_more') ?? 'Xem chi tiết' }}
            </a>
          @endif
          <a
            href="{{ $bookingHref }}"
            class="sd-ship-route__cta"
            @if(!empty($bookingHref) && $bookingHref !== '#booking' && !str_starts_with((string) $bookingHref, 'http') && !str_starts_with((string) $bookingHref, '/'))
              data-route-url="{{ $bookingHref }}"
            @endif
          >
            <span class="sd-ship-route__cta-shine" aria-hidden="true"></span>
            <span class="sd-ship-route__cta-inner">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                <path d="M13 4v8m0 0 3-3m-3 3-3-3"/>
              </svg>
              <span class="sd-ship-route__cta-label">{{ t('ship_book_mobile') }}</span>
            </span>
          </a>
        </div>
      </footer>
    </article>
  @endforeach
</div>
@endif
