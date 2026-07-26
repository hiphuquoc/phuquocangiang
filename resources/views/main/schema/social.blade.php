@php
    $title          = $data->seo_title ?? $data->title ?? config('main.description');
    $description    = $data->seo_description ?? $data->description ?? null;
    $url            = !empty($data->link_canonical) ? $data->link_canonical : $data->slug_full;

    // Locale hiện tại + alternates -> chuẩn cho og:locale + og:locale:alternate
    $__currentLang  = current_language();
    $__ogLocale     = $__currentLang->og_locale ?? 'vi_VN';
    $__alternates   = \App\Helpers\SeoAlternates::for($data);
    $__canonical    = \App\Helpers\SeoAlternates::urlFor($data, current_locale())
                        ?? rtrim(env('APP_URL'), '/') . (current_language() && !current_language()->is_default ? '/' . current_locale() : '') . '/' . ltrim($url ?? '', '/');
    $image          = !empty($data->image) ? rtrim(env('APP_URL'), '/') . $data->image : rtrim(env('APP_URL'), '/') . config('admin.images.default_750x460');
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}" />
<link rel="canonical" href="{{ $__canonical }}" />
<meta property="og:locale" content="{{ $__ogLocale }}" />
@foreach($__alternates as $__alt)
    @if(!$__alt['is_default'] && !empty($__alt['og_locale']) && $__alt['og_locale'] !== $__ogLocale)
        <meta property="og:locale:alternate" content="{{ $__alt['og_locale'] }}" />
    @endif
@endforeach
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:url" content="{{ $__canonical }}" />
<meta property="og:site_name" content="{{ $title }}" />
<meta property="article:published_time" content="{{ date('c', strtotime($data->created_at ?? 'now')) }}" />
<meta property="article:modified_time" content="{{ date('c', strtotime($data->updated_at ?? 'now')) }}" />
<meta property="og:image" content="{{ $image }}" />
<meta property="og:image:width" content="750" />
<meta property="og:image:height" content="460" />
<meta property="og:image:alt" content="{{ $title }}" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />
<meta name="twitter:creator" content="@@hitour" />
<meta name="twitter:image" content="{{ $image }}" />
