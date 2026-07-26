@php
  $related = $relatedServices ?? ['head' => [], 'items' => []];
  $relatedItems = $related['items'] ?? [];
  $relatedHead = $related['head'] ?? [];
  $locationName = $locationName ?? '';
@endphp

@if(!empty($relatedItems))
<section class="sd-section sd-tour-related" id="related-services" aria-labelledby="tour-related-services-title">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $relatedHead['eyebrow'] ?? '',
      'title' => $relatedHead['title'] ?? t('tour_related_services_title'),
      'desc' => $relatedHead['desc'] ?? '',
      'titleId' => 'tour-related-services-title',
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    <div class="sd-tour-related__track" role="list">
      @foreach($relatedItems as $card)
        <a
          href="{{ $card['href'] ?? '#' }}"
          class="sd-tour-related__card sd-tour-related__card--{{ $card['type'] ?? 'default' }}"
          role="listitem"
        >
          <span class="sd-tour-related__top">
            <span class="sd-tour-related__icon" aria-hidden="true">
              @switch($card['type'] ?? '')
                @case('ship')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1M13 4v8m0 0 3-3m-3 3-3-3"/></svg>
                  @break
                @case('air')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
                  @break
                @case('combo')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                  @break
                @case('service')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  @break
                @case('hotel')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                  @break
                @case('carrental')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10l-2.7-3.5A2 2 0 0 0 13.7 5H6.3a2 2 0 0 0-1.6.8L2 9.5.5 11.1C.2 11.4 0 11.9 0 12.4V16c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                  @break
                @case('guide')
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                  @break
                @default
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
              @endswitch
            </span>

            <span class="sd-tour-related__headline">
              @if(!empty($card['kicker']))
                <span class="sd-tour-related__kicker">{{ $card['kicker'] }}</span>
              @endif
              <strong class="sd-tour-related__label">{{ $card['title'] ?? '' }}</strong>
            </span>
          </span>

          <span class="sd-tour-related__foot">
            @if(!empty($card['desc']))
              <span class="sd-tour-related__teaser">{{ $card['desc'] }}</span>
            @endif
            <span class="sd-tour-related__cta">{{ $card['cta'] ?? t('tour_related_cta') }}</span>
          </span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif
