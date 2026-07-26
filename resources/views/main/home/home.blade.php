@extends('main.layouts.main')
@push('head-custom')
<!-- ===== START:: SCHEMA ===== -->
<!-- STRAT:: Title - Description - Social -->
@include('main.schema.social', ['data' => $item])
<!-- END:: Title - Description - Social -->

<!-- STRAT:: Organization Schema -->
@include('main.schema.organization')
<!-- END:: Organization Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.article', ['data' => $item])
<!-- END:: Article Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.creativeworkseries', ['data' => $item])
<!-- END:: Article Schema -->

<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')
    @include('main.home.slider')

    <div class="pageHome">
    <!-- START: Sort Booking -->
    @php
        $active = 'ship';
    @endphp
    @include('main.form.formBooking', compact('active'))
    <!-- END: Sort Booking -->

    <!-- START: Điểm đến nổi bật -->
    @if(!empty($islandLocations)&&$islandLocations->isNotEmpty())
        <div class="sectionBox withBorder">
            <div class="container">
                <h2 class="sectionBox_title">{{ t('home_section_special') }}</h2>
                <p class="sectionBox_desc">{{ t('home_section_special_desc') }}</p>
                @include('main.home.specialLocation', compact('specialLocations'))
            </div>
        </div>
    @endif
    <!-- END: Điểm đến nổi bật -->

    @if(!single_island_mode())
    <!-- START: Điểm đến biển đảo -->
    @if(!empty($islandLocations)&&$islandLocations->isNotEmpty())
        <div class="sectionBox withBorder">
            <div class="container">
                <h2 class="sectionBox_title">{{ t('home_section_island') }}</h2>
                <p class="sectionBox_desc">{{ t('home_section_island_desc') }}</p>
                @include('main.home.islandLocation', compact('islandLocations'))
            </div>
        </div>
    @endif
    <!-- END: Điểm đến biển đảo -->
    @endif
    

    <!-- START: Khách sạn trong nước -->
    @if(!empty($hotelLocations)&&$hotelLocations->isNotEmpty())
        <div class="sectionBox withBorder sectionBox--hotelDomestic">
            <div class="container">
                @include('main.home.hotelLocationList', compact('hotelLocations'))
            </div>
        </div>
    @endif
    <!-- END: Khách sạn trong nước -->

    <!-- START: Tàu cao tốc -->
    @if(!empty($shipLocations)&&$shipLocations->isNotEmpty())
        <div class="sectionBox withBorder sectionBox--shipTickets">
            <div class="container">
                <h2 class="sectionBox_title">{{ t('home_section_ship') }}</h2>
                <p class="sectionBox_desc">{{ t('home_section_ship_desc') }}</p>
                @include('main.home.shipLocationList', compact('shipLocations'))
            </div>
        </div>
    @endif
    <!-- END: Tàu cao tốc -->

    <!-- START: Vé vui chơi giải trí -->
    @if(!empty($serviceLocations)&&$serviceLocations->isNotEmpty())
        <div class="sectionBox withBorder">
            <div class="container">
                <h2 class="sectionBox_title">{{ t('home_section_entertainment') }}</h2>
                <p class="sectionBox_desc">{{ t('home_section_entertainment_desc') }}</p>
                @include('main.snippets.pageFragment', [
                    'fragmentKind' => 'home',
                    'url' => $fragmentUrls['services'] ?? '',
                    'seoId' => $item->id ?? 0,
                    'locale' => app()->getLocale(),
                    'section' => 'services',
                    'minHeight' => 400,
                    'skeleton' => 'tourGrid',
                    'ariaLabel' => t('home_section_entertainment'),
                ])
                {{-- @include('main.home.serviceLocationList', compact('serviceLocations')) --}}
            </div>
        </div>
    @endif
    <!-- END: Vé vui chơi giải trí -->

    <!-- START: Đối tác tàu cao tốc -->
    @if(!empty($shipPartners)&&$shipPartners->isNotEmpty())
        <div class="sectionBox sectionBox--homePartners noBackground">
            <div class="container">
                @include('main.home.partner', [
                    'list'          => $shipPartners,
                    'title'         => t('home_partner_ship'),
                    'description'   => t('home_partner_ship_desc'),
                    'variant'       => 'ship',
                    'label'         => t('home_partner_ship_label'),
                ])
            </div>
        </div>
    @endif
    <!-- END: Đối tác tàu cao tốc -->

    @if(module_enabled('air'))
    <!-- START: Đối tác máy bay -->
    @if(!empty($airPartners)&&$airPartners->isNotEmpty())
        <div class="sectionBox sectionBox--homePartners noBackground">
            <div class="container">
                @include('main.home.partner', [
                    'list'          => $airPartners,
                    'title'         => t('home_partner_air'),
                    'description'   => t('home_partner_air_desc'),
                    'variant'       => 'air',
                    'label'         => t('home_partner_air_label'),
                ])
            </div>
        </div>
    @endif
    <!-- END: Đối tác máy bay -->
    @endif

    </div><!-- /.pageHome -->

    {{-- <div class="sectionBox">
        <div class="container">
            @include('main.home.blogListNoImage')
        </div>
    </div> --}}
    
@endsection
@include('main.snippets.pageFragmentsScript')
@push('scripts-custom')
    <script type="text/javascript">
        $(document).ready(function(){
            setSlick();
        })
        $(window).resize(function(){
            setSlick();
        })
        function setSlick(){
            $('.serviceGrid').slick({
                infinite: false,
                slidesToShow: 3,
                slidesToScroll: 3,
                arrows: true,
                prevArrow: '<button class="slick-arrow slick-prev" aria-label="{{ t('previous_slide') }}"><i class="fa-solid fa-angle-left"></i></button>',
                nextArrow: '<button class="slick-arrow slick-next" aria-label="{{ t('next_slide') }}"><i class="fa-solid fa-angle-right"></i></button>',
                responsive: [
                    {
                        breakpoint: 1023,
                        settings: {
                            infinite: false,
                            slidesToShow: 2.6,
                            slidesToScroll: 2,
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 767,
                        settings: {
                            infinite: false,
                            slidesToShow: 1.7,
                            slidesToScroll: 1,
                            arrows: true,
                        }
                    },
                    {
                        breakpoint: 577,
                        settings: 'unslick'
                    }
                ]
            });
        }
    </script>
@endpush