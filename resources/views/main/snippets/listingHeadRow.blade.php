{{--
    Kicker (badge) + tiêu đề cùng dòng — chuẩn chung (video, section box, explore, FAQ...).
--}}
@php
    $tag = $tag ?? 'h2';
    $allowHtml = !empty($allowHtml);
    $centered = !empty($centered);
    if (!isset($withSectionTitleClass)) {
        $withSectionTitleClass = ($tag === 'h2');
    } else {
        $withSectionTitleClass = (bool) $withSectionTitleClass;
    }
    $titleClasses = trim(($titleClass ?? '') . ($withSectionTitleClass ? ' sectionBox_title' : ''));
@endphp
<div class="listingHeadRow{{ $centered ? ' listingHeadRow--center' : '' }}{{ !empty($wrapperClass) ? ' ' . $wrapperClass : '' }}">
    @if(!empty($kicker))
        <span class="listingHeadRow_kicker">{{ $kicker }}</span>
    @endif
    @if($allowHtml && isset($titleHtml))
        <{{ $tag }}
            @if(!empty($id)) id="{{ $id }}" @endif
            class="listingHeadRow_title{{ $titleClasses !== '' ? ' ' . $titleClasses : '' }}"
        >{!! $titleHtml !!}</{{ $tag }}>
    @elseif(!empty($titleUrl))
        <a href="{{ $titleUrl }}" class="listingHeadRow_titleLink"@if(!empty($titleAttr)) title="{{ $titleAttr }}"@endif>
            <{{ $tag }}
                @if(!empty($id)) id="{{ $id }}" @endif
                class="listingHeadRow_title{{ $titleClasses !== '' ? ' ' . $titleClasses : '' }}"
            >{{ $title ?? '' }}</{{ $tag }}>
        </a>
    @else
        <{{ $tag }}
            @if(!empty($id)) id="{{ $id }}" @endif
            class="listingHeadRow_title{{ $titleClasses !== '' ? ' ' . $titleClasses : '' }}"
        >{{ $title ?? '' }}</{{ $tag }}>
    @endif
</div>
