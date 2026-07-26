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
                    <div class="bookingForm_item_body successMessageBox">
                        <div class="successMessageBox_head">
                            <i class="fa-solid fa-check"></i>{{ t('confirm_service_success') }}
                        </div>
                        <div class="successMessageBox_body">
                            <div>{!! t('confirm_booking_code', ['no' => '<span class="highLight">'.($item->no ?? '').'</span>']) !!}</div>
                            <div>{{ t('confirm_status_label') }} <span class="badgeWait"><i class="fa-regular fa-clock"></i>{{ t('confirm_status_waiting') }}</span></div>
                            <div class="noteWait">{!! t('confirm_service_note_wait', ['brand' => config('company.sortname')]) !!}</div>
                        </div>
                    </div>
                    
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_head">
                            {{ t('confirm_detail_info') }}
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
                                            <td>{{ $item->customer_contact->zalo ?? null }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($item->customer_contact->email))
                                        <tr>
                                            <td>Email</td>
                                            <td>{{ $item->customer_contact->email }}</td>
                                        </tr>
                                    @endif
                                    @if(!empty($item->service->serviceLocation->display_name))
                                        <tr>
                                            <td>{{ t('booking_destination') }}</td>
                                            <td>{{ $item->service->serviceLocation->display_name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>{{ t('confirm_service_name') }}</td>
                                        <td><a href="{{ env('APP_URL').'/'.$item->service->seo->slug_full }}" style="color:rgba(0,123,255,1);text-decoration:none" target="_blank">{{ $item->service->name ?? null }}</a></td>
                                    </tr>
                                    <tr>
                                        <td>{{ t('confirm_type') }}</td>
                                        <td>{{ $item->quantityAndPrice[0]->option_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ t('confirm_date') }}</td>
                                        <td>{{ \App\Helpers\DateAndTime::convertMktimeToDayOfWeek(strtotime($item->date_from)) }}, {{ date('d/m/Y', strtotime($item->date_from)) }}</td>
                                    </tr>
                                    @if(!empty($item->note_customer))
                                        <tr>
                                            <td>{{ t('note_label') }}</td>
                                            <td>{{ $item->note_customer }}</td>
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
                            <table class="tableDetailShipBooking noResponsive">
                                <thead>
                                    <!-- Bảng tính tiền -->
                                    <tr>
                                        <th style="text-align:center;"><div>{{ t('confirm_th_service') }}</div></th>
                                        <th style="text-align:center;"><div>{{ t('confirm_th_unit_price') }}</div></th>
                                        <th style="text-align:center;"><div>{{ t('confirm_th_total') }}</div></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $xhtmlTable = null;
                                        $total      = 0;
                                        foreach($item->quantityAndPrice as $quantityPrice){
                                            $xhtmlTable .= '<tr>
                                                                <td>'.$quantityPrice->option_age.'</td>
                                                                <td style="text-align:right;">'.$quantityPrice->quantity.' * '.number_format($quantityPrice->price).'</td>
                                                                <td style="text-align:right;">'.number_format($quantityPrice->quantity*$quantityPrice->price).'</td>
                                                            </tr>';
                                            $total  += $quantityPrice->quantity*$quantityPrice->price;
                                        }
                                    @endphp
                                    {!! $xhtmlTable !!}
                                    <tr>
                                        <td colspan="2">
                                        <div style="font-weight:500;">{{ t('confirm_total_amount') }}</div>
                                        </td>
                                        <td style="text-align:right;">
                                        <div style="font-weight:700;font-size:1.2rem;color:#E74C3C;letter-spacing:0.5px;">{!! format_price($total) !!}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Quy trình đặt vé -->
                    <div class="bookingForm_item">
                        <div class="bookingForm_item_head">
                        {{ t('confirm_process_service_title') }}
                        </div>
                        <div class="bookingForm_item_body" style="padding:0;background:none;border:none;box-shadow:none;">
                        <ul>
                            <li>{{ t('confirm_process_step1') }}</li>
                            <li>{{ t('confirm_process_step2') }}</li>
                            <li>{{ t('confirm_process_step3_service') }}</li>
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