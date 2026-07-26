@if(!empty($content))
    @php
        $locName = $item->display_name ?? $item->name ?? t('destination');
        $headingId = 'tour-guide-intro-' . ($item->id ?? 'loc');
    @endphp
    <section class="tourGuideIntro" aria-labelledby="{{ $headingId }}">
        <div class="container">
            <header class="tourGuideIntro_header">
                @include('main.snippets.listingHeadRow', [
                    'kicker' => t('tour_guide_intro_kicker'),
                    'title' => t('tour_guide_intro_title', ['name' => $locName]),
                    'tag' => 'h2',
                    'id' => $headingId,
                    'withSectionTitleClass' => false,
                ])
                <p class="tourGuideIntro_lede">{{ t('tour_guide_intro_lede') }}</p>
            </header>
            <div class="tourGuideIntro_content">
                <div id="js_showHideFullContent_content" class="tourGuideIntro_prose maxLine_4">
                    {!! $content !!}
                </div>
                <div class="viewMore viewMorePill tourGuideIntro_viewMore">
                    <button
                        type="button"
                        class="viewMorePill_btn"
                        onclick="showHideFullContent(this, 'maxLine_4');"
                    >
                        <span class="viewMorePill_btn_label">{{ t('read_more') }}</span>
                        <span class="viewMorePill_btn_icon" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-long"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </section>
@endif
