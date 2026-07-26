@php
  $roomsHead = $rooms['head'] ?? [];
  $roomItems = $rooms['items'] ?? [];
@endphp

<section class="sd-section sd-hotel-rooms" id="hotel-rooms" aria-labelledby="hotel-rooms-title">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $roomsHead['eyebrow'] ?? t('kicker_hotel'),
      'title' => $roomsHead['title'] ?? t('hotel_choose_room'),
      'desc' => strip_tags((string) ($roomsHead['desc'] ?? '')),
      'titleId' => 'hotel-rooms-title',
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    <div class="sd-hotel-rooms__list">
      @foreach($roomItems as $roomCard)
        @include('main.hotel-v2.fragments.room-card', ['room' => $roomCard])
      @endforeach
    </div>
  </div>
</section>
