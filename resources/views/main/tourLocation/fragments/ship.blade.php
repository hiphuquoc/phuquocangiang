@if(!empty($list) && $list->isNotEmpty() && !empty($item->shipLocations[0]->infoShipLocation->seo->slug_full))
    @include('main.shipLocation.shipGridMerge', [
        'list'        => $list,
        'limit'       => 3,
        'link'        => $item->shipLocations[0]->infoShipLocation->seo->slug_full,
        'itemHeading' => 'h3',
    ])
@endif
