@if(!empty($list) && $list->isNotEmpty())
    @include('main.serviceLocation.serviceItem', [
        'list'          => $list,
        'catalogId'     => 'js_serviceFilter_parent',
        'hiddenId'      => 'js_serviceFilter_hidden',
    ])
@else
    <div class="pageFragment_empty" style="color:rgb(0,123,255);">
        {!! t('service_location_empty', ['name' => e($item->display_name ?? $item->name ?? ''), 'brand' => e(config('main.name'))]) !!}
    </div>
@endif
