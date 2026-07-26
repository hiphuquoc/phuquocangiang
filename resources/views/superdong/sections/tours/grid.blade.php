{{--
  Tour product grid — dùng chung trang chủ & trang danh mục tour.

  Props: $toursSection ['head' => [...], 'items' => [...]]
  Optional: $sectionId, $sectionClass, $showSectionHead, $gridClass
--}}
@php
  $toursSection = $toursSection ?? ['head' => [], 'items' => []];
  $toursHead = $toursSection['head'] ?? [];
  $toursItems = $toursSection['items'] ?? [];
  $sectionId = $sectionId ?? 'tours';
  $sectionClass = trim('sd-section sd-section--alt ' . ($sectionClass ?? ''));
  $showSectionHead = $showSectionHead ?? true;
  $gridClass = $gridClass ?? 'sd-product-grid sd-product-grid--4';
@endphp

@if(!empty($toursItems))
<section class="{{ $sectionClass }}" id="{{ $sectionId }}">
  <div class="sd-section__inner">
    @if($showSectionHead && (!empty($toursHead['title']) || !empty($toursHead['eyebrow'])))
      @include('superdong.ui.section-head', [
        'eyebrow' => $toursHead['eyebrow'] ?? 'Tour nổi bật',
        'title' => $toursHead['title'] ?? '',
        'desc' => $toursHead['desc'] ?? '',
        'linkHref' => !empty($toursHead['linkLabel']) ? ($toursHead['linkHref'] ?? '#'.$sectionId) : null,
        'linkLabel' => $toursHead['linkLabel'] ?? 'Xem tất cả tour →',
        'titleId' => $toursHead['titleId'] ?? null,
      ])
    @endif

    <div class="{{ $gridClass }}">
      @foreach($toursItems as $item)
        @include('superdong.ui.cards.product', array_merge($item, [
          'price' => $item['price'] ?? 'Liên hệ',
          'ctaLabel' => $item['ctaLabel'] ?? 'Xem chi tiết',
        ]))
      @endforeach
    </div>
  </div>
</section>
@endif
