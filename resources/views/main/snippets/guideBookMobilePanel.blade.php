@php
    $gbsTitleId = 'guideBookMobileSheetTitle_' . substr(str_replace('.', '', uniqid('', true)), -10);
    $gbsProgressId = 'guideBookMobileProgress_' . substr(str_replace('.', '', uniqid('', true)), -10);
    $gbsUid = substr(preg_replace('/\W/', '', uniqid('gbs', true)), -14);
    $gbsStepTotal = count($arrayData);
@endphp
<div class="guideBookBoxMobile">
    <div class="guideBookBoxMobile_collage" aria-hidden="true">
        @foreach($arrayData as $image)
            <img src="{{ $image['img'] }}" alt="" loading="lazy" decoding="async" />
            @php
                if ($loop->index == 2) {
                    break;
                }
            @endphp
        @endforeach
    </div>
    <div
        class="guideBookBoxMobile_backdrop"
        style="display:none"
        onclick="showHideElement('js_showHideElement_box');"
        aria-hidden="true"
    ></div>
    <div
        id="js_showHideElement_box"
        class="guideBookBoxMobile_sheet"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $gbsTitleId }}"
        data-gbs-uid="{{ $gbsUid }}"
        data-current-step="0"
        style="display:none"
    >
        <header class="guideBookBoxMobile_sheet_head">
            <button
                type="button"
                class="guideBookBoxMobile_sheet_close"
                onclick="showHideElement('js_showHideElement_box');"
                aria-label="{{ t('close') }}"
            >
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
            <div class="guideBookBoxMobile_sheet_head_main">
                <span class="guideBookBoxMobile_sheet_kicker">{{ t('kicker_book_tour') }}</span>
                <h2 id="{{ $gbsTitleId }}" class="guideBookBoxMobile_sheet_title maxLine_2">
                    {{ $title ?? '' }}
                </h2>
            </div>
        </header>

        <div class="guideBookBoxMobile_sheet_scroll customScrollBar-y" id="guideBookMobileScroll_{{ $gbsUid }}">
            <nav
                id="{{ $gbsProgressId }}"
                class="guideBookBoxMobile_stickyHero"
                aria-label="{{ t('kicker_book_tour') }}"
            >
                <div class="guideBookBoxMobile_stickyHero_track" role="tablist">
                    @foreach($arrayData as $item)
                        @php
                            $secId = 'gbs_sec_' . $gbsUid . '_' . $loop->index;
                        @endphp
                        <button
                            type="button"
                            class="guideBookBoxMobile_stepPill {{ $loop->first ? 'is-active' : '' }}"
                            role="tab"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            tabindex="{{ $loop->first ? '0' : '-1' }}"
                            data-guide-step="{{ $loop->index }}"
                            data-guide-target="{{ $secId }}"
                            aria-controls="{{ $secId }}"
                            id="guideBookMobile-tab-{{ $loop->index }}-{{ $gbsProgressId }}"
                        >
                            <span class="guideBookBoxMobile_stepPill_no" aria-hidden="true">{{ $loop->iteration }}</span>
                            <span class="guideBookBoxMobile_stepPill_label maxLine_2">{{ $item['title'] }}</span>
                        </button>
                    @endforeach
                </div>
            </nav>

            <div class="guideBookBoxMobile_flow">
                @foreach($arrayData as $item)
                    @php
                        $secId = 'gbs_sec_' . $gbsUid . '_' . $loop->index;
                    @endphp
                    <section
                        id="{{ $secId }}"
                        class="guideBookBoxMobile_section"
                        data-guide-step="{{ $loop->index }}"
                        role="tabpanel"
                        aria-labelledby="guideBookMobile-tab-{{ $loop->index }}-{{ $gbsProgressId }}"
                    >
                        <header class="guideBookBoxMobile_section_head">
                            <span
                                class="guideBookBoxMobile_section_kicker"
                                aria-label="{{ $loop->iteration }}/{{ $gbsStepTotal }}"
                            >
                                <span class="guideBookBoxMobile_section_kicker_cur">{{ $loop->iteration }}</span>
                                <span class="guideBookBoxMobile_section_kicker_sep" aria-hidden="true">/</span>
                                <span class="guideBookBoxMobile_section_kicker_total">{{ $gbsStepTotal }}</span>
                            </span>
                            <h3 class="guideBookBoxMobile_section_heading maxLine_3">{{ $item['title'] }}</h3>
                        </header>
                        <div class="guideBookBoxMobile_section_prose">
                            {!! $item['content'] !!}
                        </div>
                        <figure class="guideBookBoxMobile_section_media">
                            <img
                                class="guideBookBoxMobile_section_img"
                                src="{{ $item['img'] }}"
                                alt="{{ $item['title'] }}"
                                loading="lazy"
                                decoding="async"
                            />
                        </figure>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</div>
@include('main.snippets.viewMoreDetailPill', [
    'onClick' => "showHideElement('js_showHideElement_box');",
    'panelId' => 'js_showHideElement_box',
])
