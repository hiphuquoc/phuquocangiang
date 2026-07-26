@php
  $optionsHead = $options['head'] ?? [];
  $optionItems = $options['items'] ?? [];
  $sectionId = $sectionId ?? 'product-options';
@endphp

@if(!empty($optionItems))
<section class="sd-section sd-hotel-rooms sd-product-options" id="{{ $sectionId }}" aria-labelledby="{{ $sectionId }}-title">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $optionsHead['eyebrow'] ?? '',
      'title' => $optionsHead['title'] ?? '',
      'desc' => strip_tags((string) ($optionsHead['desc'] ?? '')),
      'titleId' => $sectionId . '-title',
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    <div class="sd-product-options__list">
      @foreach($optionItems as $option)
        <article class="sd-product-option">
          <div class="sd-product-option__body">
            <h3 class="sd-product-option__title">{{ $option['title'] ?? '' }}</h3>
            @if(!empty($option['rows']))
              <ul class="sd-product-option__rows">
                @foreach($option['rows'] as $row)
                  <li>
                    <div class="sd-product-option__row-main">
                      @if(!empty($row['label']))
                        <span>{{ $row['label'] }}</span>
                      @endif
                      <strong>{!! $row['priceFormatted'] ?? '' !!}</strong>
                    </div>
                    @if(!empty($row['dates']))
                      <small>{{ $row['dates'] }}</small>
                    @endif
                    @if(!empty($row['note']))
                      <small>{{ $row['note'] }}</small>
                    @endif
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
          <div class="sd-product-option__action">
            @if(!empty($option['priceFormatted']))
              <div class="sd-product-option__price-wrap">
                <span class="sd-product-option__price-label">{{ t('price_from') }}</span>
                <div class="sd-product-option__price">{!! $option['priceFormatted'] !!}</div>
              </div>
            @endif
            <a href="{{ $option['bookingHref'] ?? '#' }}" class="sd-product-option__cta">
              {{ $option['ctaLabel'] ?? t('book_tour') }}
            </a>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>
@endif
