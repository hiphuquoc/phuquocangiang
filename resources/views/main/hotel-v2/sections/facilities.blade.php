@php
  $facilities = $facilities ?? [];
  $tripadvisor = $facilities['tripadvisor'] ?? [];
  $traveloka = $facilities['traveloka'] ?? [];
@endphp

<section class="sd-section sd-section--alt sd-hotel-facilities" aria-labelledby="hotel-facilities-title">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => t('kicker_hotel'),
      'title' => t('hotel_facility_hotel'),
      'desc' => '',
      'titleId' => 'hotel-facilities-title',
      'titleTag' => 'h2',
      'reveal' => false,
      'compact' => true,
    ])

    @if(!empty($tripadvisor))
      <div class="sd-hotel-facilities__groups">
        @foreach($tripadvisor as $key => $group)
          @php
            $nameGroup = match ($key) {
              'hotel_info_feature' => t('hotel_facility_hotel'),
              'hotel_room_feature' => t('hotel_facility_room'),
              'hotel_room_type' => t('hotel_room_type'),
              default => $key,
            };
          @endphp
          <article class="sd-hotel-facilities__group">
            <h3 class="sd-hotel-facilities__group-title">{{ $nameGroup }}</h3>
            <ul class="sd-hotel-facilities__list">
              @foreach($group as $facility)
                <li class="sd-hotel-facilities__item">
                  @if(!empty($facility['icon']))
                    <span class="sd-hotel-facilities__icon">{!! $facility['icon'] !!}</span>
                  @else
                    <span class="sd-hotel-facilities__icon sd-hotel-facilities__icon--fallback" aria-hidden="true">✓</span>
                  @endif
                  <span>{{ $facility['name'] ?? '' }}</span>
                </li>
              @endforeach
            </ul>
          </article>
        @endforeach
      </div>
    @endif

    @if(!empty($traveloka))
      <div class="sd-hotel-facilities__traveloka">
        @foreach($traveloka as $key => $group)
          <article class="sd-hotel-facilities__group">
            <h3 class="sd-hotel-facilities__group-title">{{ $key }}</h3>
            <ul class="sd-hotel-facilities__list sd-hotel-facilities__list--chips">
              @foreach($group as $facility)
                <li class="sd-hotel-facilities__chip">
                  @if(!empty($facility['icon']))
                    <span class="sd-hotel-facilities__icon">{!! $facility['icon'] !!}</span>
                  @endif
                  <span>{{ $facility['name'] ?? '' }}</span>
                </li>
              @endforeach
            </ul>
          </article>
        @endforeach
      </div>
    @endif
  </div>
</section>
