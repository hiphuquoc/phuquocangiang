@if(!empty($list) && $list->isNotEmpty())
    @include('main.comboLocation.comboItem', ['list' => $list])
@endif
