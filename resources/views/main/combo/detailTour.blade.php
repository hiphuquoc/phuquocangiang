<div class="detailTourBox">
    <table class="noResponsive">
        <tbody>
            <tr>
                <td colspan="2"><h2>{{ $item->name }}</h2></td>
            </tr>
            @if(!empty($item->code))
                <tr>
                    <td>{{ t('combo_code') }}</td>
                    <td><h3 style="font-size:1.05rem;">{{ $item->code }}</h3></td>
                </tr>
            @endif
            @if(!empty($item->days))
                @if($item->days>1)
                    <tr>
                        <td>{{ t('tour_duration') }}</td>
                        <td><h3 style="font-size:1.05rem;">{{ t('tour_days_nights', ['days' => $item->days, 'nights' => $item->nights]) }}</h3></td>
                    </tr>
                @else 
                    <tr>
                        <td>{{ t('tour_duration') }}</td>
                        <td><h3 style="font-size:1.05rem;">{{ $item->time_start }} - {{ $item->time_end }}</h3></td>
                    </tr>
                @endif
            @endif
            @if(!empty($item->departure_schedule))
                <tr>
                    <td>{{ t('tour_schedule') }}</td>
                    <td><h3 style="font-size:1.05rem;">{{ $item->departure_schedule }}</h3></td>
                </tr>
            @endif
            @php
                $xhtmlDeparture             = [];
                foreach($item->options as $option){
                    foreach($option->prices as $price){
                        if(!in_array($price->departure->display_name, $xhtmlDeparture)) $xhtmlDeparture[] = $price->departure->display_name;
                    }
                }
                $xhtmlDeparture             = implode(', ', $xhtmlDeparture);
            @endphp
            <tr>
                <td>{{ t('tour_depart_from') }}</td>
                <td><h3 style="font-size:1.05rem;">{{ $xhtmlDeparture }}</h3></td>
            </tr>
        </tbody>
    </table>
</div>