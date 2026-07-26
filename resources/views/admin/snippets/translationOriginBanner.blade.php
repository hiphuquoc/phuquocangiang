{{--
    V3.1.2: Origin Banner — TRANG GỐC (default locale).

    Layout 2 hàng: thông điệp trên, switcher full width dưới (tránh tràn ngang).
--}}
@php
    $defaultCode = config('language.default_code', 'vi');
    $seo         = $translationOriginSeo ?? null;
    if (!$seo) return;

    $allLangs    = $translationOriginLanguages ?? collect();
    $current     = $translationOriginCurrent ?? null;
    $statusMap   = $translationOriginStatus ?? [];

    $renderFlag = function ($lang, $sizeClass = 'translationFlag--md') {
        if (!$lang) return '';
        $flag = $lang->flag ?? '';
        if (!empty($flag) && (str_starts_with($flag, '/') || str_starts_with($flag, 'http'))) {
            return '<img src="' . e($flag) . '" alt="' . e($lang->code) . '" class="translationFlag ' . e($sizeClass) . '">';
        }
        return '<span class="translationFlag translationFlag--text ' . e($sizeClass) . '">' . e($flag ?: strtoupper(substr($lang->code, 0, 2))) . '</span>';
    };

    $translatedCount = 0;
    foreach ($statusMap as $hasIt) if ($hasIt) $translatedCount++;
    $totalNonDefault = max(0, $allLangs->count() - 1);
@endphp

<div class="translationBanner translationBanner--origin">
    <div class="translationBanner_inner">
        <div class="translationBanner_top">
            <div class="translationBanner_left">
                <i class="fa-solid fa-flag translationBanner_icon" aria-hidden="true"></i>
                <div class="translationBanner_text">
                    <div class="translationBanner_title">
                        <span>Đang chỉnh sửa bản gốc:</span>
                        <span class="translationBanner_currentFlag">{!! $renderFlag($current, 'translationFlag--lg') !!}</span>
                        <strong>{{ strtoupper($defaultCode) }} — {{ $current->name ?? 'Tiếng Việt' }}</strong>
                    </div>
                    <div class="translationBanner_subtext">
                        Đây là phiên bản gốc — chỉnh sửa mọi trường (giá, ngày, FK, hình ảnh, nội dung). Bản dịch các ngôn ngữ khác:
                        <strong>{{ $translatedCount }}/{{ $totalNonDefault }}</strong> đã có nội dung.
                    </div>
                </div>
            </div>
        </div>

        <div class="translationBanner_bottom">
            <div class="translationBanner_switcher">
                <span class="translationBanner_switcherLabel">Phiên bản</span>
                <div class="translationBanner_switcherList" role="group" aria-label="Chọn ngôn ngữ">
                    @if($current)
                        <span class="translationBanner_switcherItem is-active is-current"
                              title="{{ $current->name }} (đang xem)">
                            {!! $renderFlag($current, 'translationFlag--sm') !!}
                            <span class="translationBanner_switcherCode">{{ strtoupper($current->code) }}</span>
                        </span>
                    @endif
                    @foreach($allLangs as $lang)
                        @if($lang->code === $defaultCode) @continue @endif
                        @php $hasTrans = !empty($statusMap[$lang->id]); @endphp
                        <a href="{{ route('admin.translation.edit', [$lang->code, $seo->id]) }}"
                           class="translationBanner_switcherItem {{ $hasTrans ? 'has-translation' : 'no-translation' }}"
                           title="{{ $lang->name }} — {{ $hasTrans ? 'Đã dịch' : 'Chưa có bản dịch' }}">
                            {!! $renderFlag($lang, 'translationFlag--sm') !!}
                            <span class="translationBanner_switcherCode">{{ strtoupper($lang->code) }}</span>
                            @if($hasTrans)
                                <i class="fa-solid fa-check translationBanner_switcherCheck" aria-hidden="true"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
