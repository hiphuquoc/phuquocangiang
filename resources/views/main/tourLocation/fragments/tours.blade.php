@if(!empty($list) && $list->isNotEmpty())
    @include('main.tourLocation.tourItem', ['list' => $list, 'withFilterLoader' => false])
@else
    <div class="pageFragment_empty" style="color:rgb(0,123,255);">
        {!! t('tour_list_empty', ['name' => e($item->display_name ?? ''), 'brand' => e(config('main.name'))]) !!}
    </div>
@endif
