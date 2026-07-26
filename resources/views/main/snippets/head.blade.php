
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{{-- ============================================================
     TEMP (i18n) — chặn index cho các locale chưa dịch xong.
     Locale mặc định (vi): index,follow như cũ.
     Locale khác:          noindex,nofollow để Google không index.
     Khi dịch xong, mở lại: xem docs/i18n-noindex-temporary.md
============================================================ --}}
@if(is_default_locale())
<meta name="robots" content="index,follow">
@else
<meta name="robots" content="noindex,nofollow">
@endif
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="fragment" content="!" />

{{-- ====== Multilingual SEO meta ======
     - hreflang cho mọi locale có translation `published`
     - canonical theo locale hiện tại
     - x-default trỏ về default locale
     Yêu cầu: blade nào có $data (Seo/entity với ->seo) sẽ có alternates;
     blade không có $data sẽ chỉ render hreflang chung cho HOMEPAGE.
--}}
@php
    $__seoSource = $data ?? ($item->seo ?? null) ?? ($itemSeo ?? null) ?? null;
    $__alternates = $__seoSource
        ? \App\Helpers\SeoAlternates::for($__seoSource)
        : collect();
    $__canonical = null;
    if ($__seoSource) {
        $__canonical = \App\Helpers\SeoAlternates::urlFor($__seoSource, current_locale());
    }
    if (empty($__canonical)) {
        // Fallback: home / current path tương ứng locale hiện tại
        $__canonical = url(request()->getRequestUri());
    }
    $__xDefault = $__seoSource ? \App\Helpers\SeoAlternates::xDefaultUrl($__seoSource) : null;
@endphp
<link rel="canonical" href="{{ $__canonical }}" />
@if($__alternates->isNotEmpty())
    @foreach($__alternates as $__alt)
        <link rel="alternate" hreflang="{{ $__alt['code'] }}" href="{{ $__alt['url'] }}" />
    @endforeach
    @if($__xDefault)
        <link rel="alternate" hreflang="x-default" href="{{ $__xDefault }}" />
    @endif
@else
    {{-- Fallback hreflang cho trang không gắn entity SEO (vd home) --}}
    @foreach(\App\Models\Language::active() as $__lang)
        @php
            $__path = '/' . ltrim(request()->path(), '/');
            $__segs = array_values(array_filter(explode('/', $__path)));
            if (!empty($__segs) && \App\Models\Language::byCode($__segs[0])) array_shift($__segs);
            $__rest = implode('/', $__segs);
            $__prefix = $__lang->is_default ? '' : '/' . $__lang->code;
            $__href = rtrim(env('APP_URL'), '/') . $__prefix . ($__rest ? '/' . $__rest : '');
        @endphp
        <link rel="alternate" hreflang="{{ $__lang->code }}" href="{{ $__href }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ rtrim(env('APP_URL'), '/') }}/" />
@endif

@stack('head-custom')

<!-- BEGIN: Custom CSS-->
@vite(['resources/sources/main/style.scss'])
<!-- END: Custom CSS-->

<link rel="icon" href="{{ Storage::url('images/svg/favicon-hitour.ico') }}" type="image/x-icon">

<!-- BEGIN: FONT AWESOME -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<!-- END: FONT AWESOME -->

<!-- BEGIN: SLICK -->
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<!-- END: SLICK -->

<style type="text/css">
    /* @font-face {
        font-family: 'Segoe-UI Light';
        font-style: normal;
        font-weight: 400;
        src: url('/fonts/SegoeUI-Light.ttf');
        font-display: swap;
    } */

    @font-face {
        font-family: 'Segoe-UI';
        font-style: normal;
        font-weight: 500;
        src: url('/fonts/SegoeUI.ttf');
        font-display: swap;
    }

    @font-face {
        font-family: 'Segoe-UI Semi';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url('/fonts/SegoeUI-SemiBold.ttf');
    }

    @font-face {
        font-family: 'Segoe-UI Bold';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url('/fonts/SegoeUI-Bold.ttf');
    }

    /* @font-face {
        font-family: 'SVN-Gilroy Thin';
        font-style: normal;
        font-weight: 400;
        src: url(//bizweb.dktcdn.net/100/438/408/themes/919724/assets/svn-gilroy_regular.ttf?1698220622470);
        font-display: 'Swap';
    }

    @font-face {
        font-family: 'SVN-Gilroy Light';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(//bizweb.dktcdn.net/100/438/408/themes/919724/assets/svn-gilroy_regular.ttf?1698220622470);
    } */

    @font-face {
        font-family: 'SVN-Gilroy';
        font-style: normal;
        font-display: swap;
        font-weight: 500;
        src: url('/fonts/svn-gilroy_medium.ttf');
    }

    @font-face {
        font-family: 'SVN-Gilroy Med';
        font-style: normal;
        font-display: swap;
        font-weight: 700;
        src: url('/fonts/svn-gilroy_med.ttf');
    }

    @font-face {
        font-family: 'SVN-Gilroy Semi';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url('/fonts/svn-gilroy_semibold.ttf');
    }

    @font-face {
        font-family: 'SVN-Gilroy Bold';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url('/fonts/svn-gilroy_semibold.ttf');
    }
</style>

<!-- BEGIN: Jquery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- END: Jquery -->