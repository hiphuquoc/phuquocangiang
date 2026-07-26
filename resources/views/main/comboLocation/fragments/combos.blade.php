@if(!empty($list) && $list->isNotEmpty())
    @include('main.comboLocation.comboItem', ['list' => $list])
@else
    <div class="pageFragment_empty" style="color:rgb(0,123,255);">
        {!! t('combo_location_empty', ['name' => e($item->display_name ?? ''), 'brand' => e(config('main.name'))]) !!}
    </div>
@endif
