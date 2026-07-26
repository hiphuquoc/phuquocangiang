@php
  $item = $item ?? null;
@endphp

@if($item)
<section class="sd-section sd-product-details" id="tour-details" aria-labelledby="tour-details-title">
  <div class="sd-section__inner">
    <h2 class="sd-product-details__sr" id="tour-details-title">{{ t('tour_highlights') }}</h2>

    @if(!empty($item->content->special_content))
      <article class="sd-product-details__block" id="diem-noi-bat-chuong-trinh-tour">
        <h3>{{ t('tour_highlights') }}</h3>
        <div class="sd-product-details__html">{!! $item->content->special_content !!}</div>
      </article>
    @endif

    @if(!empty($item->timetables) && $item->timetables->isNotEmpty())
      <article class="sd-product-details__block" id="lich-trinh-tour-du-lich">
        <h3>{{ t('tour_itinerary') }}</h3>
        <div class="sd-product-details__tabs" data-tour-itinerary-tabs>
          <button type="button" class="is-active" data-tab="full">{{ t('tour_itinerary_full') }}</button>
          <button type="button" data-tab="short">{{ t('tour_itinerary_short') }}</button>
        </div>
        <div class="sd-product-details__itinerary" data-tab-panel="full">
          @foreach($item->timetables as $timetable)
            <details class="sd-product-details__day" @if($loop->first) open @endif>
              <summary>{{ $timetable->title }}</summary>
              <div class="sd-product-details__html">{!! $timetable->content !!}</div>
            </details>
          @endforeach
        </div>
        <div class="sd-product-details__itinerary" data-tab-panel="short" hidden>
          @foreach($item->timetables as $timetable)
            @if(!empty($timetable->content_sort))
              <details class="sd-product-details__day">
                <summary>{{ $timetable->title }}</summary>
                <div class="sd-product-details__html">{!! $timetable->content_sort !!}</div>
              </details>
            @endif
          @endforeach
        </div>
      </article>
    @endif

    @foreach([
      ['field' => 'policy_child', 'id' => 'chinh-sach-tre-em-tour', 'title' => t('tour_policy_child')],
      ['field' => 'include', 'id' => 'tour-bao-gom-va-khong-bao-gom', 'title' => t('tour_includes')],
      ['field' => 'not_include', 'id' => 'tour-chua-bao-gom', 'title' => t('tour_not_includes')],
      ['field' => 'policy_cancel', 'id' => 'chinh-sach-huy-tour', 'title' => t('tour_policy_cancel', ['name' => $item->name ?? ''])],
      ['field' => 'note', 'id' => 'luu-y-khi-tham-gia-chuong-trinh-tour', 'title' => t('tour_notes', ['name' => $item->name ?? ''])],
    ] as $block)
      @php $html = $item->content->{$block['field']} ?? null; @endphp
      @if(!empty($html))
        <article class="sd-product-details__block" id="{{ $block['id'] }}">
          <h3>{{ $block['title'] }}</h3>
          <div class="sd-product-details__html">{!! $html !!}</div>
        </article>
      @endif
    @endforeach
  </div>
</section>
@endif
