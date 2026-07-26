@php
    // $tourDestCount = (int) ($item->tours_count ?? 0);
    $textClass = $textClass ?? 'megaMenu_islandDestLink__text';
@endphp
<span class="megaMenu_destTitleWrap">
    <span class="{{ $textClass }} maxLine_2">{{ $item->name ?? $item->seo->title ?? null }}</span>
    {{-- Tạm ẩn badge đếm tour trong megaMenu_content
    @if($tourDestCount > 0)
        <span class="megaMenu_destTourBadge" aria-label="{{ t('mega_tt_country_tour_count', ['count' => $tourDestCount]) }}">{{ $tourDestCount }}</span>
    @endif
    --}}
</span>
