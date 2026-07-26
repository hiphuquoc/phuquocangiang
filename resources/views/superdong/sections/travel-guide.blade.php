<!-- CẨM NANG & LỊCH TRÌNH — travelGuide spread -->
@php
  $tgSectionId = 'sd-travel-guide-title';
  $guidesSection = $island['guides'] ?? null;
  $islandLabel = $island['name'] ?? island_name();
  $collageImages = $guidesSection['images'] ?? [
    config('admin.images.default_750x460'),
    config('admin.images.default_750x460'),
    config('admin.images.default_750x460'),
  ];
  $guides = $guidesSection['items'] ?? [];
  $guidesHead = $guidesSection['head'] ?? [];
@endphp
@if(!empty($guides))
<section class="sd-section sd-section--alt travelGuide" id="guide" aria-labelledby="{{ $tgSectionId }}">
  <div class="sd-section__inner">
    <div class="travelGuide_inner">
      <div class="travelGuide_collage" aria-hidden="true">
        <div class="travelGuide_collage_stage" data-tg-stage>
          <figure class="travelGuide_collage_card travelGuide_collage_card--back">
            <img src="{{ $collageImages[0] ?? '' }}" alt="" loading="lazy" decoding="async">
            <span class="travelGuide_collage_tape"></span>
          </figure>
          <figure class="travelGuide_collage_card travelGuide_collage_card--mid">
            <img src="{{ $collageImages[1] ?? '' }}" alt="" loading="lazy" decoding="async">
            <span class="travelGuide_collage_tape"></span>
          </figure>
          <figure class="travelGuide_collage_card travelGuide_collage_card--front">
            <img src="{{ $collageImages[2] ?? '' }}" alt="" loading="lazy" decoding="async">
            <span class="travelGuide_collage_tape"></span>
          </figure>

          <span class="travelGuide_collage_compass">
            <svg viewBox="0 0 64 64" fill="none" aria-hidden="true" focusable="false">
              <circle cx="32" cy="32" r="29" stroke="currentColor" stroke-width="1.4" stroke-dasharray="2 4" opacity="0.55"/>
              <circle cx="32" cy="32" r="22" fill="#fff" stroke="currentColor" stroke-width="1.8"/>
              <line x1="32" y1="11.5" x2="32" y2="14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="32" y1="50" x2="32" y2="52.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="11.5" y1="32" x2="14" y2="32" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              <line x1="50" y1="32" x2="52.5" y2="32" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              <path d="M32 15.5 L36 32 L32 40 L28 32 Z" fill="currentColor"/>
              <path d="M32 48.5 L28 32 L32 24 L36 32 Z" fill="currentColor" opacity="0.28"/>
              <circle cx="32" cy="32" r="2.4" fill="#fff" stroke="currentColor" stroke-width="1.4"/>
            </svg>
          </span>

          <span class="travelGuide_collage_stamp">
            <span class="travelGuide_collage_stamp_inner">
              <span>Cẩm</span>
              <span class="travelGuide_collage_stamp_divider"></span>
              <span>nang</span>
            </span>
          </span>
        </div>
      </div>

      <div class="travelGuide_content">
        <div class="sd-section-head__eyebrow-wrapper">
          <span class="sd-section-head__accent-dot" aria-hidden="true"></span>
          <span class="sd-section-head__eyebrow">Cẩm nang &amp; lịch trình</span>
        </div>
        <h2 class="sd-section-head__title" id="{{ $tgSectionId }}">{!! $guidesHead['title'] ?? ('Bí kíp khám phá ' . $islandLabel) !!}</h2>
        <p class="sd-section-head__desc" style="margin-bottom: 1.5rem;">{{ $guidesHead['desc'] ?? ('Lịch trình gợi ý, mẹo đi lại và ẩm thực — tổng hợp cho chuyến đi ' . $islandLabel . '.') }}</p>
        <ul class="travelGuide_list" role="list">
          @foreach($guides as $idx => $guide)
            <li class="travelGuide_list_item" style="--cg-delay: {{ $idx * 70 }}ms">
              <a href="{{ $guide['href'] ?? '#guide' }}" class="travelGuide_list_link" title="{{ $guide['title'] ?? '' }}">
                <span class="travelGuide_list_index">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="travelGuide_list_meta">
                  <span class="travelGuide_list_eyebrow">{{ $islandLabel }} guide</span>
                  <span class="travelGuide_list_title">{{ $guide['title'] ?? '' }}</span>
                </span>
                <span class="travelGuide_list_arrow" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                  </svg>
                </span>
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</section>
@endif
