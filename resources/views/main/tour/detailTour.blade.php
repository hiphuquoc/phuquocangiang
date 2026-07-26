<div class="detailTourBox">
    <table class="noResponsive">
        <tbody>
            <tr>
                <td colspan="2"><h2>{{ $item->name }}</h2></td>
            </tr>
            @if(!empty($item->code))
                <tr>
                    <td>{{ t('tour_code') }}</td>
                    <td><h3 style="font-size:1rem;">{{ $item->code }}</h3></td>
                </tr>
            @endif
            @if(!empty($item->days))
                @if($item->days>1)
                    <tr>
                        <td>{{ t('tour_duration') }}</td>
                        <td><h3 style="font-size:1rem;">{{ t('tour_days_nights', ['days' => $item->days, 'nights' => $item->nights]) }}</h3></td>
                    </tr>
                @else 
                    <tr>
                        <td>{{ t('tour_duration') }}</td>
                        <td><h3 style="font-size:1rem;">{{ $item->time_start }} - {{ $item->time_end }}</h3></td>
                    </tr>
                @endif
            @endif
            @if(!empty($item->departure_schedule))
                <tr>
                    <td>{{ t('tour_schedule') }}</td>
                    <td><h3 style="font-size:1rem;">{{ $item->departure_schedule }}</h3></td>
                </tr>
            @endif
            @if(!empty($item->transport))
                <tr>
                    <td>{{ t('tour_transport') }}</td>
                    <td><h3 style="font-size:1rem;">{{ $item->transport }}</h3></td>
                </tr>
            @endif
            <tr>
                <td>{{ t('tour_depart_from') }}</td>
                <td><h3 style="font-size:1rem;">{{ $item->pick_up }}</h3></td>
            </tr>
        </tbody>
    </table>
</div>