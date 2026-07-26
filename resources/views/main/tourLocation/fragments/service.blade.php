@if(!empty($list) && $list->isNotEmpty())
    @include('main.serviceLocation.serviceItem', [
        'list'          => $list,
        'itemHeading'   => 'h3',
        'slick'         => true,
        'showFilterBox' => false,
    ])
@endif
