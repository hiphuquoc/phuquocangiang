<!-- QUICK ACCESS -->
@php
  $quick = $island['quickAccess'] ?? [];
  $quickCards = $quick['cards'] ?? [];
@endphp
<section class="sd-quick">
  <div class="sd-quick__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $quick['eyebrow'] ?? 'Dịch vụ trọn đảo',
      'title' => $quick['title'] ?? '',
      'desc' => $quick['desc'] ?? '',
      'align' => 'center',
    ])

    @if(!empty($quickCards))
      <div class="sd-quick__grid">
        @foreach($quickCards as $index => $card)
          @php $imageUrl = $card['image'] ?? ''; @endphp
          <a
            class="sd-quick__card{{ !empty($card['large']) ? ' sd-quick__card--lg' : '' }}"
            href="{{ $card['href'] ?? '#' }}"
            data-reveal
            @if($index < 2 && $imageUrl !== '')
              style="--sd-img:url('{{ $imageUrl }}')"
            @elseif($imageUrl !== '')
              data-lazy-bg="{{ $imageUrl }}"
            @endif
          >
            <span class="sd-quick__top">
              <span class="sd-quick__tag">{{ $card['tag'] ?? '' }}</span>
              <span class="sd-quick__num" aria-hidden="true">{{ $card['num'] ?? '' }}</span>
            </span>
            <span class="sd-quick__content">
              <span class="sd-quick__copy">
                <strong class="sd-quick__label">{{ $card['label'] ?? '' }}</strong>
                @if(!empty($card['meta']))
                  <span class="sd-quick__meta">{{ $card['meta'] }}</span>
                @endif
              </span>
              <span class="sd-quick__action">
                <span class="sd-quick__cta">
                  <span>{{ $card['cta'] ?? 'Xem thêm' }}</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </span>
            </span>
          </a>
        @endforeach
      </div>
    @endif
  </div>
</section>
