@if(!empty($list) && $list->isNotEmpty())
    @include('main.shipLocation.shipGridMerge', ['list' => $list])
@endif
