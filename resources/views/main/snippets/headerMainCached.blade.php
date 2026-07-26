{{--
    Menu desktop + mobile (headerMain) — cache HTML riêng theo ngôn ngữ:
    public/caches/menuMain_{locale}.html(.gz)
--}}
{!! app(\App\Services\HtmlCacheService::class)->getOrRenderMenu(
    current_locale(),
    fn () => view('main.snippets.headerMain')->render()
) !!}
