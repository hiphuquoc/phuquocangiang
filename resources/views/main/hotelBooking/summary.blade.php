@if(!empty($data['name'])&&!empty($data['phone']))
    <div class="shipBookingTotalBox_row">
        <div>{{ $data['name'] }} - {{ $data['phone'] }} @if(!empty($data['zalo'])&&$data['zalo']==$data['phone']) (Zalo)@endif</div>
        @if(!empty($data['email']))
        <div>@if(!empty($data['zalo'])&&$data['zalo']!=$data['phone']) Zalo: {{ $data['zalo'] }} - @endif{{ $data['email'] }}</div>
        @endif
    </div>
@endif
<div class="shipBookingTotalBox_row">
    <div class="shipBookingTotalBox_row_title"><i class="fa-solid fa-hotel"></i>{{ $data['hotel_name'] ?? null }}</div>
    <div>
        {{ $data['quantity'] }} {{ $room->name ?? null }}
        @if($room->prices[0]->breakfast==1||$room->prices[0]->given==1)
        @php
            $tmp            = [];
            if($room->prices[0]->breakfast==1) $tmp[] = t('hotel_breakfast');
            if($room->prices[0]->given==1) $tmp[] = t('hotel_pickup');
            $xhtmlInclude   = implode(' + ', $tmp);
        @endphp
        ({{ $xhtmlInclude }})
        @endif
    </div>
    <table class="noResponsive">
        <tbody>
        <tr>
            <td><span class="highLight">{{ $data['quantity'] }}</span> {{ t('hotel_rooms') }} * <span class="highLight">{{ $data['number_night'] }}</span> {{ t('hotel_nights_short') }} * {{ number_format($room->prices[0]->price) }}</td>
            <td style="text-align:right;">{{ number_format($data['quantity']*$data['number_night']*$room->prices[0]->price) }}</td>
        </tr>
        </tbody>
    </table>
</div>
<div class="shipBookingTotalBox_row">
    <table class="noResponsive">
        <tbody>
        <tr>
            <td colspan="2">{{ t('summary_total') }}</td>
            <td style="text-align:right;"><span style="font-weight:700;color:#E74C3C;letter-spacing:0.3px;">{{ number_format($data['number_night']*$data['quantity']*$room->prices[0]->price) }}</span></td>
        </tr>
        </tbody>
    </table>
</div>