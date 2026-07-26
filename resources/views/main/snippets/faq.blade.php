@if($list->isNotEmpty())
    @php
        $faqItemRef         = $item ?? null;
        $faqPrefix          = $faqIdPrefix ?? (is_object($faqItemRef) && isset($faqItemRef->id) ? 'i'.$faqItemRef->id : 'faq');
        $faqHeadingId       = 'faq-heading-'.$faqPrefix;
        $faqHiddenTitle     = !empty($hiddenTitle) && $hiddenTitle == true;
        $faqSubject         = $faqSubject ?? ($title ?? (is_object($faqItemRef) ? ($faqItemRef->name ?? '') : ''));
        $faqKicker          = $kicker ?? t('kicker_support');
        $faqHotline         = trim((string) config('company.hotline'));
        $faqHotlineHref     = $faqHotline !== '' ? 'tel:'.preg_replace('/\s+/', '', $faqHotline) : null;
        $faqEmail           = trim((string) config('company.email'));
        $faqEmailHref       = $faqEmail !== '' ? 'mailto:'.$faqEmail : null;
        $faqLead            = $faqLead ?? t('tour_faq_default_lead', ['brand' => config('main.name')]);
    @endphp
    <section
        class="tourFaqBlock{{ $faqHiddenTitle ? ' tourFaqBlock--bare' : '' }}"
        data-faq-root
        @if($faqHiddenTitle)
            aria-label="{{ t('tour_faq_aria') }}"
        @else
            aria-labelledby="{{ $faqHeadingId }}"
        @endif
    >
        <div class="tourFaqBlock_inner">
            @unless($faqHiddenTitle)
                @php
                    $faqTitleHtml = t('tour_faq_aria');
                    if (!empty($faqSubject)) {
                        $faqTitleHtml = t('tour_faq_question_about').' <span class="tourFaqBlock_intro_title_subject">'.e($faqSubject).'</span>';
                    }
                @endphp
                <aside class="tourFaqBlock_intro">
                    @include('main.snippets.listingHeadRow', [
                        'kicker'                => $faqKicker,
                        'allowHtml'             => true,
                        'titleHtml'             => $faqTitleHtml,
                        'tag'                   => 'h2',
                        'id'                    => $faqHeadingId,
                        'wrapperClass'          => 'tourFaqBlock_intro_head',
                    ])
                    <p class="tourFaqBlock_intro_desc">{{ $faqLead }}</p>

                    @if($faqHotlineHref || $faqEmailHref)
                        <div class="tourFaqBlock_intro_actions">
                            @if($faqHotlineHref)
                                <a
                                    href="{{ $faqHotlineHref }}"
                                    class="tourFaqBlock_intro_actions_btn"
                                    title="{{ t('hotline') }} {{ $faqHotline }}"
                                >
                                    <span class="tourFaqBlock_intro_actions_btn_icon" aria-hidden="true">
                                        <i class="fa-solid fa-headset"></i>
                                    </span>
                                    <span class="tourFaqBlock_intro_actions_btn_text">
                                        <span class="tourFaqBlock_intro_actions_btn_label">{{ t('call_advisor') }}</span>
                                        <span class="tourFaqBlock_intro_actions_btn_value">{{ $faqHotline }}</span>
                                    </span>
                                </a>
                            @endif
                            @if($faqEmailHref)
                                <a
                                    href="{{ $faqEmailHref }}"
                                    class="tourFaqBlock_intro_actions_btn tourFaqBlock_intro_actions_btn--ghost"
                                    title="{{ t('send_email') }} · {{ $faqEmail }}"
                                >
                                    <span class="tourFaqBlock_intro_actions_btn_icon" aria-hidden="true">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <span class="tourFaqBlock_intro_actions_btn_text">
                                        <span class="tourFaqBlock_intro_actions_btn_label">{{ t('send_email') }}</span>
                                        <span class="tourFaqBlock_intro_actions_btn_value">{{ $faqEmail }}</span>
                                    </span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <p class="tourFaqBlock_intro_note">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        {{ t('free_consult') }}
                    </p>
                </aside>
            @endunless

            <div class="tourFaqBlock_list" role="list">
                @foreach($list as $faq)
                    @php
                        $fid        = 'faq-'.$faqPrefix.'-'.$loop->index;
                        $isFirst    = $loop->first;
                    @endphp
                    <div class="tourFaqBlock_item" role="listitem">
                        <h3 class="tourFaqBlock_heading">
                            <button
                                type="button"
                                class="tourFaqBlock_trigger"
                                id="{{ $fid }}-btn"
                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                aria-controls="{{ $fid }}-panel"
                            >
                                <span class="tourFaqBlock_trigger_index" aria-hidden="true">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="tourFaqBlock_trigger_text">{{ $faq->question }}</span>
                                <span class="tourFaqBlock_trigger_icon" aria-hidden="true">
                                    <i class="fa-solid fa-plus"></i>
                                </span>
                            </button>
                        </h3>
                        <div
                            class="tourFaqBlock_panel"
                            id="{{ $fid }}-panel"
                            role="region"
                            aria-labelledby="{{ $fid }}-btn"
                            @unless($isFirst) hidden @endunless
                        >
                            <div class="tourFaqBlock_panel_inner">
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@push('scripts-custom')
    <script type="text/javascript">
        (function () {
            $(document)
                .off('click.tourFaqBlock', '.tourFaqBlock_trigger')
                .on('click.tourFaqBlock', '.tourFaqBlock_trigger', function (e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var panelId = $btn.attr('aria-controls');
                    var panel = document.getElementById(panelId);
                    if (!panel) return;
                    var open = $btn.attr('aria-expanded') === 'true';
                    $btn.attr('aria-expanded', open ? 'false' : 'true');
                    panel.hidden = open;
                });
        })();
    </script>
@endpush
