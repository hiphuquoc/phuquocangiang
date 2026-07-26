@php
  $sdHotlineDisplay = '1900 5454 87';
  $sdHotlineTel = '1900545487';
@endphp

@php
  $nav = $islandNav ?? island_nav();
@endphp

<header class="sd-header" id="top">
  <div class="sd-header__inner">
    <a class="sd-header__brand" href="{{ $nav['home'] ?? route('main.home') }}">
      <span class="sd-header__logo-mark">SD</span>
      <span class="sd-header__logo-text">
        <strong>Superdong</strong>
        <span>Côn Đảo Travel</span>
      </span>
    </a>
    <nav class="sd-header__nav" aria-label="Menu chính">
      @include('superdong.chrome.nav')
    </nav>
    <div class="sd-header__actions">
      <div class="sd-header__region sd-header__region--mobile">
        @include('main.snippets.regionSwitcher', ['variant' => 'mobile'])
      </div>

      <a href="tel:{{ $sdHotlineTel }}" class="sd-header__hotline">
        <span class="sd-header__hotline-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.5 19.5 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
        </span>
        <span class="sd-header__hotline-text">
          <small>Hotline 24/7</small>
          <strong>{{ $sdHotlineDisplay }}</strong>
        </span>
      </a>

      <button type="button" class="sd-header__toggle" aria-label="Mở menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
