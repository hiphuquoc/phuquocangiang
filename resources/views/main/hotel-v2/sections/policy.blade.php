@php
  $policy = $policy ?? [];
@endphp

<section class="sd-section sd-section--alt sd-hotel-policy" aria-labelledby="hotel-policy-title">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => t('kicker_hotel'),
      'title' => $policy['title'] ?? t('hotel_policy_section'),
      'desc' => '',
      'titleId' => 'hotel-policy-title',
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    <div class="sd-hotel-policy__content sd-listing-content__prose">
      {!! $policy['html'] ?? '' !!}
    </div>
  </div>
</section>
