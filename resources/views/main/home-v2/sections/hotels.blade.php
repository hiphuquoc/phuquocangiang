<!-- HOTELS SECTION -->
@php
  $hotelsSection = $island['hotels'] ?? ['head' => [], 'items' => []];
  $hotelsHead = $hotelsSection['head'] ?? [];
  $hotelsItems = $hotelsSection['items'] ?? [];
@endphp
@if(!empty($hotelsItems))
<section class="sd-section sd-section--alt" id="hotels">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $hotelsHead['eyebrow'] ?? 'Nghỉ dưỡng',
      'title' => $hotelsHead['title'] ?? '',
      'desc' => $hotelsHead['desc'] ?? '',
      'linkHref' => $hotelsHead['linkHref'] ?? (island_nav()['hotels'] ?? '#hotels'),
      'linkLabel' => $hotelsHead['linkLabel'] ?? 'Xem tất cả →',
    ])

    <div class="sd-product-grid sd-product-grid--4">
      @foreach($hotelsItems as $item)
        @include('superdong.ui.cards.product', array_merge($item, [
          'price' => $item['price'] ?? 'Liên hệ',
          'ctaLabel' => 'Xem chi tiết',
          'ctaHref' => $item['ctaHref'] ?? '#',
        ]))
      @endforeach
    </div>
  </div>
</section>
@endif
