<table class="noResponsive" width="100%" style="border-collapse:collapse;">
    <tbody>
    <tr>
        <td colspan="2"  class="infoShipDeparture">
            <div class="infoShipDeparture_info">
                <div><span class="highLight">{{ $departure->departure }}</span>, {{ $departure->port_departure_province }}</div>
                <div><span class="highLight">{{ $departure->time_departure }}</span> {{ t('ship_depart_from_port') }} {{ $departure->port_departure }}, {{ $departure->port_departure_address }}</div>
            </div>
            <div class="infoShipDeparture_icon">
                <i class="fa-solid fa-ship"></i>
            </div>
            <div class="infoShipDeparture_info">
                <div><span class="highLight">{{ $departure->location }}</span>, {{ $departure->port_location_province }}</div>
                <div><span class="highLight">{{ $departure->time_arrive }}</span> {{ t('ship_arrive_at_port') }} {{ $departure->port_location }}, {{ $departure->port_location_address }}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span style="width:70px;display:inline-block;">{{ t('confirm_date') }}</span> : <span class="highLight">{{ \App\Helpers\DateAndTime::convertMktimeToDayOfWeek(strtotime($departure->date)) }}, {{ date('d-m-Y', strtotime($departure->date)) }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span style="width:70px;display:inline-block;">{{ t('ship_partner_label') }}</span> : <span class="highLight">{{ $departure->partner_name ?? '-' }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            @php
                $tmp    = [];
                if(!empty($departure->quantity_adult)) $tmp[] = '<span class="highLight">'.$departure->quantity_adult.'</span> '.t('pax_adult');
                if(!empty($departure->quantity_child)) $tmp[] = '<span class="highLight">'.$departure->quantity_child.'</span> '.t('pax_child_6_11');
                if(!empty($departure->quantity_old)) $tmp[] = '<span class="highLight">'.$departure->quantity_old.'</span> '.t('pax_senior_60plus');
                $xhtmlQuantity = implode(', ', $tmp);
            @endphp
            <span style="width:70px;display:inline-block;">{{ t('ship_quantity_label') }}</span> : <span>{!! $xhtmlQuantity !!}</span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span style="width:70px;display:inline-block;">{{ t('ship_ticket_type_label') }}</span> : <span class="highLight">{{ $departure->type ?? '-' }}</span>
        </td>
    </tr>
    </tbody>
</table>