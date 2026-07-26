@if(!empty($items))
<div class="sd-product-grid sd-product-grid--3 sd-hotel-grid" data-hotel-grid>
  @foreach($items as $card)
    <div class="sd-hotel-grid__item" data-filter-hotel="{{ $card['filterHotel'] ?? 'tat-ca-khach-san' }}">
      @include('superdong.ui.cards.product', array_merge($card, [
        'price' => $card['price'] ?? (string) config('currency.contact_label', 'Liên hệ'),
        'ctaLabel' => t('tour_related_cta'),
        'ctaHref' => $card['ctaHref'] ?? '#',
        'reveal' => false,
      ]))
    </div>
  @endforeach
</div>
<div id="js_filterHotel_hidden" hidden aria-hidden="true"></div>
@include('main.listing-v2.snippets.listing-filter-loader', [
  'emptyKey' => 'hotel_location_empty',
  'locationName' => $locationName ?? '',
])
@else
<div class="sd-listing-empty">
  {!! t('hotel_location_empty', ['name' => e($locationName ?? ''), 'brand' => e(config('main.name'))]) !!}
</div>
@endif
