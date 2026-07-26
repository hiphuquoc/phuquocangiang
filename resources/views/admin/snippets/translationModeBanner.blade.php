{{--
    V3.1.2: Translation Mode Banner — TRANG ĐANG DỊCH (locale != default).

    Layout 2 hàng:
      - Hàng 1: thông điệp + nút "Bản gốc" (không ép cùng hàng với 10+ nút ngôn ngữ)
      - Hàng 2: switcher full width, chip wrap tự nhiên
--}}
@php
    $defaultCode = $translationDefaultCode ?? config('language.default_code', 'vi');
    $allLangs    = $translationAllLanguages ?? collect();
    $entityId    = $translationEntity->id ?? 0;
    $seoId       = $translationSeo->id ?? 0;
    $entityType  = $translationSeo->type ?? '';
    $statusMap   = $translationStatusMap ?? [];
    $backUrl     = \App\Http\Controllers\AdminTranslationController::backUrlForType($entityType, $entityId);

    $renderFlag = function ($lang, $sizeClass = 'translationFlag--md') {
        if (!$lang) return '';
        $flag = $lang->flag ?? '';
        if (!empty($flag) && (str_starts_with($flag, '/') || str_starts_with($flag, 'http'))) {
            return '<img src="' . e($flag) . '" alt="' . e($lang->code) . '" class="translationFlag ' . e($sizeClass) . '">';
        }
        return '<span class="translationFlag translationFlag--text ' . e($sizeClass) . '">' . e($flag ?: strtoupper(substr($lang->code, 0, 2))) . '</span>';
    };
@endphp

<div class="translationBanner translationBanner--mode">
    <div class="translationBanner_inner">
        <div class="translationBanner_top">
            <div class="translationBanner_left">
                <i class="fa-solid fa-language translationBanner_icon" aria-hidden="true"></i>
                <div class="translationBanner_text">
                    <div class="translationBanner_title">
                        <span>Đang chỉnh sửa bản dịch:</span>
                        <span class="translationBanner_currentFlag">{!! $renderFlag($translationLanguage, 'translationFlag--lg') !!}</span>
                        <strong>{{ strtoupper($translationLocale ?? '') }} — {{ $translationLanguage->name ?? '' }}</strong>
                    </div>
                    <div class="translationBanner_subtext">
                        Trường <span class="translationBanner_legend translationBanner_legend--editable">tô vàng</span> dịch được. Trường <span class="translationBanner_legend translationBanner_legend--locked">tô xám</span> hiển thị bản gốc <strong>{{ strtoupper($defaultCode) }}</strong>, không sửa được tại đây.
                    </div>
                </div>
            </div>
            <a href="{{ $backUrl }}" class="translationBanner_btnBack" title="Quay lại trang gốc">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Bản gốc</span>
            </a>
        </div>

        <div class="translationBanner_bottom">
            <div class="translationBanner_switcher">
                <span class="translationBanner_switcherLabel">Phiên bản</span>
                <div class="translationBanner_switcherList" role="group" aria-label="Chọn ngôn ngữ">
                    @php $defLang = $allLangs->firstWhere('code', $defaultCode); @endphp
                    @if($defLang)
                        <a href="{{ $backUrl }}"
                           class="translationBanner_switcherItem has-translation {{ $translationLocale === $defaultCode ? 'is-active' : '' }}"
                           title="{{ $defLang->name }} (bản gốc)">
                            {!! $renderFlag($defLang, 'translationFlag--sm') !!}
                            <span class="translationBanner_switcherCode">{{ strtoupper($defLang->code) }}</span>
                        </a>
                    @endif
                    @foreach($allLangs as $lang)
                        @if($lang->code === $defaultCode) @continue @endif
                        @php
                            $hasTrans = !empty($statusMap[$lang->id]);
                            $stateClass = $translationLocale === $lang->code
                                ? 'is-active'
                                : ($hasTrans ? 'has-translation' : 'no-translation');
                        @endphp
                        <a href="{{ route('admin.translation.edit', [$lang->code, $seoId]) }}"
                           class="translationBanner_switcherItem {{ $stateClass }}"
                           title="{{ $lang->name }} — {{ $hasTrans ? 'Đã dịch' : 'Chưa có bản dịch' }}">
                            {!! $renderFlag($lang, 'translationFlag--sm') !!}
                            <span class="translationBanner_switcherCode">{{ strtoupper($lang->code) }}</span>
                            @if($hasTrans && $translationLocale !== $lang->code)
                                <i class="fa-solid fa-check translationBanner_switcherCheck" aria-hidden="true"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
