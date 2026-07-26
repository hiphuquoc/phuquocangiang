@if(!empty($items))
<div class="sd-product-grid sd-product-grid--3 sd-service-grid" data-service-grid>
  @foreach($items as $card)
    <div class="sd-service-grid__item" data-filter-ticket="{{ $card['filterTicket'] ?? 'tat-ca-ve' }}">
      @include('superdong.ui.cards.experience', array_merge($card, [
        'price' => $card['price'] ?? (string) config('currency.contact_label', 'Liên hệ'),
        'ctaLabel' => t('tour_related_cta'),
        'reveal' => false,
      ]))
    </div>
  @endforeach
</div>
<div id="js_filterService_hidden" hidden aria-hidden="true"></div>
@include('main.listing-v2.snippets.listing-filter-loader', [
  'emptyKey' => 'service_location_empty',
  'locationName' => $locationName ?? '',
])
@else
<div class="sd-listing-empty">
  {!! t('service_location_empty', ['name' => e($locationName ?? ''), 'brand' => e(config('main.name'))]) !!}
</div>
@endif
