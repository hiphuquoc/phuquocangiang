@if(!empty($item->options)&&$item->options->isNotEmpty())
    <div class="contentShip_item">
        <div id="gia-ve-vinwonders-phu-quoc" class="contentShip_item_title" data-toccontent="">
            <i class="fa-solid fa-money-check-dollar"></i>
            <h2 id="randomIdTocContent_1">{{ t('service_price_table_title', ['name' => $item->name ?? '']) }}</h2>
        </div>
        <div class="contentTour_item_text">
            <p>
                {!! t('service_price_note', ['hotline' => config('company.hotline')]) !!}
            </p>
            <table class="tableContentBorder" style="margin-bottom:0;font-size:0.95rem;">
                <thead>
                    <tr>
                        <th>{{ t('service_ticket_type_note') }}</th>
                        <th style="min-width:200px;">{{ t('service_ticket_price') }}</th>
                        <th>-</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item->options as $option)
                        <tr>
                            <td>
                                <div>
                                    {{ $option->name ?? null }}
                                </div>
                                @if(!empty($option->prices[0]->promotion))
                                    <div>
                                        {{ t('note_label') }}: {{ $option->prices[0]->promotion ?? null }}
                                    </div>
                                @endif
                                @if(!empty($option->prices)&&$option->prices->isNotEmpty())
                                    <div>
                                        {{ t('ship_applied_dates') }}:<br>
                                        @php
                                            if($option->prices[0]->date_start==$option->prices[0]->date_end){
                                                $xhtmlDate = date('d/m/Y', strtotime($option->prices[0]->date_start));
                                            }else {
                                                $xhtmlDate = date('d/m/Y', strtotime($option->prices[0]->date_start)).' - '.date('d/m/Y', strtotime($option->prices[0]->date_end));
                                            } 
                                        @endphp
                                        <div class="highLight">{{ $xhtmlDate }}</div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($option->prices)&&$option->prices->isNotEmpty())
                                    @foreach($option->prices as $price)
                                        <div>{{ $price->apply_age ?? null }} <span style="font-weight:700;color:rgb(0, 90, 180);font-size:1.1rem;">{!! !empty($price->price) ? format_price($price->price) : t('contact_price') !!}</span></div>
                                    @endforeach
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ booking_route('serviceBooking.form', [
                                    'service_location_id'   => $item->serviceLocation->id ?? 0,
                                    'service_info_id'       => $item->id ?? 0
                                ]) }}" class="buttonSecondary" style="min-width:125px;padding:0.75rem;"><i class="fa-solid fa-check"></i>{{ t('service_book_this_ticket') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="text-align:center;color:red;">
                <em>{{ t('service_price_vat_note') }}</em>
            </p>
        </div>
    </div>
@endif