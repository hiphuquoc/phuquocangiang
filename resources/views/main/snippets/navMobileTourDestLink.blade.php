@php
    $tourDestCount = (int) ($item->tours_count ?? 0);
@endphp
<a class="nav-mobile_destLink" href="{{ seo_url($item) }}" title="{{ $item->name ?? $item->seo->title ?? null }}">
    <span class="nav-mobile_destLink__titleWrap">
        <span class="nav-mobile_destLink__name">{{ $item->name ?? $item->seo->title ?? null }}</span>
        @if($tourDestCount > 0)
            <span class="nav-mobile_destLink__badge" aria-label="{{ t('mega_tt_country_tour_count', ['count' => $tourDestCount]) }}">{{ $tourDestCount }}</span>
        @endif
    </span>
</a>
