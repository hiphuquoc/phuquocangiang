@php
    $flag = false;
    foreach($list as $ship) {
        if(!empty($ship->prices[0]->price_adult)) {
            $flag = true;
            break;
        }
    }
@endphp

<div class="shipGrid">
    @if($flag==true)
        @foreach($list as $ship)
            @php
                $arrayBrandShip         = [];
                foreach($ship->prices as $price){
                    if(!in_array($price->partner->name, $arrayBrandShip)) $arrayBrandShip[] = $price->partner->name;
                }
            @endphp
            @if(!empty($ship->prices[0]->price_adult))
                <div class="shipGrid_item">
                    <div class="shipGrid_item_image">
                        <a href="/{{ $ship->seo->slug_full ?? null }}" title="{{ $ship->name ?? $ship->seo->title ?? $ship->seo->seo_title ?? null }}">
                            <img src="{{ config('main.svg.loading_main') }}" data-src="{{ $ship->seo->image_small ?? $ship->seo->image ?? config('admin.images.default_750x460') }}" alt="{{ $ship->name ?? $ship->seo->title ?? $ship->seo->seo_title ?? null }}" title="{{ $ship->name ?? $ship->seo->title ?? $ship->seo->seo_title ?? null }}" />
                        </a>
                        <div class="shipGrid_item_image_left">{{ !empty($ship->prices[0]->times[0]->time_move) ? \App\Helpers\Time::convertMkToTimeMove($ship->prices[0]->times[0]->time_move) : null }}</div>
                        <div class="shipGrid_item_image_bottom">{{ implode(', ', $arrayBrandShip) }}</div>
                    </div>
                    <div class="shipGrid_item_content">
                        <div class="shipGrid_item_content_title maxLine_1">
                        <a href="/{{ $ship->seo->slug_full ?? null }}" title="{{ $ship->name ?? $ship->seo->title ?? null }}">
                            @if(!empty($itemHeading)&&$itemHeading=='h3')
                                <h3>{{ $ship->name ?? $ship->seo->title ?? null }}</h3>
                            @else 
                                <h2>{{ $ship->name ?? $ship->seo->title ?? null }}</h2>
                            @endif
                        </a>
                        </div>
                        <div class="shipGrid_item_content_table">
                        {{-- 2026 redesign: route line đồng bộ với airGrid (cardRouteLine).
                             Thay table-row "Cảng A ↔ Cảng B" cũ bằng pill + dashed line + ship icon. --}}
                        @php
                            $shipDepCity = t('ship_port_prefix').' '.($ship->departure->district->district_name ?? $ship->departure->province->province_name ?? t('unknown_short'));
                            $shipLocCity = t('ship_port_prefix').' '.($ship->location->district->district_name  ?? $ship->location->province->province_name  ?? t('unknown_short'));
                        @endphp
                        <div class="cardRouteLine shipGrid_item_route" aria-label="{{ t('ship_journey_aria', ['from' => $shipDepCity, 'to' => $shipLocCity]) }}">
                            <span class="cardRouteLine_city maxLine_1" title="{{ $shipDepCity }}">{{ $shipDepCity }}</span>
                            <span class="cardRouteLine_path" aria-hidden="true">
                                <i class="fa-solid fa-ship"></i>
                            </span>
                            <span class="cardRouteLine_city cardRouteLine_city--end maxLine_1" title="{{ $shipLocCity }}">{{ $shipLocCity }}</span>
                        </div>
                        @php
                            /* filter */
                            $arrayDeparture     = [];
                            $arrayPrice         = [
                                'price_adult'   => [], 
                                'price_child'   => [], 
                                'price_old'     => [], 
                                'price_vip'     => []
                            ];
                            foreach($ship->prices as $price){
                                /* xây dựng mảng price */
                                if(!in_array($price->price_adult, $arrayPrice['price_adult'])) {
                                    $arrayPrice['price_adult'][] = $price->price_adult;
                                    sort($arrayPrice['price_adult']);
                                }
                                if(!in_array($price->price_child, $arrayPrice['price_child'])) {
                                    $arrayPrice['price_child'][] = $price->price_child;
                                    sort($arrayPrice['price_child']);
                                }
                                if(!in_array($price->price_old, $arrayPrice['price_old'])) {
                                    $arrayPrice['price_old'][] = $price->price_old;
                                    sort($arrayPrice['price_old']);
                                }
                                if(!in_array($price->price_vip, $arrayPrice['price_vip'])) {
                                    if(!empty($price->price_vip)) $arrayPrice['price_vip'][] = $price->price_vip;
                                    sort($arrayPrice['price_vip']);
                                }
                                /* xây dựng mảng time */
                                foreach($price->times as $time){
                                    $arrayDeparture[$time->ship_from_sort.'-'.$time->ship_to_sort][] = $time->time_departure;
                                    /* sắp xếp mỗi lần thêm vào mảng */
                                    sort($arrayDeparture[$time->ship_from_sort.'-'.$time->ship_to_sort]);
                                }
                            }
                        @endphp
                        @foreach($arrayDeparture as $key => $value)
                            <div class="shipGrid_item_content_table_row" style="width:100%;{{ $loop->first ? 'margin-top:0.5rem' : null }}">
                                <div class="maxLine_1">
                                    {{ t('ship_leg_label') }} {{ $key }}
                                </div>
                                <div class="maxLine_1" style="color:#003B7B;">
                                    @foreach(array_unique($value) as $v)
                                        <span style="font-weight:bold;">{{ $v }}</span>{{ !$loop->last ? ' | ' : null }}
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <div class="shipGrid_item_content_table_row" style="margin-top:0.5rem;">
                            <div>
                                {{ t('pax_adult') }}: 
                            </div>
                            <div>
                                @if(count($arrayPrice['price_adult'])>1)
                                    <span class="text-price_500">{!! format_price($arrayPrice['price_adult'][0]) !!} - {!! format_price(end($arrayPrice['price_adult'])) !!}</span> /{{ t('ship_per_ticket') }}
                                @else
                                    <span class="text-price_500">{!! !empty($arrayPrice['price_adult'][0]) ? format_price($arrayPrice['price_adult'][0]).'</span> /'.t('ship_per_ticket') : '-' !!}
                                @endif
                            </div>
                        </div>
                        <div class="shipGrid_item_content_table_row" style="margin-top:0.15rem;">
                            <div>
                                {{ t('ship_child_6_11') }}: 
                            </div>
                            <div>
                                @if(count($arrayPrice['price_child'])>1)
                                    <span class="text-price_500">{!! format_price($arrayPrice['price_child'][0]) !!} - {!! format_price(end($arrayPrice['price_child'])) !!}</span> /{{ t('ship_per_ticket') }}
                                @else
                                    <span class="text-price_500">{!! !empty($arrayPrice['price_child'][0]) ? format_price($arrayPrice['price_child'][0]).'</span> /'.t('ship_per_ticket') : '-' !!}
                                @endif
                            </div>
                        </div>
                        <div class="shipGrid_item_content_table_row" style="margin-top:0.15rem;">
                            <div>
                                {{ t('ship_over_60') }}: 
                            </div>
                            <div>
                                @if(count($arrayPrice['price_old'])>1)
                                    <span class="text-price_500">{!! format_price($arrayPrice['price_old'][0]) !!} - {!! format_price(end($arrayPrice['price_old'])) !!}</span> /{{ t('ship_per_ticket') }}
                                @else
                                    <span class="text-price_500">{!! !empty($arrayPrice['price_old'][0]) ? format_price($arrayPrice['price_old'][0]).'</span> /'.t('ship_per_ticket') : '-' !!}
                                @endif
                            </div>
                        </div>
                        <div class="shipGrid_item_content_table_row" style="margin-top:0.15rem;">
                            <div>
                                {{ t('ship_vip_ticket') }}: 
                            </div>
                            <div>
                                @if(count($arrayPrice['price_vip'])>1)
                                    <span class="text-price_500">{!! format_price($arrayPrice['price_vip'][0]) !!} - {!! format_price(end($arrayPrice['price_vip'])) !!}</span> /{{ t('ship_per_ticket') }}
                                @else
                                    <span class="text-price_500">{!! !empty($arrayPrice['price_vip'][0]) ? format_price($arrayPrice['price_vip'][0]).'</span> /'.t('ship_per_ticket') : t('ship_not_available').'<sup></sup>' !!}
                                @endif
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="shipGrid_item_btn">
                        <a href="{{ booking_route('shipBooking.form', ['ship_port_departure_id' => $ship->portDeparture->id, 'ship_port_location_id' => $ship->portLocation->id]) }}" title="{{ t('ship_book_for', ['name' => $ship->name ?? $ship->seo->title ?? '']) }}">
                            <span>{{ t('ship_book_mobile') }}</span>
                            <i class="fa-solid fa-ticket" aria-hidden="true"></i>
                        </a>
                        <a href="/{{ $ship->seo->slug_full ?? null }}" title="{{ t('ship_view_detail', ['name' => $ship->name ?? $ship->seo->title ?? '']) }}">
                            <span>{{ t('view_detail') }}</span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            @endif
            @php
                if(!empty($limit)&&($loop->index+1)==$limit) break;
            @endphp
        @endforeach
    @else 
        <div style="color:rgb(0,123,255);">{{ t('ship_no_schedule_to', ['name' => $item->display_name ?? '', 'brand' => config('company.sortname')]) }}</div>
    @endif
 </div>
 @if(!empty($limit)&&$list->count()>$limit)
    <div class="viewMore viewMorePill">
        <a href="/{{ $link ?? null }}" title="{{ t('view_more') }}" class="viewMorePill_btn">
            <span class="viewMorePill_btn_label">{{ t('view_more') }}</span>
            <span class="viewMorePill_btn_icon" aria-hidden="true">
                <i class="fa-solid fa-arrow-right-long"></i>
            </span>
        </a>
    </div>
@endif