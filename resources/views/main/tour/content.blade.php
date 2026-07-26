<div class="contentTour">

    <!-- gallery -->
    <div class="contentTour_item">
        @include('main.tour.gallery', compact('item'))
    </div>

    <!-- Điểm nổi bật của Tour -->
	<div id="diem-noi-bat-chuong-trinh-tour" class="contentTour_item notPrint">
		<div class="contentTour_item_title">
			<i class="fa-solid fa-award"></i>
			<h2>{{ t('tour_highlights') }}</h2>
		</div>
		<div class="contentTour_item_text">
			<table class="tableList noResponsive" style="margin-bottom:0;">
				<tbody>
					<tr>
						<td style="width:100px;">{{ t('tour_journey') }}</td>
						<td><h3 style="font-size:1rem;">{{ $item->seo->description }}</h3></td>
					</tr>
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
                    <tr>
                        <td>{{ t('tour_transport') }}</td>
                        <td><h3 style="font-size:1rem;">{{ $item->transport }}</h3></td>
                    </tr>
                    <tr>
                        <td>{{ t('tour_depart_from') }}</td>
                        <td><h3 style="font-size:1rem;">{{ $item->pick_up }}</h3></td>
                    </tr>
				</tbody>
			</table>
			{!! $item->content->special_content ?? null !!}
		</div>
	</div>
    <!-- Bảng giá Tour -->
	@if($item->options->isNotEmpty())
        <div id="bang-gia-tour" class="contentTour_item">
            <div class="contentTour_item_title noBorder">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <h2>{{ t('tour_pricing', ['name' => $item->name ?? '']) }}</h2>
            </div>
            <div class="contentTour_item_text">
                @php
                    $options    = \App\Http\Controllers\AdminTourOptionController::margeTourPriceByDate($item->options);
                    // dd($options);
                @endphp
                <table class="tableContentBorder" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>{{ t('tour_option') }}</th>
                            <th>{{ t('tour_applied_price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($options as $option)
                            <tr>
                                <td>
                                    <h3 style="font-weight:700;font-size:1rem;">{{ $option['name'] }}</h3>
                                    @foreach($option['date_apply'] as $price)
                                        @foreach($price as $applyAge)
                                            <div style="font-size:0.95rem;">{{ t('tour_from_to_dates', ['from' => !empty($applyAge['date_start']) ? date('d/m/Y', strtotime($applyAge['date_start'])) : '...', 'to' => !empty($applyAge['date_end']) ? date('d/m/Y', strtotime($applyAge['date_end'])) : '...']) }}</div>
                                            @break;
                                        @endforeach
                                        
                                    @endforeach
                                </td>
                                <td style="vertical-align:top;">
                                    @foreach($option['date_apply'] as $price)
                                        @foreach($price as $applyAge)
                                            <div><span style="font-weight:700;color:rgb(0, 90, 180);font-size:1.1rem;">{!! !empty($applyAge['price']) ? format_price($applyAge['price']) : '-' !!}</span> /{{ $applyAge['apply_age'] ?? '-' }}</div>
                                        @endforeach
                                        @break
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
            </div>
        </div>
    @endif
    <!-- Chương trình tour -->
    @if(!empty($item->timetables)&&$item->timetables->isNotEmpty())
        <div id="lich-trinh-tour-du-lich" class="contentTour_item">
            <div class="contentTour_item_title noBorder">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_itinerary') }}</h2>
                <div class="notPrint">
                    <span class="active" data-tabcontent="timeTables_full" onClick="tabContent(this);" style="cursor:pointer;">{{ t('tour_itinerary_full') }}</span>
                    <span data-tabcontent="timeTables_sort" onClick="tabContent(this);" style="cursor:pointer;">{{ t('tour_itinerary_short') }}</span>
                </div>
            </div>
            <div class="contentTour_item_text">
                <!-- nội dung đầy đủ -->
                <div id="timeTables_full" class="dayTourByList">
                    @foreach($item->timetables as $timetable)
                        <div class="dayTourByList_item">
                            <div class="dayTourByList_item_title" onClick="hideShowContent(this);">
                                <h3>{{ $timetable->title }}</h3>
                            </div>
                            <div class="dayTourByList_item_text">
                                {!! $timetable->content !!}
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- nội dung rút gọn -->
                <div id="timeTables_sort" class="dayTourByList" style="display:none;">
                    @foreach($item->timetables as $timetable)
                        @if(!empty($timetable->content_sort))
                            <div class="dayTourByList_item">
                                <div class="dayTourByList_item_title" onClick="hideShowContent(this);">
                                    <h3>{{ $timetable->title }}</h3>
                                </div>
                                <div class="dayTourByList_item_text">
                                    {!! $timetable->content_sort !!}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <!-- Chính sách trẻ em -->
    @if(!empty($item->content->policy_child))
        <div id="chinh-sach-tre-em-tour" class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_policy_child') }}</h2>
            </div>
            <div class="contentTour_item_text">
                {!! $item->content->policy_child !!}
            </div>
        </div>
    @endif
    <!-- Tour bao gồm -->
    @if(!empty($item->content->include))
        <div id="tour-bao-gom-va-khong-bao-gom" class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_includes') }}</h2>
            </div>
            <div class="contentTour_item_text">
                {!! $item->content->include !!}
            </div>
        </div>
    @endif
    <!-- Tour chưa bao gồm -->
    @if(!empty($item->content->not_include))
        <div class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_not_includes') }}</h2>
            </div>
            <div class="contentTour_item_text">
                {!! $item->content->not_include !!}
            </div>
        </div>
    @endif
    <!-- Chính sách hủy tour -->
    @if(!empty($item->content->policy_cancel))
        <div id="chinh-sach-huy-tour" class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_policy_cancel', ['name' => $item->name ?? '']) }}</h2>
            </div>
            <div class="contentTour_item_text">
                {!! $item->content->policy_cancel !!}
            </div>
        </div>
    @endif
    <!-- Chính sách hủy tour -->
    @if(!empty($item->content->note))
        <div id="luu-y-khi-tham-gia-chuong-trinh-tour" class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_notes', ['name' => $item->name ?? '']) }}</h2>
            </div>
            <div class="contentTour_item_text">
                {!! $item->content->note !!}
            </div>
        </div>
    @endif
    <!-- Thực đơn -->
    @if(!empty($item->content->menu))
        <div id="thuc-don-theo-chuong-trinh-tour" class="contentTour_item">
            <div class="contentTour_item_title noBorder">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_menu', ['name' => $item->name ?? '']) }}</h2>
            </div>
            <div class="contentTour_item_text">
                <div class="menuTour">
                    {!! $item->content->menu !!}
                </div>
            </div>
        </div>
    @endif
    <!-- Khách sạn tham khảo -->
    @if(!empty($item->content->hotel))
        <div id="khach-san-tham-khao" class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-bookmark"></i>
                <h2>{{ t('tour_reference_hotel') }}</h2>
            </div>
            <div class="contentTour_item_text">
                <div class="hotelTour">
                    {!! $item->content->hotel !!}
                </div>
            </div>
        </div>
    @endif
    <!-- Câu hỏi thường gặp -->
    @if(!empty($item->questions)&&$item->questions->isNotEmpty())
        <div id="cau-hoi-thuong-gap" class="contentTour_item">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-circle-question"></i>
                <h2>{{ t('tour_faq_about', ['name' => $item->name ?? '']) }}</h2>
            </div>
            <div class="contentTour_item_text">
                @include('main.snippets.faq', [
                    'list' => $item->questions, 
                    'title' => $item->name,
                    'hiddenTitle'   => true
                ])
            </div>
        </div>
    @endif

    <!-- Tour liên quan -->
    @if(!empty($related)&&$related->isNotEmpty())
        <div id="tour-lien-quan" class="contentTour_item notPrint">
            <div class="contentTour_item_title">
                <i class="fa-solid fa-person-walking-luggage"></i>
                <h2>{{ t('tour_related') }}</h2>
            </div>
            <div class="contentTour_item_text">
                @include('main.tour.related', ['list' => $related])
            </div>
        </div>
    @endif
</div>

@push('scripts-custom')
    <script type="text/javascript">
        function tabContent(elemtBtn){
            const idShow        = $(elemtBtn).data('tabcontent');
            const elementShow   = $('#'+idShow);
            /* ẩn tất cả phần tử con => hiện lại phần tử được chọn */
            elementShow.parent().children().each(function(){
                $(this).css('display', 'none');
            });
            elementShow.css('display', 'block');
            /* xóa active tất cả phần tử con button => active button vừa click */
            $(elemtBtn).parent().children().each(function(){
                $(this).removeClass('active');
            });
            $(elemtBtn).addClass('active');
        }
        function hideShowContent(elemtBtn){
            const elemtContent      = $(elemtBtn).next();
            const displayContent    = elemtContent.css('display');
            if(displayContent=='none'){
                elemtContent.css('display', 'block');
            }else {
                elemtContent.css('display', 'none');
            }
        }
    </script>
@endpush