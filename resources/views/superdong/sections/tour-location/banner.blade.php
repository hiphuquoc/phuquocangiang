@php
  $banner = $banner ?? [];
  $defaultBookingTab = $defaultBookingTab ?? 'tour';
@endphp

<div class="sd-hero-shell sd-hero-shell--listing">
  <div class="sd-hero-shell__backdrop" aria-hidden="true">
    <div class="sd-hero-shell__media">
      <img
        src="{{ $banner['image'] ?? '' }}"
        alt="{{ $banner['imageAlt'] ?? '' }}"
        width="1920"
        height="1080"
        class="is-active"
        loading="eager"
        decoding="async"
      >
    </div>
    <div class="sd-hero-shell__grain"></div>
    <div class="sd-hero-shell__cut">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,22 L760,108 Q820,118 880,108 L1440,20 L1440,120 L0,120 Z"/>
      </svg>
    </div>
  </div>

  @include('superdong.chrome.header')

  <section class="sd-hero sd-hero--listing" aria-label="{{ $banner['locationName'] ?? 'Tour' }}">
    @if(!empty($banner['title']))
      <h1 class="sd-hero__sr-only">{{ $banner['title'] }}</h1>
    @endif
    <div class="sd-hero__body">
      <div class="sd-hero__inner sd-hero__inner--listing">
        @include('superdong.sections.booking.widget', [
          'defaultTab' => $defaultBookingTab,
          'variant' => 'listing',
        ])
      </div>
    </div>
  </section>
</div>
