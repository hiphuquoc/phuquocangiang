<!-- Hãng tàu -->
<div class="contentShip_item">
    <div id="hang-tau-cao-toc-phu-quoc" class="contentShip_item_title" data-tocContent>
        <i class="fa-solid fa-award"></i>
        <h2>{{ t('ship_brand_title', ['name' => $keyWord ?? t('ship_high_speed_generic')]) }}</h2>
    </div>
    <div class="contentTour_item_text">
        <p>{!! t('ship_brand_intro') !!}</p>
        <ul class="listStyle">
            <li>{{ t('ship_brand_criteria_1') }}</li>
            <li>{{ t('ship_brand_criteria_2') }}</li>
            <li>{{ t('ship_brand_criteria_3') }}</li>
            <li>{{ t('ship_brand_criteria_4') }}</li>
            <li>{{ t('ship_brand_criteria_5') }}</li>
            <li>{{ t('ship_brand_criteria_6') }}</li>
        </ul>
        <p>{!! t('ship_brand_outro', ['name' => $keyWord ?? t('ship_high_speed_generic')]) !!}</p>
        <div class="shipPartnerBox">
            @foreach($item->partners as $partner)
                <div class="shipPartnerBox_item">
                    <a href="/{{ $partner->infoPartner->seo->slug_full ?? null }}" title="{{ $partner->infoPartner->name ?? $partner->infoPartner->seo->title ?? $partner->infoPartner->seo->seo_title ?? null }}" class="shipPartnerBox_item_image">
                        <img src="{{ config('main.svg.loading_main') }}" data-src="{{ $partner->infoPartner->seo->image_small ?? $partner->infoPartner->seo->image ?? config('admin.images.default_750x460') }}" alt="{{ $partner->infoPartner->name ?? $partner->infoPartner->seo->title ?? $partner->infoPartner->seo->seo_title ?? null }}" title="{{ $partner->infoPartner->name ?? $partner->infoPartner->seo->title ?? $partner->infoPartner->seo->seo_title ?? null }}" />
                    </a>
                    <div class="shipPartnerBox_item_content">
                        <a href="/{{ $partner->infoPartner->seo->slug_full ?? null }}" title="{{ $partner->infoPartner->name ?? $partner->infoPartner->seo->title ?? $partner->infoPartner->seo->seo_title ?? null }}">
                            <h3>{{ $partner->infoPartner->name ?? $partner->infoPartner->seo->title ?? null }}</h3>
                        </a>
                        <div class="shipPartnerBox_item_content_desc maxLine_4">{{ $partner->infoPartner->seo->seo_description ?? null }}</div>
                    </div>
                </div>  
            @endforeach
        </div>
    </div>
</div>