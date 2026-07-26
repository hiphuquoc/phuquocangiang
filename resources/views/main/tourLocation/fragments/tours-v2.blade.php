@if(!empty($items))
  <div id="js_filterTour_parent" class="sd-product-grid sd-product-grid--4 sd-tour-grid" data-tour-grid>
    @foreach($items as $item)
      <div class="sd-tour-grid__item" data-filter-day="{{ $item['filterDay'] ?? 'tour-trong-ngay' }}">
        @include('superdong.ui.cards.product', array_merge($item, [
          'price' => $item['price'] ?? (string) config('currency.contact_label', 'Liên hệ'),
          'ctaLabel' => 'Xem chi tiết',
          'reveal' => false,
        ]))
      </div>
    @endforeach
  </div>
  <div id="js_filterTour_hidden" hidden aria-hidden="true"></div>
  @include('main.tourLocation-v2.snippets.tour-filter-loader')
@else
  <div class="sd-tour-location-empty">
    {!! t('tour_list_empty', ['name' => e($item->display_name ?? ''), 'brand' => e(config('main.name'))]) !!}
  </div>
@endif
