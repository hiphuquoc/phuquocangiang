@php
  $hero = $hero ?? app(\App\Services\HomeHero\HomeHeroService::class)->forFrontend(app()->getLocale());
  $heroSlides = $hero['backgrounds'] ?? [];
@endphp

<div class="sd-hero-shell">
  <div class="sd-hero-shell__backdrop" aria-hidden="true">
    <div class="sd-hero-shell__media" data-hero-slider>
      @forelse($heroSlides as $i => $slide)
        <img
          src="{{ $slide['src'] }}"
          alt="{{ $slide['alt'] ?? '' }}"
          width="1920"
          height="1080"
          @class(['is-active' => $i === 0])
          loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
          @if($i === 0) fetchpriority="high" @endif
          decoding="async"
        >
      @empty
        <img
          src="https://www.agoda.com/wp-content/uploads/2024/03/Featured-image-An-Thoi-Harbour-In-Phu-Quoc-Island-Vietnam.jpg"
          alt=""
          width="1920"
          height="1080"
          class="is-active"
          loading="eager"
        >
      @endforelse
    </div>
    <div class="sd-hero-shell__grain"></div>
    <div class="sd-hero-shell__cut">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,22 L760,108 Q820,118 880,108 L1440,20 L1440,120 L0,120 Z"/>
      </svg>
    </div>
  </div>

  @include('superdong.chrome.header')
  @include('superdong.sections.hero.content')

  @if(count($heroSlides) > 1)
    <div class="sd-hero__slider-dots" data-hero-dots aria-hidden="true">
      @foreach($heroSlides as $i => $slide)
        <button type="button" @class(['is-active' => $i === 0]) data-hero-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
      @endforeach
    </div>
  @endif
</div>
