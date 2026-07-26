@if(!empty($list) && $list->isNotEmpty())
    @include('main.airLocation.airItem', [
        'list'        => $list,
        'itemHeading' => 'h3',
        'collapsible' => true,
    ])
@endif
