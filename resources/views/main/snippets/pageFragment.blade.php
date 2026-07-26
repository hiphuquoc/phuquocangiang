{{--
    Shell placeholder: cache HTML không chứa giá theo currency.
    Nội dung thật tải qua AJAX vào .pageFragment_content.
--}}
@php
    $fragmentId = $fragmentId ?? ('pageFragment-' . ($section ?? 'block') . '-' . uniqid());
    $minHeight  = (int) ($minHeight ?? 360);
    $skeleton   = $skeleton ?? 'tourGrid';
    if ($skeleton === 'compact') {
        $skeleton = 'shipGrid';
    }
    $skeletonClass = match ($skeleton) {
        'sdProductGrid' => 'pageFragment--sdProductGrid',
        default => 'pageFragment--' . $skeleton,
    };
@endphp
@once
    @push('head-custom')
    <style>
        .pageFragment--loading .loadingGridBox--pageFragment{display:flex!important;flex-wrap:wrap}
        .pageFragment--loaded,.pageFragment:not(.pageFragment--loading){min-height:0!important}
        .pageFragment--loaded .pageFragment_skeleton,
        .pageFragment:not(.pageFragment--loading) .pageFragment_skeleton,
        .pageFragment--loaded .loadingGridBox--pageFragment,
        .pageFragment--loaded .sd-product-grid--skeleton,
        .pageFragment--loaded .pageFragment_content .loadingGridBox{display:none!important;height:0!important;overflow:hidden!important;margin:0!important;padding:0!important;visibility:hidden!important}
    </style>
    @endpush
@endonce
<div id="{{ $fragmentId }}"
    class="pageFragment {{ $skeletonClass }} pageFragment--loading"
    data-page-fragment
    data-fragment-url="{{ $url ?? '' }}"
    data-fragment-kind="{{ $fragmentKind ?? 'tour-location' }}"
    data-fragment-section="{{ $section ?? '' }}"
    data-fragment-seo-id="{{ $seoId ?? '' }}"
    data-fragment-locale="{{ $locale ?? app()->getLocale() }}"
    data-fragment-default-locale="{{ config('language.default_code', 'vi') }}"
    style="--page-fragment-min-height: {{ $minHeight }}px;"
    role="region"
    aria-busy="true"
    aria-live="polite"
    aria-label="{{ $ariaLabel ?? '' }}">
    <div class="pageFragment_inner">
        <div class="pageFragment_skeleton" aria-hidden="true">
            @include('main.snippets.pageFragmentSkeleton', ['skeleton' => $skeleton])
        </div>
        <div class="pageFragment_content"></div>
    </div>
</div>
