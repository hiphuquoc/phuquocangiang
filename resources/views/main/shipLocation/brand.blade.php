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
            @php
                $dataPartnerUnique  = [];
                foreach($item->ships as $ship){
                    foreach($ship->partners as $partner) {
                        if(!in_array($partner->infoPartner->id, $dataPartnerUnique)) {
                            $dataPartnerUnique[$partner->infoPartner->id]                       = $partner->infoPartner->toArray();
                            $dataPartnerUnique[$partner->infoPartner->id]['seo_description']    = $partner->infoPartner->seo->seo_description;
                            $dataPartnerUnique[$partner->infoPartner->id]['slug_full']          = $partner->infoPartner->seo->slug_full;
                        }
                    }
                }
            @endphp
            @foreach($dataPartnerUnique as $partner)
                <div class="shipPartnerBox_item">
                    <a href="/{{ $partner['slug_full'] ?? null }}" title="{{ $partner['name'] ?? null }}" class="shipPartnerBox_item_image">
                        <img src="{{ config('main.svg.loading_main') }}" data-src="{{ $partner['company_logo'] }}" alt="{{ $partner['name'] ?? null }}" title="{{ $partner['name'] ?? null }}" />
                    </a>
                    <div class="shipPartnerBox_item_content">
                        <a href="/{{ $partner['slug_full'] ?? null }}" title="{{ $partner['name'] ?? null }}"><h3>{{ $partner['name'] ?? null }}</h3></a>
                        <div class="shipPartnerBox_item_content_desc maxLine_4">{{ $partner['seo_description'] ?? null }}</div>
                    </div>
                </div>  
            @endforeach
        </div>
    </div>
</div>