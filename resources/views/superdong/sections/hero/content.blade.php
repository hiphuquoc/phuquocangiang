@php
  $hero = $hero ?? app(\App\Services\HomeHero\HomeHeroService::class)->forFrontend(app()->getLocale());
  $heroRoutes = $hero['routes'] ?? [];
  $heroButtons = $hero['buttons'] ?? [];
  $primaryBtn = $heroButtons['primary'] ?? ['enabled' => true, 'label' => 'Đặt vé tàu', 'url' => '#booking'];
  $secondaryBtn = $heroButtons['secondary'] ?? ['enabled' => true, 'label' => '1900 545 487', 'url' => 'tel:1900545487'];
  $islandLabel = $island['name'] ?? island_name();
@endphp

<section class="sd-hero" aria-label="Giới thiệu {{ $islandLabel }}">
  <div class="sd-hero__body">
    <div class="sd-hero__inner">
      <div class="sd-hero__content">
        <!-- Eyebrow Badge -->
        <div class="sd-hero__badge">
          <span class="sd-hero__badge-pulse"></span>
          <span>VÉ TÀU CAO TỐC CHÍNH HÃNG</span>
        </div>

        <!-- Headline & Tagline -->
        <div class="sd-hero__lead">
          <h1 class="sd-hero__title">
            {{ $hero['title'] ?? ('Vé Tàu Cao Tốc Đi ' . $islandLabel) }}
            <span class="sd-hero__title-accent">{{ $hero['title_accent'] ?? 'Hành Trình Trọn Vẹn' }}</span>
          </h1>
          <p class="sd-hero__tagline">
            {{ $hero['tagline'] ?? 'Tra cứu lịch trình & đặt vé tàu cao tốc trực tuyến giá niêm yết — Nhận vé điện tử QR code tức thì trong 30 giây.' }}
          </p>
        </div>

        <!-- Route Tickets Streamlined List -->
        @if(!empty($heroRoutes))
          <div class="sd-hero__routes-wrapper">
            <ul class="sd-hero__routes" aria-label="Tuyến tàu cao tốc nổi bật">
              @foreach($heroRoutes as $route)
                <li>
                  <a class="sd-hero__route-card" href="{{ $route['href'] ?? '#booking' }}">
                    <div class="sd-hero__route-journey">
                      <span class="sd-hero__route-from">{{ $route['from'] ?? '' }}</span>
                      <span class="sd-hero__route-arrow">➔</span>
                      <span class="sd-hero__route-to">{{ $route['to'] ?? '' }}</span>
                    </div>
                    <div class="sd-hero__route-info">
                      @if(!empty($route['duration']))
                        <span class="sd-hero__route-duration">⏱ {{ $route['duration'] }}</span>
                      @endif
                      @if(!empty($route['price']))
                        <div class="sd-hero__route-price">
                          <small>GIÁ TỪ</small>
                          <strong>{{ $route['price'] }}<small>đ</small></strong>
                        </div>
                      @endif
                    </div>
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        <!-- Action Buttons -->
        @if(!empty($primaryBtn['enabled']) || !empty($secondaryBtn['enabled']))
          <div class="sd-hero__actions">
            @if(!empty($primaryBtn['enabled']))
              <a class="sd-hero__btn sd-hero__btn--solid" href="{{ $primaryBtn['url'] ?? '#booking' }}">
                <span>{{ $primaryBtn['label'] ?? 'Đặt vé ngay' }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </a>
            @endif
            @if(!empty($secondaryBtn['enabled']))
              <a class="sd-hero__btn sd-hero__btn--ghost" href="{{ $secondaryBtn['url'] ?? 'tel:1900545487' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.79 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <span>{{ $secondaryBtn['label'] ?? '1900 545 487' }}</span>
              </a>
            @endif
          </div>
        @endif
      </div>

      @include('superdong.sections.booking.widget')
    </div>
  </div>
</section>
