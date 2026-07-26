@php
    $dataShipPort 		= \App\Models\ShipPort::all();
@endphp
<div class="bookFormSortShip">
    <div class="bookFormSortShip_column">
        <!-- One column -->
        <div class="bookFormSortShip_column_item">
            <div class="inputWithIconBetween">
                <div class="inputWithIconBetween_item inputWithLabelInside location">
                    <label for="js_loadShipLocationByShipDeparture_element"><i class="fa-solid fa-anchor"></i> {{ t('form_departure_point') }}</label>
                    <select id="js_loadShipLocationByShipDeparture_element" class="select2 form-select select2-hidden-accessible" name="ship_port_departure_id" onchange="loadShipLocationByShipDeparture(this, 'js_loadShipLocationByShipDeparture_idWrite');" tabindex="-1" aria-hidden="true">
                        {{-- <option value="">- Lựa chọn -</option> --}}
                        @foreach($dataShipPort as $port)
                            @php
                                $selected	= null;
                                /* kiểm tra cho trang ship_info */
                                if(!empty($item->portDeparture->name)&&$item->portDeparture->name==$port->name) $selected = 'selected';
                                /* kiểm tra cho trang ship_location */
                                if(!empty($item->ships[0]->portDeparture->name)&&$item->ships[0]->portDeparture->name==$port->name) $selected = 'selected';
                                $portName 	= \App\Helpers\Build::buildFullShipPort($port);
                            @endphp
                            <option value="{{ $port->id }}" {{ $selected }}>{{ $portName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inputWithIconBetween_icon" onclick="swapShipPorts();" title="{{ t('form_swap_ports') ?? 'Đảo chiều điểm đi / điểm đến' }}" role="button" tabindex="0">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </div>
                <div class="inputWithIconBetween_item inputWithLabelInside location">
                    <label for="js_loadShipLocationByShipDeparture_idWrite"><i class="fa-solid fa-location-dot"></i> {{ t('form_arrival_point') }}</label>
                    <select id="js_loadShipLocationByShipDeparture_idWrite" class="select2 form-select select2-hidden-accessible" name="ship_port_location_id" tabindex="-1" aria-hidden="true">
                        {{-- <option value="">- Lựa chọn -</option> --}}
                    </select>
                </div>
            </div>
        </div>
        <!-- One column -->
        <div class="bookFormSortShip_column_item">
            <!-- One column -->
            <div class="bookFormSortShip_input_item">
                <div class="inputWithLabelInside date">
                    <label for="input_date_ship_1"><i class="fa-regular fa-calendar-days"></i> {{ t('booking_departure_date') }}</label>
                    <input type="text" class="form-control flatpickr-basic flatpickr-input active" id="input_date_ship_1" name="date_1" value="{{ date('Y-m-d', time() + 86400) }}" aria-label="{{ t('form_ship_date_aria') }}" readonly="readonly" required>
                </div>
            </div>
        </div>
    </div>
    <div class="bookFormSortShip_column">
        <!-- One column -->
        <div class="bookFormSortShip_column_item">
            <div class="inputWithLabelInside peopleGroup inputWithForm">
                <label for="js_setValueQuantityShip_idWrite"><i class="fa-solid fa-users"></i> {{ t('form_passenger_count') }}</label>
                {{-- <div class="inputWithForm"> --}}
                    <input type="text" id="js_setValueQuantityShip_idWrite" class="form-control inputWithForm_input" name="quantity" value="{{ t('pax_count_summary', ['adult' => 1, 'child' => 0, 'old' => 0]) }}" readonly="readonly" aria-label="{{ t('form_pax_ship_aria') }}" required>
                    <div class="inputWithForm_form">
                        <div class="formBox">
                            <div class="formBox_labelOneRow">
                                <div class="formBox_labelOneRow_item">
                                    <div class="labelWithIcon">
                                        <div class="labelWithIcon_icon adult"><i class="fa-solid fa-user"></i></div>
                                        <div class="labelWithIcon_label">
                                            <div class="labelWithIcon_title">{{ t('pax_adult') }}</div>
                                            <div class="labelWithIcon_sub">{{ t('pax_age_adult_range', ['from' => date('Y', time()) - 12, 'to' => date('Y', time()) - 59]) }}</div>
                                        </div>
                                    </div>
                                    <div class="inputNumberCustom"> 
                                        <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_nguoilon', 'minus');">
                                            <i class="fa-solid fa-minus"></i>
                                        </div>
                                        <input id="js_changeValueInputShip_input_nguoilon" class="inputNumberCustom_input" type="number" name="adult_ship" value="1" aria-label="{{ t('form_pax_adult_aria') }}" onkeyup="setValueQuantityShip()" />
                                        <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_nguoilon', 'plus');">
                                            <i class="fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="formBox_labelOneRow_item">
                                    <div class="labelWithIcon">
                                        <div class="labelWithIcon_icon children"><i class="fa-solid fa-child"></i></div>
                                        <div class="labelWithIcon_label">
                                            <div class="labelWithIcon_title">{{ t('pax_child') }}</div>
                                            <div class="labelWithIcon_sub">{{ t('pax_age_child_range', ['from' => date('Y', time()) - 6, 'to' => date('Y', time()) - 11]) }}</div>
                                        </div>
                                    </div>
                                    <div class="inputNumberCustom"> 
                                        <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_treem', 'minus');">
                                            <i class="fa-solid fa-minus"></i>
                                        </div>
                                        <input id="js_changeValueInputShip_input_treem" class="inputNumberCustom_input" type="number" name="child_ship" value="0" aria-label="{{ t('form_pax_child_aria') }}" onkeyup="setValueQuantityShip()" />
                                        <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_treem', 'plus');">
                                            <i class="fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="formBox_labelOneRow_item">
                                    <div class="labelWithIcon">
                                        <div class="labelWithIcon_icon old"><i class="fa-solid fa-user-tie"></i></div>
                                        <div class="labelWithIcon_label">
                                            <div class="labelWithIcon_title">{{ t('pax_old') }}</div>
                                            <div class="labelWithIcon_sub">{{ t('pax_age_old_range', ['from' => date('Y', time()) - 60]) }}</div>
                                        </div>
                                    </div>
                                    <div class="inputNumberCustom"> 
                                        <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_caotuoi', 'minus');">
                                            <i class="fa-solid fa-minus"></i>
                                        </div>
                                        <input id="js_changeValueInputShip_input_caotuoi" class="inputNumberCustom_input" type="number" name="old_ship" value="0" aria-label="{{ t('form_pax_senior_aria') }}" onkeyup="setValueQuantityShip()" />
                                        <div class="inputNumberCustom_button" onClick="changeValueInputShip('js_changeValueInputShip_input_caotuoi', 'plus');">
                                            <i class="fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {{-- </div> --}}
            </div>
        </div>
        <!-- One column -->
        <div class="bookFormSortShip_column_item button">
            <div class="buttonSecondary" onClick="submitForm('shipBookingSort');">
                <i class="fa-solid fa-magnifying-glass"></i>{{ t('form_find_ship') }}
            </div>
        </div>
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">
        function loadShipLocationByShipDeparture(element, idWrite, namePortActive = null){
            const idShipPort = $(element).val();
            $.ajax({
                url         : '{{ booking_route("shipBooking.loadShipLocation") }}',
                type        : 'get',
                dataType    : 'html',
                data        : {
                    '_token'        	: '{{ csrf_token() }}',
                    ship_port_id    	: idShipPort,
					name_port_active    : namePortActive
                },
                success     : function(data){
                    $('#'+idWrite).html(data);
                }
            });
        }

        function setValueQuantityShip(){
			const valueAdult 	= parseInt($('#js_changeValueInputShip_input_nguoilon').val());
			const valueChild 	= parseInt($('#js_changeValueInputShip_input_treem').val());
			const valueOld 		= parseInt($('#js_changeValueInputShip_input_caotuoi').val());
			const valueFull 	= valueAdult+' {{ t('pax_adult') }}, '+valueChild+' {{ t('pax_child') }}, '+valueOld+' {{ t('pax_old') }}';
			$('#js_setValueQuantityShip_idWrite').val(valueFull);
		}
        function changeValueInputShip(idInput, action){
			const valueInput 	= parseInt($('#'+idInput).val());
			if(action=='plus'){
				$('#'+idInput).val(parseInt(valueInput + 1));
			}else if(action=='minus'&&valueInput>0){
				$('#'+idInput).val(parseInt(valueInput - 1));
			}
			setValueQuantityShip();
		}
    </script>
@endpush