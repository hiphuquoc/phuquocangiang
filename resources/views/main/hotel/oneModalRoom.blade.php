<div class="modalHotelRoom_box">
    <!-- icon close -->
    <div class="modalHotelRoom_box_close" onClick="openCloseModalRoom({{ $price->id }});">
        <i class="fa-solid fa-xmark"></i>
    </div>
    
    <div id="js_setHeightBoxByBox_element_{{ $price->id }}" class="modalHotelRoom_box_body">
        <div id="js_setHeightBoxByBox_rule_{{ $price->id }}" class="modalHotelRoom_box_body_gallery">
            @if(!empty($price->room->images)&&$price->room->images->isNotEmpty())
                <div class="modalHotelRoom_box_body_gallery_top">
                    <img src="{{ config('main.svg.loading_main') }}" data-google-cloud="{{ $price->room->images[0]->image }}" data-size="600" alt="{{ t('hotel_room_image', ['name' => $price->room->name]) }}" title="{{ t('hotel_room_image', ['name' => $price->room->name]) }}" />
                </div>
                <div class="modalHotelRoom_box_body_gallery_bottom">
                    @foreach($price->room->images as $image)
                        <div class="modalHotelRoom_box_body_gallery_bottom_item">
                            <img src="{{ config('main.svg.loading_main') }}" data-google-cloud="{{ $image->image }}" data-size="200" alt="{{ t('hotel_room_image', ['name' => $price->room->name]) }}" title="{{ t('hotel_room_image', ['name' => $price->room->name]) }}" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="modalHotelRoom_box_body_info customScrollBar-y">
                <!-- title -->
                <div class="modalHotelRoom_box_body_info_title">
                    {{ $price->room->name }}
                </div>
                <!-- số người tối đa & kích thước phòng -->
                <div class="modalHotelRoom_box_body_info_item">
                    <div class="modalHotelRoom_box_body_info_item_text">
                        <div> 
                            <img src="{{ Storage::url('images/svg/icon-sizeroom.svg') }}" alt="{{ t('hotel_room_size') }}" title="{{ t('hotel_room_size') }}" />
                            <span>{{ $price->room->size }} m<sup>2</sup></span>
                        </div>
                        <div> 
                            <img src="{{ Storage::url('images/svg/icon-adult.svg') }}" alt="{{ t('hotel_max_people') }}" title="{{ t('hotel_max_people') }}" />
                            <span>{{ $price->number_people }}</span>
                        </div>
                    </div>
                </div>
                <!-- bao gồm -->
                @if($price->breakfast==1||$price->given==1)
                    <div class="modalHotelRoom_box_body_info_item">
                        <div class="modalHotelRoom_box_body_info_item_label">
                            {{ t('hotel_room_includes') }}
                        </div>
                        <div class="modalHotelRoom_box_body_info_item_text">
                            @if($price->breakfast==1)
                                <div class="modalHotelRoom_box_body_info_item_text_item">
                                    <img src="{{ Storage::url('images/svg/icon-breakfast.svg') }}" alt="{{ t('hotel_includes_breakfast') }}" title="{{ t('hotel_includes_breakfast') }}" />
                                    <span>{{ t('hotel_breakfast') }}</span>
                                </div>
                            @endif
                            @if($price->given==1)
                                <div class="modalHotelRoom_box_body_info_item_text_item">
                                    <img src="{{ Storage::url('images/svg/icon-given.png') }}" alt="{{ t('hotel_includes_pickup') }}" title="{{ t('hotel_includes_pickup') }}" />
                                    <span>{{ t('hotel_pickup') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="modalHotelRoom_box_body_info_item">
                    <div class="modalHotelRoom_box_body_info_item_label">
                        {{ t('hotel_bed_type_label') }}
                    </div>
                    <div class="modalHotelRoom_box_body_info_item_text">
                        @php
                            $tmp        = [];
                            foreach($price->beds as $bed){
                                $tmp[]  = $bed->quantity.'-'.$bed->infoHotelBed->name;
                            }
                            $xhtmlBed   = implode(' '.t('hotel_and').' ', $tmp);
                            if(empty($xhtmlBed)) $xhtmlBed = t('hotel_undefined');
                        @endphp
                        <div class="modalHotelRoom_box_body_info_item_text_item" style="width:100%;">
                            <img src="{{ Storage::url('images/svg/icon-bed.svg') }}" alt="{{ t('hotel_bed_type') }}" title="{{ t('hotel_bed_type') }}" style="width:22px;" />
                            <span>{{ $xhtmlBed }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- facilities -->
                @if(!empty($price->room->facilities)&&$price->room->facilities->isNotEmpty())
                    <div class="modalHotelRoom_box_body_info_item">
                        <div class="modalHotelRoom_box_body_info_item_label">
                            {{ t('hotel_basic_facilities') }}:
                        </div>
                        <div class="modalHotelRoom_box_body_info_item_facilities">

                            @foreach($price->room->facilities as $facility)
                                <div class="modalHotelRoom_box_body_info_item_facilities_item">
                                    {!! $facility->infoHotelRoomFacility->icon !!}
                                    {{ $facility->infoHotelRoomFacility->name }}
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endif

                {{-- <!-- condition -->
                @if(!empty($price->description))
                    <div class="modalHotelRoom_box_body_info_condition">
                        {!! $price->description !!}
                    </div>
                @endif --}}
                <!-- details -->
                @if(!empty($price->room->details)&&$price->room->details->isNotEmpty())
                
                    
                    @foreach($price->room->details as $detail)
                    <div class="modalHotelRoom_box_body_info_item">
                        <div class="modalHotelRoom_box_body_info_item_label">
                            {{ $detail->name }}
                        </div>
                        <div class="modalHotelRoom_box_body_info_item_text">
                            {!! $detail->detail !!}
                        </div>
                    </div>
                    @endforeach

                
                @endif
        </div>
    </div>
    <!-- button -->
    <div class="modalHotelRoom_box_footer">
        @if(!empty($price->price))
            <div class="modalHotelRoom_box_footer_price">
                {{-- @if(!empty($price->sale_off)&&!empty($price->price_old))
                    <div class="modalHotelRoom_box_footer_price_old">
                        <div class="modalHotelRoom_box_footer_price_old_number">
                            {!! format_price($price->price_old) !!}
                        </div>
                        <div class="modalHotelRoom_box_footer_price_old_saleoff">
                            -{{ $price->sale_off }}%
                        </div>
                    </div>
                @endif --}}
                <div class="modalHotelRoom_box_footer_price_now">
                    {!! format_price($price->price) !!}
                </div>
            </div>
        @endif
        <a href="{{ booking_route('hotelBooking.form', ['hotel_price_id' => $price->id]) }}" class="modalHotelRoom_box_footer_button">{{ t('hotel_book_this_room') }}</a>
    </div>
</div> 
<div class="modalHotelRoom_bg" onClick="openCloseModalRoom({{ $price->id }});"></div>