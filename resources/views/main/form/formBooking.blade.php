@push('head-custom')
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/pickers/pickadate/pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/css/plugins/forms/pickers/form-flat-pickr.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/css/plugins/forms/pickers/form-pickadate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('sources/admin/app-assets/vendors/css/forms/select/select2.min.css') }}">
@endpush

<div class="container">
	<div class="bookFormSort" onClick="hideShowAround();">
		<div class="bookFormSort_head">
			<div class="bookFormSort_head_item {{ !empty($active)&&$active=='ship' ? 'active' : null }}" data-tab="shipBookingForm" onClick="changeTab(this);">
				<i class="fa-solid fa-ship"></i>
				<span class="show-767">{{ t('tab_ship') }}</span>
				<span class="hide-767">{{ t('tab_ship_full') }}</span>
			</div>
			<div class="bookFormSort_head_item {{ !empty($active)&&$active=='tour' ? 'active' : null }}" data-tab="tourBookingForm" onClick="changeTab(this);">
				<i class="fa-solid fa-suitcase-rolling"></i>
				<span>{{ t('tab_tour') }}</span>
			</div>
			<div class="bookFormSort_head_item {{ !empty($active)&&$active=='combo' ? 'active' : null }}" data-tab="comboBookingForm" onClick="changeTab(this);">
				<i class="fa-solid fa-gift"></i>
				<span>{{ t('tab_combo') }}</span>
			</div>
			<div class="bookFormSort_head_item {{ !empty($active)&&$active=='hotel' ? 'active' : null }}" data-tab="hotelBookingForm" onClick="changeTab(this);">
				<i class="fa-solid fa-bed"></i>
				<span>{{ t('tab_hotel') }}</span>
			</div>
			<div class="bookFormSort_head_item {{ !empty($active)&&$active=='service' ? 'active' : null }}" data-tab="ticketBookingForm" onClick="changeTab(this);">
				<i class="fa-solid fa-ticket"></i>
				<span>{{ t('tab_entertainment') }}</span>
			</div>
		</div>
		<div class="bookFormSort_body">
			<!-- Ship booking form -->
			<div id="shipBookingForm" {{ !empty($active)&&$active!='ship' ? 'style=display:none;' : null }}>
				<form id="shipBookingSort" method="GET" action="{{ booking_route('shipBooking.form') }}">
					@include('main.form.sortBooking.ship', compact('item'))
				</form>
			</div>
			<!-- Tour booking form -->
			<div id="tourBookingForm" {{ !empty($active)&&$active!='tour' ? 'style=display:none;' : null }}>
				<form id="tourBookingSort" method="GET" action="{{ booking_route('tourBooking.form') }}">
					@include('main.form.sortBooking.tour', compact('item'))
				</form>
			</div>
			<!-- Combo booking form -->
			<div id="comboBookingForm" {{ !empty($active)&&$active!='combo' ? 'style=display:none;' : null }}>
				<form id="comboBookingSort" method="GET" action="{{ booking_route('comboBooking.form') }}">
					@include('main.form.sortBooking.combo', compact('item'))
				</form>
			</div>
			<!-- Khách sạn -->
			<div id="hotelBookingForm" {{ !empty($active)&&$active!='hotel' ? 'style=display:none;' : null }}>
				<form id="hotelBookingSort" method="GET" action="{{ booking_route('hotelBooking.search') }}">
					@include('main.form.sortBooking.hotel', compact('item'))
				</form>
			</div>
			<!-- Vé dịch vụ -->
			<div id="ticketBookingForm" {{ !empty($active)&&$active!='service' ? 'style=display:none;' : null }}>
				<form id="ticketBookingSort" method="GET" action="{{ booking_route('serviceBooking.form') }}">
					@include('main.form.sortBooking.ticket')
				</form>
			</div>
		</div>
	</div>
</div>

@push('scripts-custom')
	<!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/picker.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/picker.date.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/picker.time.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
	<!-- ===== -->
    <script src="{{ asset('sources/admin/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/js/scripts/forms/pickers/form-pickers.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ asset('sources/admin/app-assets/js/scripts/forms/form-select2.min.js') }}"></script>
    <script type="text/javascript">

		$(document).ready(function(){
			loadShipLocationByShipDeparture($('#js_loadShipLocationByShipDeparture_element'), 'js_loadShipLocationByShipDeparture_idWrite', '{{ $item->portLocation->name ?? $item->ships[0]->portLocation->name ?? null }}');
		});

		function submitForm(idForm){
            // event.preventDefault();
            $('#'+idForm).submit();
        }

        function hideShowAround(action = 'on'){
			const elemt = $('#js_hideShowAround');
			if(elemt.length==0){
				$('<div id="js_hideShowAround" style="width:100%;height:100%;position:fixed;background-color:rgb(35, 42, 49);opacity: 0.8;top:0;left:0;z-index:100;" onClick="hideShowAround(\'off\');"></div>').appendTo('body');
			}else {
				if(action=='off') elemt.remove();
			}
		}

		function changeTab(element){
			/* active button */
			$(element).parent().children().each(function(){
				$(this).removeClass('active');
			});
			$(element).addClass('active');
			/* xử lý tab content */
			const idContent 		= $(element).data('tab');
			const elementContent 	= $('#'+idContent);
			/* ẩn tất cả tab */
			elementContent.parent().children().each(function(){
				$(this).css('display', 'none');
			});
			/* bật tab được chọn */
			elementContent.css('display', 'block');
		}

		function swapShipPorts(){
			const $departure = $('#js_loadShipLocationByShipDeparture_element');
			const $arrival = $('#js_loadShipLocationByShipDeparture_idWrite');
			if ($departure.length && $arrival.length) {
				const depVal = $departure.val();
				const arrVal = $arrival.val();
				if (arrVal && $departure.find('option[value="' + arrVal + '"]').length) {
					$departure.val(arrVal).trigger('change');
					setTimeout(function(){
						if ($arrival.find('option[value="' + depVal + '"]').length) {
							$arrival.val(depVal).trigger('change');
						}
					}, 350);
				}
			}
		}
    </script>
@endpush