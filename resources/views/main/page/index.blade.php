@extends('main.layouts.main')
@push('head-custom')
<!-- ===== START:: SCHEMA ===== -->
@php
    $dataSchema = $item->seo ?? null;
@endphp

<!-- STRAT:: Title - Description - Social -->
@include('main.schema.social', ['data' => $dataSchema])
<!-- END:: Title - Description - Social -->

<!-- STRAT:: Organization Schema -->
@include('main.schema.organization')
<!-- END:: Organization Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.article', ['data' => $dataSchema])
<!-- END:: Article Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.creativeworkseries', ['data' => $dataSchema])
<!-- END:: Article Schema -->

<!-- STRAT:: Article Schema -->
@include('main.schema.breadcrumb', ['data' => $breadcrumb])
<!-- END:: Article Schema -->

<!-- ===== END:: SCHEMA ===== -->
@endpush
@section('content')

    @include('main.snippets.breadcrumb')

    <div class="sectionBox">
        <div class="container">
            <!-- title -->
            <h1 class="titlePage">{{ $item->name ?? null }}</h1>
            {{-- <!-- rating -->
            @include('main.template.rating', compact('item')) --}}
            {{-- <!-- tocContent main -->
            <div id="tocContentMain" style="margin-top:1rem;"></div> --}}
            <!-- content -->
            {!! $content ?? null !!}
        </div>
    </div>

    @if(!empty($item->show_partner)&&$item->show_partner==1)
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

@endsection
@push('scripts-custom')
    <script type="text/javascript">
        // buildTocContentMain('js_buildTocContentSidebar_element');
    </script>
@endpush