@php
  $room = $room ?? [];
  $images = $room['images'] ?? [];
  $roomId = (int) ($room['id'] ?? 0);
@endphp

<article class="sd-hotel-room" data-hotel-room-id="{{ $roomId }}">
  @if(!empty($images))
    <div class="sd-hotel-room__media">
      <button
        type="button"
        class="sd-hotel-room__gallery-trigger"
        data-hotel-room-open="{{ $roomId }}"
        aria-label="{{ t('view_more') }}"
      >
        <img
          src="{{ $images[0]['src'] }}"
          alt="{{ $images[0]['alt'] ?? ($room['roomName'] ?? '') }}"
          width="640"
          height="400"
          loading="lazy"
          decoding="async"
        >
        @if(count($images) > 1)
          <span class="sd-hotel-room__photo-count">+{{ count($images) - 1 }}</span>
        @endif
      </button>
    </div>
  @endif

  <div class="sd-hotel-room__body">
    <h3 class="sd-hotel-room__title">{{ $room['roomName'] ?? '' }}</h3>

    <ul class="sd-hotel-room__facts">
      @if(!empty($room['size']))
        <li>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 5h18v14H3z" opacity=".15"/><path fill="currentColor" d="M5 7h14v10H5z"/></svg>
          {{ $room['size'] }} m²
        </li>
      @endif
      @if(!empty($room['maxPeople']))
        <li>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 8a7 7 0 0 1 14 0z"/></svg>
          {{ $room['maxPeople'] }} {{ t('hotel_max_people') }}
        </li>
      @endif
      <li>
        @if(!empty($room['breakfast']))
          {{ t('hotel_includes_breakfast') }}
        @else
          {{ t('hotel_no_breakfast') }}
        @endif
      </li>
      @if(!empty($room['pickup']))
        <li>{{ t('hotel_includes_pickup') }}</li>
      @endif
    </ul>

    @if(!empty($room['beds']))
      <p class="sd-hotel-room__beds">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 11V7a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v4H4zm16 2v5h-2v-3H6v3H4v-5h16z"/></svg>
        {{ $room['beds'] }}
      </p>
    @endif

    @if(!empty($room['facilities']))
      <ul class="sd-hotel-room__facilities">
        @foreach($room['facilities'] as $facility)
          <li>
            @if(!empty($facility['icon']))
              <span class="sd-hotel-room__facility-icon">{!! $facility['icon'] !!}</span>
            @endif
            <span>{{ $facility['name'] ?? '' }}</span>
          </li>
        @endforeach
        @if(!empty($room['facilityExtra']))
          <li class="sd-hotel-room__facilities-more">
            + {{ t('hotel_other_facilities', ['count' => $room['facilityExtra']]) }}
          </li>
        @endif
      </ul>
    @endif
  </div>

  <div class="sd-hotel-room__action">
    @if(!empty($room['saleOff']) && !empty($room['priceOldFormatted']))
      <div class="sd-hotel-room__price-old">
        <span>{!! $room['priceOldFormatted'] !!}</span>
        <em>-{{ $room['saleOff'] }}%</em>
      </div>
    @endif
    <div class="sd-hotel-room__price">{!! $room['priceFormatted'] ?? '' !!}</div>
    <a href="{{ $room['bookingHref'] ?? '#' }}" class="sd-hotel-room__cta">{{ t('book_room') }}</a>
  </div>

  @if(!empty($images))
    <template id="hotel-room-modal-{{ $roomId }}">
      <div class="sd-hotel-room-modal__gallery">
        @foreach($images as $image)
          <figure class="sd-hotel-room-modal__figure">
            <img src="{{ $image['src'] }}" alt="{{ $image['alt'] ?? ($room['roomName'] ?? '') }}" loading="lazy" decoding="async">
          </figure>
        @endforeach
      </div>
      <div class="sd-hotel-room-modal__info">
        <h4>{{ $room['roomName'] ?? '' }}</h4>
        @if(!empty($room['size']))
          <p>{{ $room['size'] }} m² · {{ $room['maxPeople'] ?? '' }} {{ t('hotel_max_people') }}</p>
        @endif
        @if(!empty($room['beds']))
          <p>{{ $room['beds'] }}</p>
        @endif
      </div>
    </template>
  @endif
</article>
