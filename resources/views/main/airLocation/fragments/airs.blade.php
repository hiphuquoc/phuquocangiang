@if(!empty($list) && $list->isNotEmpty())
    @include('main.airLocation.airItem', [
        'list'        => $list,
        'itemHeading' => 'h2',
        'collapsible' => true,
    ])
@endif
