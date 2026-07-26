<div class="inputWithLabelInside peopleGroup inputWithForm">
    <label for="bookFormSort_date">{{ t('hotel_pax_and_rooms') }}</label>
    
        <input type="text" id="js_setValueQuantityHotel_idWrite" class="form-control inputWithForm_input" value="{{ $dataForm['adult'] ?? 2 }} {{ t('pax_adult') }}, {{ $dataForm['child'] ?? 0 }} {{ t('pax_child') }}, {{ $dataForm['quantity'] ?? 1 }} {{ t('hotel_rooms') }}" readonly="readonly"  aria-label="{{ t('hotel_pax_and_rooms_aria') }}" required />
        <div class="inputWithForm_form">
            <div class="formBox">
                <div class="formBox_labelOneRow">
                    <div class="formBox_labelOneRow_item">
                        <div class="labelWithIcon">
                            <div class="labelWithIcon_icon adult"></div>
                            <div class="labelWithIcon_label">
                                {{ t('pax_adult') }} ({{ t('pax_age_year_from', ['year' => date('Y', time()) - 12]) }})
                            </div>
                        </div>
                        <div class="inputNumberCustom"> 
                            <div class="inputNumberCustom_button" onclick="changeValueInputHotel('js_changeValueInputHotel_input_nguoilon', 'minus');">
                                <i class="fa-solid fa-minus"></i>
                            </div>
                            <input id="js_changeValueInputHotel_input_nguoilon" class="inputNumberCustom_input" type="number" name="adult" value="{{ $dataForm['adult'] ?? 2 }}" aria-label="{{ t('hotel_pax_adult_aria') }}" onkeyup="setValueQuantityHotel()">
                            <div class="inputNumberCustom_button" onclick="changeValueInputHotel('js_changeValueInputHotel_input_nguoilon', 'plus');">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="formBox_labelOneRow_item">
                        <div class="labelWithIcon">
                            <div class="labelWithIcon_icon children"></div>
                            <div class="labelWithIcon_label">
                                {{ t('pax_child') }} ({{ t('pax_age_year_range', ['from' => date('Y', time()) - 6, 'to' => date('Y', time()) - 11]) }})
                            </div>
                        </div>
                        <div class="inputNumberCustom"> 
                            <div class="inputNumberCustom_button" onclick="changeValueInputHotel('js_changeValueInputHotel_input_treem', 'minus');">
                                <i class="fa-solid fa-minus"></i>
                            </div>
                            <input id="js_changeValueInputHotel_input_treem" class="inputNumberCustom_input" type="number" name="child" value="{{ $dataForm['child'] ?? 0 }}" aria-label="{{ t('hotel_pax_child_aria') }}" onkeyup="setValueQuantityHotel()">
                            <div class="inputNumberCustom_button" onclick="changeValueInputHotel('js_changeValueInputHotel_input_treem', 'plus');">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="formBox_labelOneRow_item">
                        <div class="labelWithIcon">
                            <div class="labelWithIcon_icon hotelRoom"></div>
                            <div class="labelWithIcon_label">
                                {{ t('hotel_rooms_count') }}
                            </div>
                        </div>
                        <div class="inputNumberCustom"> 
                            <div class="inputNumberCustom_button" onclick="changeValueInputHotel('js_changeValueInputHotel_input_phong', 'minus');">
                                <i class="fa-solid fa-minus"></i>
                            </div>
                            <input id="js_changeValueInputHotel_input_phong" class="inputNumberCustom_input" type="number" name="quantity" value="{{ $dataForm['quantity'] ?? 1 }}" aria-label="{{ t('hotel_pax_room_aria') }}" onkeyup="setValueQuantityHotel()">
                            <div class="inputNumberCustom_button" onclick="changeValueInputHotel('js_changeValueInputHotel_input_phong', 'plus');">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
</div>

@push('scripts-custom')
    <script type="text/javascript">
        function setValueQuantityHotel(){
            /* input lưu giá trị */ 
			const valueAdult 	= parseInt($('#js_changeValueInputHotel_input_nguoilon').val());
			const valueChild 	= parseInt($('#js_changeValueInputHotel_input_treem').val());
			const valueQuantity = parseInt($('#js_changeValueInputHotel_input_phong').val());
            $('#hotel_booking_adult').val(valueAdult);
            $('#hotel_booking_child').val(valueChild);
            $('#hotel_booking_quantity').val(valueQuantity);
            /* input này chỉ show */
            const valueFull 	= valueAdult+' {{ t('pax_adult') }}, '+valueChild+' {{ t('pax_child') }}, '+valueQuantity+' {{ t('hotel_rooms') }}';
			$('#js_setValueQuantityHotel_idWrite').val(valueFull);
		}
        function changeValueInputHotel(idInput, action){
            const valueInput = parseInt($('#'+idInput).val());
            if(action == 'plus'){
                $('#'+idInput).val(parseInt(valueInput + 1));
            } else if(action == 'minus' && valueInput > 0){
                $('#'+idInput).val(parseInt(valueInput - 1));
            }
            setValueQuantityHotel();
            
            if (typeof loadBookingSummary === 'function') {
                loadBookingSummary();
            }
        }
    </script>
@endpush