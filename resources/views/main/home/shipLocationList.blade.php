@if(!empty($shipLocations)&&$shipLocations->isNotEmpty())
    <div class="shipLocationShowcase">
        @foreach($shipLocations as $shipLocation)
            <a href="/{{ $shipLocation->seo->slug_full ?? null }}" title="{{ $shipLocation->name ?? $shipLocation->seo->title ?? $shipLocation->seo->seo_title ?? null }}" class="shipLocationShowcase_item">
                <div class="shipLocationShowcase_item_image">
                    <img
                        src="{{ config('main.svg.loading_main') }}"
                        data-src="{{ $shipLocation->seo->image_small ?? $shipLocation->seo->image ?? config('admin.images.default_750x460') }}"
                        alt="{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}"
                        title="{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}"
                    />
                    <span class="shipLocationShowcase_item_image_badge">
                        <i class="fa-solid fa-ship" aria-hidden="true"></i>
                        {{ t('home_ship_featured_badge') }}
                    </span>
                </div>
                <div class="shipLocationShowcase_item_top">
                    <span class="shipLocationShowcase_item_icon" aria-hidden="true"><i class="fa-solid fa-compass"></i></span>
                    <h3>{{ $shipLocation->name ?? $shipLocation->seo->title ?? null }}</h3>
                    <span class="shipLocationShowcase_item_arrow" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                </div>
                <p class="shipLocationShowcase_item_desc">
                    {{ t('home_ship_item_desc', ['name' => $shipLocation->name ?? $shipLocation->seo->title ?? t('home_ship_default_destination')]) }}
                </p>
                <div class="shipLocationShowcase_item_meta">
                    <span>{{ t('home_ship_meta_eticket') }}</span>
                    <span>{{ t('home_ship_meta_support') }}</span>
                    <span>{{ t('home_ship_meta_fast_booking') }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endif