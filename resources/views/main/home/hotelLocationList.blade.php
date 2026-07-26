@if(!empty($hotelLocations)&&$hotelLocations->isNotEmpty())
    @php
        $totalHotels = (int) $hotelLocations->sum('hotels_count');
        $totalLocations = (int) $hotelLocations->count();
    @endphp
    <section class="hotelDomesticShowcase" aria-labelledby="hotel-domestic-heading">
        <header class="hotelDomesticShowcase_intro">
            <div class="hotelDomesticShowcase_intro_bg" aria-hidden="true"></div>
            <div class="hotelDomesticShowcase_intro_mesh" aria-hidden="true"></div>
            <div class="hotelDomesticShowcase_intro_inner">
                <div class="hotelDomesticShowcase_intro_head">
                    <span class="hotelDomesticShowcase_intro_label">{{ t('home_hotel_intro_label') }}</span>
                    <h2 id="hotel-domestic-heading">{{ t('home_hotel_intro_heading') }}</h2>
                </div>
                <p class="hotelDomesticShowcase_intro_lead">{{ t('home_hotel_intro_lead') }}</p>
                <div class="hotelDomesticShowcase_intro_stats" role="list">
                    <div class="hotelDomesticShowcase_intro_stats_item" role="listitem">
                        <span class="hotelDomesticShowcase_intro_stats_icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                        <div class="hotelDomesticShowcase_intro_stats_text">
                            <span class="hotelDomesticShowcase_intro_stats_value">{{ $totalLocations }}</span>
                            <span class="hotelDomesticShowcase_intro_stats_label">{{ t('home_hotel_stats_destinations') }}</span>
                        </div>
                    </div>
                    <div class="hotelDomesticShowcase_intro_stats_divider" aria-hidden="true"></div>
                    <div class="hotelDomesticShowcase_intro_stats_item" role="listitem">
                        <span class="hotelDomesticShowcase_intro_stats_icon" aria-hidden="true"><i class="fa-solid fa-bed"></i></span>
                        <div class="hotelDomesticShowcase_intro_stats_text">
                            <span class="hotelDomesticShowcase_intro_stats_value">{{ number_format($totalHotels) }}</span>
                            <span class="hotelDomesticShowcase_intro_stats_label">{{ t('home_hotel_stats_options') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        @foreach($hotelLocations as $hotelLocation)
            @php
                $locName = $hotelLocation->display_name ?? $hotelLocation->name ?? $hotelLocation->seo->title ?? t('home_hotel_default_destination');
                $locCount = (int) ($hotelLocation->hotels_count ?? 0);
            @endphp
            <a
                href="/{{ $hotelLocation->seo->slug_full ?? null }}"
                title="{{ $locName }}"
                class="hotelDomesticShowcase_item {{ $loop->first ? 'isHero' : null }}"
            >
                <div class="hotelDomesticShowcase_item_image">
                    <img
                        src="{{ config('main.svg.loading_main') }}"
                        data-src="{{ $hotelLocation->seo->image_small ?? $hotelLocation->seo->image ?? config('admin.images.default_750x460') }}"
                        alt=""
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        decoding="async"
                        fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                    />
                    <span class="hotelDomesticShowcase_item_badge">
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        @if($locCount > 0)
                            {{ t('home_hotel_count_options', ['count' => number_format($locCount)]) }}
                        @else
                            {{ t('home_hotel_view_suggestions') }}
                        @endif
                    </span>
                </div>
                <div class="hotelDomesticShowcase_item_content">
                    <span class="hotelDomesticShowcase_item_kicker">{{ t('home_hotel_kicker_destination') }}</span>
                    <div class="hotelDomesticShowcase_item_title">
                        <h3>{{ $locName }}</h3>
                    </div>
                    <p class="hotelDomesticShowcase_item_desc">
                        @if($locCount > 0)
                            {{ t('home_hotel_item_desc_count', ['name' => $locName, 'count' => number_format($locCount)]) }}
                        @else
                            {{ t('home_hotel_item_desc_empty', ['name' => $locName]) }}
                        @endif
                    </p>
                    <span class="hotelDomesticShowcase_item_cta">
                        <span class="hotelDomesticShowcase_item_cta_label">{{ t('view_detail') }}</span>
                        <span class="hotelDomesticShowcase_item_arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right-long"></i></span>
                    </span>
                </div>
            </a>
        @endforeach
    </section>
@endif
