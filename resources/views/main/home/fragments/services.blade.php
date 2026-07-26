@if(!empty($list) && $list->isNotEmpty())
    @include('main.serviceLocation.serviceItem', [
        'list'          => $list,
        'showFilterBox' => false,
    ])
@endif
