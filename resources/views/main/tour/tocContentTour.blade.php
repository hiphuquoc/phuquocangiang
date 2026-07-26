<div id="js_buildTocContentSidebar_idWrite" class="tocContentTour customScrollBar-y" style="margin-top:1.25rem;">
    <a href="#diem-noi-bat-chuong-trinh-tour" title="{{ t('tour_highlights') }}" class="tocContentTour_item">
        <i class="fa-solid fa-award"></i>{{ t('tour_highlights_short') }}
    </a>
    <a href="#bang-gia-tour" title="{{ t('tour_pricing_short') }}" class="tocContentTour_item">
        <i class="fa-solid fa-hand-holding-dollar"></i>{{ t('tour_pricing_short') }}
    </a>
    @if(!empty($item->timetables)&&$item->timetables->isNotEmpty())
        <a href="#lich-trinh-tour-du-lich" title="{{ t('tour_itinerary') }}" class="tocContentTour_item">
            <i class="fa-solid fa-bookmark"></i>{{ t('tour_itinerary') }}
        </a>
    @endif
    @if(!empty($item->content->policy_child))
        <a href="#chinh-sach-tre-em-tour" title="{{ t('tour_policy_child') }}" class="tocContentTour_item">
            <i class="fa-solid fa-children"></i>{{ t('tour_policy_child') }}
        </a>
    @endif
    @if(!empty($item->content->include)||!empty($item->content->not_include))
        <a href="#tour-bao-gom-va-khong-bao-gom" title="{{ t('tour_include_exclude') }}" class="tocContentTour_item">
            <i class="fa-solid fa-list-check"></i>{{ t('tour_include_exclude') }}
        </a>
    @endif
    @if(!empty($item->content->policy_cancel))
        <a href="#chinh-sach-huy-tour" title="{{ t('tour_policy_cancel_short') }}" class="tocContentTour_item">
            <i class="fa-solid fa-xmark"></i>{{ t('tour_policy_cancel_short') }}
        </a>
    @endif
    @if(!empty($item->content->note))
        <a href="#luu-y-khi-tham-gia-chuong-trinh-tour" title="{{ t('tour_notes_short') }}" class="tocContentTour_item">
            <i class="fa-solid fa-circle-exclamation"></i>{{ t('tour_notes_short') }}
        </a>
    @endif
    @if(!empty($item->content->menu))
        <a href="#thuc-don-theo-chuong-trinh-tour" title="{{ t('tour_menu_short') }}" class="tocContentTour_item">
            <i class="fa-solid fa-utensils"></i>{{ t('tour_menu_short') }}
        </a>
    @endif
    @if(!empty($item->content->hotel))
        <a href="#khach-san-tham-khao" title="{{ t('tour_reference_hotel') }}" class="tocContentTour_item">
            <i class="fa-solid fa-bed"></i>{{ t('tour_reference_hotel') }}
        </a>
    @endif
    @if(!empty($item->questions)&&$item->questions->isNotEmpty())
        <a href="#cau-hoi-thuong-gap" title="{{ t('faq') }}" class="tocContentTour_item">
            <i class="fa-solid fa-circle-question"></i>{{ t('faq') }}
        </a>
    @endif
    @if(!empty($related)&&$related->isNotEmpty())
        <a href="#tour-lien-quan" title="{{ t('tour_related') }}" class="tocContentTour_item">
            <i class="fa-solid fa-person-walking-luggage"></i>{{ t('tour_related') }}
        </a>
    @endif
    {{-- <a href="#" class="tocContentTour_item">
        <i class="fa-solid fa-images"></i>Ảnh đẹp Tour
    </a> --}}
</div>

@php
    $locations = collect($item->locations ?? []);
    $flagQcCombo        = false;
    foreach($locations as $location){
        if(!empty($location->infoLocation->comboLocations)&&$location->infoLocation->comboLocations->isNotEmpty()){
            $flagQcCombo  = true;
            break;
        }
    }
@endphp
@if($flagQcCombo==true)
    <div class="serviceRelatedSidebarBox">
        <div class="serviceRelatedSidebarBox_title callUseService">
            @php
                $flagIsland         = false;
                foreach($locations as $location){
                    if($location->infoLocation->island==1) {
                        $flagIsland = true;
                    }
                }
            @endphp
        <h2><i class="fa-solid fa-star"></i> {{ t('tour_combo_suggestion', ['brand' => config('company.sortname'), 'service' => $flagIsland==true ? t('high_speed_ferry') : t('service_ticket')]) }}</h2>
        </div>
        <div class="serviceRelatedSidebarBox_box">
            @foreach($locations as $location)
                @foreach($location->infoLocation->comboLocations as $comboLocation)
                    <!-- combo du lịch -->
                    <a href="/{{ $comboLocation->infoCombolocation->seo->slug_full }}" title="{{ $comboLocation->infoCombolocation->name }}" class="serviceRelatedSidebarBox_box_item">
                        <i class="fa-solid fa-award"></i><h3>{{ $comboLocation->infoCombolocation->name }}</h3>
                    </a>
                @endforeach
            @endforeach
        </div>
    </div>
@endif

@push('scripts-custom')
    <script type="text/javascript">
        $(window).ready(function(){
            /* tính toán chiều cao sidebar */
            const heightW       = $(window).height();
            const heightBox     = $('#js_buildTocContentSidebar_idWrite').parent().outerHeight();
            const heightElemt   = $('#js_buildTocContentSidebar_idWrite').outerHeight();
            const height        = parseInt(heightW) - parseInt(heightBox - heightElemt);
            $('#js_buildTocContentSidebar_idWrite').css('max-height', 'calc('+height+'px - 3rem)');
        });
    </script>
@endpush