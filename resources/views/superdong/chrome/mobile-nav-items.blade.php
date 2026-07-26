@php
  $menuItems = $islandMenu ?? [];
@endphp

@if(!empty($menuItems))
  @foreach($menuItems as $item)
    @if(!empty($item['dropdown']) && !empty($item['children']))
      <div class="sd-mobile-nav__group" data-mobile-nav-group>
        <button
          type="button"
          class="sd-mobile-nav__group-trigger{{ !empty($item['active']) ? ' is-active' : '' }}"
          aria-expanded="false"
        >
          {{ $item['label'] ?? 'Blog' }}
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
        </button>
        <div class="sd-mobile-nav__group-panel" hidden>
          @foreach($item['children'] as $child)
            <a
              href="{{ $child['href'] ?? '#' }}"
              @class(['sd-mobile-nav__sublink', 'is-active' => !empty($child['active'])])
            >{{ $child['label'] ?? '' }}</a>
            @foreach($child['children'] ?? [] as $sub)
              <a
                href="{{ $sub['href'] ?? '#' }}"
                @class(['sd-mobile-nav__sublink sd-mobile-nav__sublink--nested', 'is-active' => !empty($sub['active'])])
              >{{ $sub['label'] ?? '' }}</a>
            @endforeach
          @endforeach
        </div>
      </div>
    @else
      <a
        href="{{ $item['href'] ?? '#' }}"
        @class(['sd-mobile-nav__link', 'is-active' => !empty($item['active'])])
      >{{ $item['label'] ?? '' }}</a>
    @endif
  @endforeach
@endif
