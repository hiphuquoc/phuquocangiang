{{--
    Skeleton thống nhất cho pageFragment (tour, combo, air, service, ship).
--}}
<div class="pageFragment_skeletonGrid">
    @if(($skeleton ?? '') === 'sdProductGrid')
        @include('main.tourLocation-v2.snippets.product-grid-skeleton')
    @else
        @include('main.tourLocation.loadingGridBox', ['loaderClass' => 'loadingGridBox--pageFragment'])
    @endif
</div>
