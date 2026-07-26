<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="{{ optional(current_language())->dir ?? 'ltr' }}" data-current-locale="{{ current_locale() }}">

<!-- === START:: Head === -->
<head>
    @include('main.snippets.head')
</head>
<!-- === END:: Head === -->

<!-- === START:: Body === -->
<body>
    <div id="js_openCloseModal_blur">
        <!-- === START:: Header === -->
        @include('main.snippets.headerTop')
        @include('main.snippets.headerMainCached')
        <!-- === END:: Header === -->

        <!-- === START:: Breadcrumb === -->
        {{-- @if(Route::current()->uri!=='/')
            @include('snippets.breadcrumb')
        @endif --}}
        <!-- === END:: Breadcrumb === -->

        <!-- === START:: Content === -->
        <div class="app-content content">
            <div class="content-overlay"></div>
            @yield('content')
        </div>

        <!-- === START:: Footer === -->
        @include('main.snippets.footer')
        <!-- === END:: Footer === -->

        <div class="bottom">
            <button type="button" id="gotoTop" class="gotoTop" title="{{ t('go_to_top') }}" aria-label="{{ t('go_to_top') }}" aria-hidden="true">
                <i class="fas fa-chevron-up" aria-hidden="true"></i>
            </button>
            @stack('bottom')
        </div>
    </div>

    <!-- Modal -->
    @stack('modal')
    @include('main.modal.messageModal')
    <!-- login form modal -->
    <div id="js_checkLoginAndSetShow_modal">
        <!-- tải ajaax checkLoginAndSetShow() -->
    </div>
    
    <!-- === START:: Scripts Default === -->
    @include('main.snippets.scripts-default')
    <!-- === END:: Scripts Default === -->

    <!-- === START:: Scripts Custom === -->
    @stack('scripts-custom')
    <!-- === END:: Scripts Custom === -->
</body>
<!-- === END:: Body === -->

</html>