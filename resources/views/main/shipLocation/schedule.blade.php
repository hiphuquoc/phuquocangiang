<div class="contentShip_item">
    <div id="lich-tau-va-gia-ve-tau-cao-toc-phu-quoc" class="contentShip_item_title" data-toccontent>
        <i class="fa-solid fa-clock"></i>
        <h2>{{ t('ship_schedule_title', ['name' => $keyWord ?? '']) }}</h2>
    </div>
    <div class="contentTour_item_text">
        <p><a href="{{ URL::current() }}" title="{{ !empty($keyWord) ? t('ship_schedule_link_named', ['name' => $keyWord]) : t('ship_schedule_link') }}">{{ !empty($keyWord) ? t('ship_schedule_link_named', ['name' => $keyWord]) : t('ship_schedule_link') }}</a> {!! t('ship_schedule_intro', ['brand' => config('company.sortname')]) !!}</p>
        <p>{!! t('ship_price_note', ['hotline' => config('company.hotline')]) !!}</p>

        @if(!empty($schedule))
            {!! $schedule !!}
        @else
            @if(!empty($item->ships)&&$item->ships->isNotEmpty())
                <table class="tableContentBorder" style="font-size:0.95rem;">
                    <thead>
                        <tr>
                            <th style="min-width:210px;">{{ t('ship_partner_label') }}</th>
                            <th>{{ t('ship_depart_arrive') }}</th>
                            <th style="min-width:210px;">{{ t('ship_price_label') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($item->ships)&&$item->ships->isNotEmpty())
                            @foreach($item->ships as $ship)
                                @if(!empty($ship->prices)&&$ship->prices->isNotEmpty())
                                    @foreach($ship->prices as $price)
                                        @php
                                            $shipTime = \App\Http\Controllers\AdminShipPriceController::mergeArrayShipPrice($price->times);
                                            // dd($shipTime);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <h3 class="highLight">{{ $price->partner->name }}</h3>
                                                </div>
                                                <div>
                                                    {{ t('ship_applied_dates') }}:<br/>
                                                    @foreach($shipTime[0]['date'] as $date)
                                                        @php
                                                            $dateStart  = date('d/m/Y', strtotime($date['date_start']));
                                                            $dateEnd    = date('d/m/Y', strtotime($date['date_end']));
                                                        @endphp
                                                        @if($dateStart==$dateEnd)
                                                            <div class="highLight">{{ $dateStart }}</div>
                                                        @else 
                                                            <div class="highLight">{{ $dateStart }} - {{ $dateEnd }}</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($shipTime as $time)
                                                    <div class="oneLine">
                                                        <h3>{{ $time['name'] }}</h3>
                                                        @foreach($shipTime[0]['time'] as $t)
                                                            <div>{{ $t['time_departure'] }} - {{ $t['time_arrive'] }}</div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>
                                                <div><span class="highLight" style="font-size:1.1rem;">{!! format_price($price['price_adult']) !!}</span> /{{ t('pax_adult') }}</div>
                                                <div><span class="highLight" style="font-size:1.1rem;">{!! format_price($price['price_child']) !!}</span> /{{ t('ship_child_6_11') }}</div>
                                                <div><span class="highLight" style="font-size:1.1rem;">{!! format_price($price['price_old']) !!}</span> /{{ t('ship_over_60') }}</div>
                                                @if(!empty($price['price_vip']))
                                                    <div><span class="highLight" style="font-size:1.1rem;">{!! format_price($price['price_vip']) !!}</span> /{{ t('ship_vip_ticket') }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else 
                                    <tr>
                                        <td colspan="3">{!! t('ship_no_schedule', ['name' => $ship->name]) !!}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @else 
                            <tr>
                                <td colspan="3">{{ t('ship_no_schedule_any') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @endif
        @endif
    </div>
</div>