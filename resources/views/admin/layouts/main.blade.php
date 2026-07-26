<!DOCTYPE html>
<html lang="vi">

<!-- === START:: Head === -->
<head>
    @include('admin.snippets.head')
</head>
<!-- === END:: Head === -->

<!-- === START:: Body === -->
<body class="pace-done vertical-layout vertical-menu-modern navbar-floating footer-static menu-expanded" data-open="click" data-menu="vertical-menu-modern" data-col>
    <!-- === START:: Header === -->
    {{-- <div class="headerTop">
        <div class="headerTop_phone"><i class="fa-solid fa-phone"></i>0388.189.089</div>
        <div class="headerTop_text">Chuyến tàu Văn Học - Học văn không khó vì có Cô Ngọc Anh!</div>
    </div> --}}
    @include('admin.snippets.menu')
    <!-- === END:: Header === -->

    <!-- === START:: Breadcrumb === -->
    {{-- @if(Route::current()->uri!=='/')
        @include('snippets.breadcrumb')
    @endif --}}
    
    <!-- === END:: Breadcrumb === -->

    {{-- V3.1: CSS banner ngôn ngữ được load ở mọi trang admin entity edit --}}
    @if(!empty($translationMode) || !empty($translationOriginSeo ?? null))
        <link rel="stylesheet" href="{{ asset('css/admin/translation-mode.css') }}?v={{ @filemtime(public_path('css/admin/translation-mode.css')) ?: time() }}">
    @endif

    <!-- === START:: Content === -->
    <div class="app-content content">
        <div class="content-overlay"></div>

        {{-- V3.1: Banner đa ngôn ngữ (đặt INSIDE .app-content để respect sidebar layout) ===
             - translation mode (locale != default): banner CAM (translationModeBanner)
             - default locale: banner XANH (translationOriginBanner) — auto-inject qua View Composer --}}
        @isset($translationMode)
            @if($translationMode)
                @include('admin.snippets.translationModeBanner')
            @endif
        @endisset
        @if(empty($translationMode) && !empty($translationOriginSeo ?? null))
            @include('admin.snippets.translationOriginBanner')
        @endif

        @yield('content')
    </div>

    <!-- === START:: Footer === -->
    {{-- @include('snippets.footer') --}}
    <!-- === END:: Footer === -->
    
    <!-- === START:: Scripts Default === -->
    @include('admin.snippets.scripts-default')
    <!-- === END:: Scripts Default === -->

    <!-- === START:: Scripts Custom === -->
    @stack('styles-custom')
    @stack('scripts-custom')
    <!-- === END:: Scripts Custom === -->

    {{-- V3.1: Translation mode JS --}}
    @php
        $isTranslationMode = !empty($translationMode);
        $hasTranslationOrigin = !empty($translationOriginSeo ?? null);
    @endphp
    @if($isTranslationMode || $hasTranslationOrigin)
        @php
            $tmLocale     = $translationLocale ?? '';
            $tmSeoId      = ($translationSeo ?? null) ? $translationSeo->id : 0;
            $tmEntityId   = ($translationEntity ?? null) ? $translationEntity->id : 0;
            $tmEntityType = ($translationSeo ?? null) ? $translationSeo->type : '';
            $tmDefCode    = $translationDefaultCode ?? config('language.default_code', 'vi');
            $tmLang       = [
                'code' => $translationLanguage->code ?? '',
                'name' => $translationLanguage->name ?? '',
                'flag' => $translationLanguage->flag ?? '',
            ];
            $tmDefLang    = [
                'code' => optional($translationDefaultLang ?? null)->code,
                'name' => optional($translationDefaultLang ?? null)->name,
            ];
            $tmInputs     = $translatableInputs ?? [];
            $tmSaveUrl    = ($tmLocale !== '' && $tmSeoId > 0) ? route('admin.translation.save', [$tmLocale, $tmSeoId]) : '';
            $tmBackUrl    = \App\Http\Controllers\AdminTranslationController::backUrlForType($tmEntityType, $tmEntityId);
            $tmBodyTrans  = $translationBodyContent ?? '';
            $tmBodyDefault = $translationBodyDefault ?? '';
            $tmSupportsBodyContent = (bool) ($translationSupportsBodyContent ?? true);
            $tmPreviewUrl = $translationPreviewUrl ?? $translationOriginPreviewUrl ?? '';
            $tmAiDraftUrl = ($tmLocale !== '' && $tmSeoId > 0) ? route('admin.translation.aiDraft', [$tmLocale, $tmSeoId]) : '';
            $tmAiSourceUrl = ($tmLocale !== '' && $tmSeoId > 0) ? route('admin.translation.aiSource', [$tmLocale, $tmSeoId]) : '';
            $tmAiTranslateFieldUrl = ($tmLocale !== '' && $tmSeoId > 0) ? route('admin.translation.aiTranslateField', [$tmLocale, $tmSeoId]) : '';
            $tmAiModels = config('ai.models', [config('ai.openai.model', 'gpt-4o-mini')]);
            $tmAiPromptTemplateListUrl = route('admin.aiPromptTemplate.list');
            $tmAiPromptTemplateSaveUrl = route('admin.aiPromptTemplate.save');
            $tmAiPromptTemplateDeleteUrl = route('admin.aiPromptTemplate.delete');
        @endphp
        <script>
            window.TRANSLATION_MODE             = @json($isTranslationMode);
            window.TRANSLATION_LOCALE           = @json($tmLocale);
            window.TRANSLATION_SEO_ID           = @json($tmSeoId);
            window.TRANSLATION_DEFAULT_CODE     = @json($tmDefCode);
            window.TRANSLATION_LANGUAGE         = @json($tmLang);
            window.TRANSLATION_DEFAULT_LANGUAGE = @json($tmDefLang);
            window.TRANSLATABLE_INPUTS          = @json($tmInputs);
            window.TRANSLATION_SAVE_URL         = @json($tmSaveUrl);
            window.TRANSLATION_BACK_URL         = @json($tmBackUrl);
            window.TRANSLATION_ENTITY_TYPE      = @json($tmEntityType);
            window.TRANSLATION_BODY_CONTENT     = @json($tmBodyTrans);
            window.TRANSLATION_BODY_DEFAULT     = @json($tmBodyDefault);
            window.TRANSLATION_SUPPORTS_BODY_CONTENT = @json($tmSupportsBodyContent);
            window.TRANSLATION_PREVIEW_URL      = @json($tmPreviewUrl);
            window.TRANSLATION_AI_DRAFT_URL     = @json($tmAiDraftUrl);
            window.TRANSLATION_AI_SOURCE_URL    = @json($tmAiSourceUrl);
            window.TRANSLATION_AI_TRANSLATE_FIELD_URL = @json($tmAiTranslateFieldUrl);
            window.TRANSLATION_AI_MODELS        = @json($tmAiModels);
            window.TRANSLATION_AI_PROMPT_TEMPLATE_LIST_URL   = @json($tmAiPromptTemplateListUrl);
            window.TRANSLATION_AI_PROMPT_TEMPLATE_SAVE_URL   = @json($tmAiPromptTemplateSaveUrl);
            window.TRANSLATION_AI_PROMPT_TEMPLATE_DELETE_URL = @json($tmAiPromptTemplateDeleteUrl);
        </script>
        <link rel="stylesheet" href="{{ asset('css/admin/translation-mode.css') }}?v={{ @filemtime(public_path('css/admin/translation-mode.css')) ?: time() }}">
        <script src="{{ asset('js/admin/translation-mode.js') }}?v={{ @filemtime(public_path('js/admin/translation-mode.js')) ?: time() }}"></script>
    @endif
</body>
<!-- === END:: Body === -->

</html>