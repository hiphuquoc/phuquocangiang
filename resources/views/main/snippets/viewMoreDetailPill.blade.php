{{--
    Nút pill "Xem chi tiết" — cùng hệ `.viewMorePill` với toggle grid vé máy bay (airGrid_toggle).
    @param string $onClick — biểu thức gọi trong onclick (vd: showHideElement('js_showHideElement_box');)
    @param string|null $panelId — id panel dialog cho aria-controls
    @param string|null $label — mặc định t('view_detail')
    @param string|null $wrapperClass — class thêm trên wrapper
--}}
@php
    $detailLabel = $label ?? t('view_detail');
    $detailOnClick = $onClick ?? '';
    $detailPanelId = $panelId ?? 'js_showHideElement_box';
    $detailWrapperClass = trim('viewMore viewMorePill viewMoreDetailPill ' . ($wrapperClass ?? ''));
@endphp
<div class="{{ $detailWrapperClass }}">
    <button
        type="button"
        class="viewMorePill_btn viewMoreDetailPill_btn"
        onclick="{{ $detailOnClick }}"
        aria-haspopup="dialog"
        aria-controls="{{ $detailPanelId }}"
    >
        <span class="viewMorePill_btn_label">{{ $detailLabel }}</span>
        <span class="viewMorePill_btn_icon viewMoreDetailPill_icon" aria-hidden="true">
            <i class="fa-solid fa-chevron-down"></i>
        </span>
    </button>
</div>
