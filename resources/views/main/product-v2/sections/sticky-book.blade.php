@php
  $bookingHref = $bookingHref ?? '#';
  $ctaLabel = $ctaLabel ?? t('book_tour');
  $priceFormatted = $priceFormatted ?? null;
  $priceLabel = $priceLabel ?? t('price_from');
@endphp

<div class="sd-product-sticky-book" data-product-sticky-book>
  @if(!empty($priceFormatted))
    <div class="sd-product-sticky-book__meta">
      <span class="sd-product-sticky-book__label">{{ $priceLabel }}</span>
      <div class="sd-product-sticky-book__price">{!! $priceFormatted !!}</div>
    </div>
  @endif
  <a href="{{ $bookingHref }}" class="sd-product-sticky-book__cta">{{ $ctaLabel }}</a>
</div>
