@php
  $menuItems = $islandMenu ?? [];
@endphp

@if(!empty($menuItems))
  @foreach($menuItems as $item)
    @if(!empty($item['dropdown']) && !empty($item['children']))
      <div class="sd-nav-dropdown" data-nav-dropdown>
        <a
          href="{{ $item['href'] ?? '#' }}"
          class="sd-nav-dropdown__trigger{{ !empty($item['active']) ? ' is-active' : '' }}"
          aria-haspopup="true"
          aria-expanded="false"
        >
          <span>{{ $item['label'] ?? '' }}</span>
          <svg class="sd-nav-dropdown__chevron" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
        </a>
        <div class="sd-nav-dropdown__panel" hidden>
          <ul class="sd-nav-dropdown__list">
            @foreach($item['children'] as $child)
              <li class="sd-nav-dropdown__item{{ !empty($child['children']) ? ' sd-nav-dropdown__item--group' : '' }}">
                <a
                  href="{{ $child['href'] ?? '#' }}"
                  @class(['sd-nav-dropdown__link', 'is-active' => !empty($child['active'])])
                >{{ $child['label'] ?? '' }}</a>
                @if(!empty($child['children']))
                  <ul class="sd-nav-dropdown__sublist">
                    @foreach($child['children'] as $sub)
                      <li>
                        <a
                          href="{{ $sub['href'] ?? '#' }}"
                          @class(['sd-nav-dropdown__sublink', 'is-active' => !empty($sub['active'])])
                        >{{ $sub['label'] ?? '' }}</a>
                      </li>
                    @endforeach
                  </ul>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @else
      <a
        href="{{ $item['href'] ?? '#' }}"
        @class(['is-active' => !empty($item['active'])])
      ><span>{{ $item['label'] ?? '' }}</span></a>
    @endif
  @endforeach
@endif
