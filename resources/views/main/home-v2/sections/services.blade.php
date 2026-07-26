<!-- SERVICES SECTION -->
@php
  $servicesSection = $island['services'] ?? ['head' => [], 'items' => []];
  $servicesHead = $servicesSection['head'] ?? [];
  $servicesItems = $servicesSection['items'] ?? [];
@endphp
@if(!empty($servicesItems))
<section class="sd-section" id="services">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $servicesHead['eyebrow'] ?? 'Giải trí & trải nghiệm',
      'title' => $servicesHead['title'] ?? '',
      'desc' => $servicesHead['desc'] ?? '',
      'linkHref' => $servicesHead['linkHref'] ?? '#services',
      'linkLabel' => $servicesHead['linkLabel'] ?? 'Xem tất cả →',
    ])

    <div class="sd-product-grid sd-product-grid--4">
      @foreach($servicesItems as $item)
        @include('superdong.ui.cards.experience', array_merge($item, [
          'price' => $item['price'] ?? 'Liên hệ',
        ]))
      @endforeach
    </div>
  </div>
</section>
@endif
