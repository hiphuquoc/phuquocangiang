@php
  $related = $related ?? ['head' => [], 'items' => []];
  $items = $related['items'] ?? [];
  $head = $related['head'] ?? [];
@endphp

@if(!empty($items))
<section class="sd-section sd-product-related" id="tour-related" aria-labelledby="tour-related-title">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $head['eyebrow'] ?? t('kicker_tour_list'),
      'title' => $head['title'] ?? 'Tour liên quan',
      'desc' => strip_tags((string) ($head['desc'] ?? '')),
      'titleId' => 'tour-related-title',
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    <div class="sd-product-grid sd-product-grid--3">
      @foreach($items as $card)
        @include('superdong.ui.cards.product', array_merge($card, [
          'price' => $card['price'] ?? 'Liên hệ',
          'ctaLabel' => $card['ctaLabel'] ?? 'Xem chi tiết',
        ]))
      @endforeach
    </div>
  </div>
</section>
@endif