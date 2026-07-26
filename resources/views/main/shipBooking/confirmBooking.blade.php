@extends('main.layouts.main')
@push('head-custom')
    
@endpush
@section('content')

    @include('main.snippets.breadcrumb')

    <div class="pageConfirm background">
        <div class="sectionBox">
            <div class="container">
                <div class="bookingForm">
                    <!-- Tình trạng booking -->
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_body successMessageBox">
                            <div class="successMessageBox_head">
                                <i class="fa-solid fa-check"></i>{{ t('confirm_ship_success') }}
                            </div>
                            <div class="successMessageBox_body">
                                <div>{!! t('confirm_booking_code', ['no' => '<span class="highLight">'.($item->no ?? null).'</span>']) !!}</div>
                                <div>{{ t('confirm_status_label') }} <span class="badgeWait"><i class="fa-regular fa-clock"></i>{{ t('confirm_status_waiting') }}</span></div>
                                <div class="noteWait">{!! t('confirm_service_note_wait', ['brand' => config('company.sortname')]) !!}</div>
                            </div>
                        </div>
                    </div>
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_head">
                            {{ t('booking_contact_info') }}
                        </div>
                        <div class="bookingForm_item_body" style="padding:0;background:none;border:none;box-shadow:none;">
                            <table class="tableDetailShipBooking noResponsive">
                                <tbody>
                                    <tr>
                                        <td style="width:150px;">{{ t('confirm_customer_name') }}</td>
                                        <td>{{ $item->customer_contact->name ?? null }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ t('booking_phone') }}</td>
                                        <td>{{ $item->customer_contact->phone ?? null }}</td>
                                    </tr>
                                    @if(!empty($item->customer_contact->zalo))
                                        <tr>
                                            <td>Zalo</td>
                                            <td>{{ $item->customer_contact->zalo }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($item->customer_contact->email))
                                        <tr>
                                            <td>Email</td>
                                            <td>{{ $item->customer_contact->email }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Thông tin liên hệ -->
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_head">
                            {{ t('confirm_cost') }}
                        </div>
                        <div class="bookingForm_item_body" style="padding:0;background:none;border:none;box-shadow:none;">
                            <table class="tableDetailShipBooking noResponsive" style="border-radius:10px;overflow:hidden;">
                                {{-- <thead>

                                </thead> --}}
                                <tbody>
                                    <!-- Bảng tính tiền -->
                                    @php
                                        $total  = 0;
                                    @endphp
                                    @if(!empty($item->infoDeparture))
                                        @foreach($item->infoDeparture as $departure)
                                            <tr style="background:#EDF2F7;">
                                                <td colspan="3">
                                                    <div><i class="fa-solid fa-ship"></i>{{ t('ship_departure_label') }} {{ $departure->departure }} - {{ $departure->location }}</div>
                                                </td>
                                            </tr>
                                            @if(!empty($departure->quantity_adult)&&!empty($departure->price_adult))
                                                <tr>
                                                    <td>{{ t('pax_adult') }}</td>
                                                    <td style="text-align:right;">{{ $departure->quantity_adult }} * {{ number_format($departure->price_adult) }}</td>
                                                    <td style="text-align:right;">{{ number_format($departure->quantity_adult*$departure->price_adult) }}</td>
                                                </tr>
                                                @php
                                                    $total  += $departure->quantity_adult*$departure->price_adult;
                                                @endphp
                                            @endif
                                            @if(!empty($departure->quantity_child)&&!empty($departure->price_child))
                                                <tr>
                                                    <td>{{ t('pax_child_6_11') }}</td>
                                                    <td style="text-align:right;">{{ $departure->quantity_child }} * {{ number_format($departure->price_child) }}</td>
                                                    <td style="text-align:right;">{{ number_format($departure->quantity_child*$departure->price_child) }}</td>
                                                </tr>
                                                @php
                                                    $total  += $departure->quantity_child*$departure->price_child;
                                                @endphp
                                            @endif
                                            @if(!empty($departure->quantity_old)&&!empty($departure->price_old))
                                                <tr>
                                                    <td>{{ t('pax_senior_60plus') }}</td>
                                                    <td style="text-align:right;">{{ $departure->quantity_old }} * {{ number_format($departure->price_old) }}</td>
                                                    <td style="text-align:right;">{{ number_format($departure->quantity_old*$departure->price_old) }}</td>
                                                </tr>
                                                @php
                                                    $total  += $departure->quantity_old*$departure->price_old;
                                                @endphp
                                            @endif
                                        @endforeach
                                    @endif
                                    @if(!empty($total))
                                        <tr>
                                            <td colspan="2">
                                                <div style="font-weight:500;">{{ t('confirm_total_amount') }}</div>
                                            </td>
                                            <td style="text-align:right;">
                                                <div style="font-weight:700;font-size:1.2rem;color:#E74C3C;letter-spacing:0.5px;">{!! format_price($total) !!}</div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Thông tin chuyến tàu -->
                    @if($item->infoDeparture->isNotEmpty())
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_head">
                            {{ t('confirm_ship_info') }}
                        </div>
                        <div class="bookingForm_item_body" style="padding:0;background:none;border:none;box-shadow:none;">
                            <div class="shipDepartureConfirmBox">
                                @foreach($item->infoDeparture as $departure)
                                    <div class="shipDepartureConfirmBox_item">
                                        @include('main.shipBooking.tableDeparture', compact('departure'))
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- Quy trình đặt vé -->
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_head">
                            {{ t('confirm_process_ship_title') }}
                        </div>
                        <div class="bookingForm_item_body" style="padding:0;background:none;border:none;box-shadow:none;">
                            <ul>
                                <li>{{ t('confirm_process_step1') }}</li>
                                <li>{{ t('confirm_process_ship_passenger_info') }}</li>
                                <li>{{ t('confirm_process_step2') }}</li>
                                <li>{{ t('confirm_process_ship_step4') }}</li>
                            </ul>
                        </div>
                    </div>
                    <!-- Button Quay lại -->
                    <div class="buttonBox">
                        <a href="{{ home_url() }}">
                            <button class="buttonCancel" type="button" aria-label="{{ t('back_to_home') }}">
                                <i class="fa-solid fa-angles-left"></i>{{ t('back_to_home') }}
                            </button>
                        </a>
                    </div>
                </div>
    
            </div>
        </div>
    </div>

@endsection
@push('scripts-custom')
    <script type="text/javascript">

    </script>
@endpush