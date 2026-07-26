@php
  $nav = $islandNav ?? island_nav();
@endphp
<div class="sd-mobile-nav" aria-hidden="true">
  <div class="sd-mobile-nav__backdrop"></div>
  <nav class="sd-mobile-nav__panel" aria-label="Menu di động">
    @include('superdong.chrome.mobile-nav-items')
    <div class="sd-mobile-nav__cta">
      <a href="tel:1900545487" class="sd-header__hotline sd-header__hotline--drawer">
        <span class="sd-header__hotline-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.5 19.5 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
        </span>
        <span class="sd-header__hotline-text">
          <small>Hotline 24/7</small>
          <strong>1900 5454 87</strong>
        </span>
      </a>
    </div>
  </nav>
</div>
