@php
    $tmp                = \App\Models\HotelLocation::select('*')
                            ->whereHas('hotels.rooms.prices', function ($query) {
                                
                            })
                            ->with('region')
                            ->get();
    $dataHotelLocation   = [];
    foreach($tmp as $hotelLocation){
        $dataHotelLocation[$hotelLocation->region->name][] = $hotelLocation;
    }
    $cookieHotelBooking = \App\Http\Controllers\CookieController::getCookie('hotel_booking');
@endphp
<div class="bookFormSortHotel">
    <div class="bookFormSortHotel_top">
        <div class="bookFormSortHotel_top_item">
            <div class="inputWithLabelInside location">
                <label for="hotel_form_sort_location">{{ t('form_destination') }}</label>
                <select id="hotel_form_sort_location" class="select2 form-select select2-hidden-accessible" name="hotel_location_id" tabindex="-1" aria-hidden="true">
                    @foreach($dataHotelLocation as $region => $hotelLocations)
                        <optgroup label="{{ $region }}, {{ t('country_vietnam') }}">
                        @foreach($hotelLocations as $hotelLocation)
                            @php
                                $selected	= null;
                                if(!empty($item->id)&&$item->id==$hotelLocation->id) $selected = 'selected';
                            @endphp
                            <option value="{{ $hotelLocation->id }}" {{ $selected }}>{{ $hotelLocation->display_name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>
        <div class="bookFormSortHotel_top_item">
            
            @include('main.hotelBooking.inputQuantityAndRoom', ['dataForm' => $cookieHotelBooking ])

        </div>
            
        
    </div>
    <div class="bookFormSortHotel_bottom">
        <div class="bookFormSortHotel_bottom_item">
            
            <div class="inputWithLabelInside date">
                <label class="maxLine_1" for="hotel_form_sort_check_in">{{ t('form_check_in') }}</label>
                <input type="text" class="form-control flatpickr-basic flatpickr-input active" id="hotel_form_sort_check_in" name="check_in" value="{{ $cookieHotelBooking['check_in'] ?? date('Y-m-d', time() + 86400) }}" aria-label="{{ t('form_hotel_check_in_aria') }}" readonly="readonly" required />
            </div>
            <div class="inputWithLabelInside night">
                <label for="hotel_form_sort_quantity">{{ t('hotel_night_count') }}</label>
                <select class="select2 form-select select2-hidden-accessible" id="number_night" name="number_night">
                    @for($i=1;$i<31;++$i)
                        @php
                            $selected = null;
                            if(!empty($cookieHotelBooking['number_night'])&&$cookieHotelBooking['number_night']==$i) $selected = 'selected';
                        @endphp
                        <option value="{{ $i }}" {{ $selected }}>{{ t('hotel_nights', ['count' => $i]) }}</option>
                    @endfor
                </select>
            </div>
            {{-- <div class="inputWithLabelInside date disabled" style="background: #dee2e6;">
                <label for="check_out">Ngày Check-Out</label>
                <input type="text" class="form-control flatpickr-basic flatpickr-input active" id="check_out" name="check_out" placeholder="YYYY-MM-DD" value="" readonly="readonly" disabled />
            </div> --}}
        </div>
        <div class="bookFormSortHotel_bottom_item">
            <div class="buttonSecondary" onClick="submitForm('hotelBookingSort');">
                <i class="fa-solid fa-magnifying-glass"></i>{{ t('form_search_hotel') }}
            </div>
        </div>
    </div>
</div>