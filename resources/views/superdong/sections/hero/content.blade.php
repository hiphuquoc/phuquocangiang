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
        <div class="sd-hero__lead">
          <h1 class="sd-hero__title">
            {{ $hero['title'] ?? ('Khám phá ' . $islandLabel) }}
            @if(!empty($hero['title_accent']))
              <span class="sd-hero__title-accent">{{ $hero['title_accent'] }}</span>
            @endif
          </h1>
        </div>
        @if(!empty($hero['tagline']))
          <p class="sd-hero__tagline">{{ $hero['tagline'] }}</p>
        @endif

        @if(!empty($heroRoutes))
          <ul class="sd-hero__routes" aria-label="Tuyến tàu cao tốc nổi bật">
            @foreach($heroRoutes as $route)
              <li>
                <a class="sd-hero__route-card" href="{{ $route['href'] ?? '#booking' }}">
                  @if(!empty($route['duration']))
                    <span class="sd-hero__route-duration">{{ $route['duration'] }}</span>
                  @endif

                  <div class="sd-hero__route-journey">
                    <span class="sd-hero__route-point-name">{{ $route['from'] ?? '' }}</span>

                    <div class="sd-hero__route-bridge" aria-hidden="true">
                      <svg class="sd-hero__route-wave" viewBox="0 0 100 14" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0,7 C8.3,4.8 16.7,9.2 25,7 S41.7,4.8 50,7 S66.7,9.2 75,7 S91.7,4.8 100,7" />
                      </svg>
                      <span class="sd-hero__route-ship">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                          <path d="M13 4v8m0 0 3-3m-3 3-3-3"/>
                        </svg>
                      </span>
                    </div>

                    <span class="sd-hero__route-point-name sd-hero__route-point-name--end">{{ $route['to'] ?? '' }}</span>
                  </div>

                  <div class="sd-hero__route-foot">
                    @if(!empty($route['price']))
                      <p class="sd-hero__route-price">
                        <span class="sd-hero__route-price-val">{{ $route['price'] }}</span>
                        <span class="sd-hero__route-price-unit">đ</span>
                      </p>
                    @endif
                    <span class="sd-hero__route-go">
                      <span>Đặt vé</span>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                  </div>
                </a>
              </li>
            @endforeach
          </ul>
        @endif

        @if(!empty($primaryBtn['enabled']) || !empty($secondaryBtn['enabled']))
          <div class="sd-hero__actions">
            @if(!empty($primaryBtn['enabled']))
              <a class="sd-hero__btn sd-hero__btn--solid" href="{{ $primaryBtn['url'] ?? '#booking' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1M13 4v8m0 0l3-3m-3 3L10 9"/></svg>
                <span>{{ $primaryBtn['label'] ?? 'Đặt vé tàu' }}</span>
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
