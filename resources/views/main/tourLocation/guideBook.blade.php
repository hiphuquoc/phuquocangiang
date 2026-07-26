@php
    $arrayData  = [
        0 => [
            'img'       => '/storage/images/upload/huong-dan-dat-tour-1-type-manager-upload.webp',
            'title'     => t('guidebook_step1_title'),
            'content'   => t('guidebook_step1_content')
        ],
        1 => [
            'img'       => '/storage/images/upload/huong-dan-dat-tour-2-type-manager-upload.webp',
            'title'     => t('guidebook_step2_title'),
            'content'   => t('guidebook_step2_content')
        ],
        2 => [
            'img'       => '/storage/images/upload/huong-dan-dat-tour-3-type-manager-upload.webp',
            'title'     => t('guidebook_step3_title'),
            'content'   => t('guidebook_step3_content', ['brand' => config('main.name')])
        ],
        3 => [
            'img'       => '/storage/images/upload/huong-dan-dat-tour-4-type-manager-upload.webp',
            'title'     => t('guidebook_step4_title'),
            'content'   => t('guidebook_step4_content', ['brand' => config('main.name')])
        ]
    ]
@endphp    

<div class="sectionBox">
    <!-- Desktop -->
    <div class="hide-767">
        <div class="container">
            @include('main.snippets.listingHeadRow', [
                'kicker' => $kicker ?? t('kicker_book_tour'),
                'title' => $title ?? '',
                'centered' => true,
                'wrapperClass' => 'listingHeadRow--guideBookLead',
                'withSectionTitleClass' => false,
            ])
            <div class="guideBookBox">
                <div class="guideBookBox_image">
                    <div class="galleryCustomBox">
                        <div id="js_setGuideBook_image" class="galleryCustomBox_box">
                            @foreach($arrayData as $item)
                                <img src="{{ $item['img'] }}" alt="{{ $title ?? null }}" title="{{ $title ?? null }}" />
                            @endforeach
                            <input type="hidden" id="js_prevNextGallery_input" value="0" />
                        </div>
                        <div class="galleryCustomBox_arrow">
                            <div class="privious" id="js_prevNextGallery_prev" onClick="prevNextGallery('previous');"></div>
                            <div class="next" id="js_prevNextGallery_next" onClick="prevNextGallery('next');"></div>
                        </div>
                    </div>
                </div>
                <div class="guideBookBox_content">
                    <div id="js_setGuideBook_box" class="guideBookStepByStep">
                        @foreach($arrayData as $item)
                            @php
                                $active = $loop->index==0 ? 'active' : null;
                            @endphp
                            <div class="guideBookStepByStep_item {{ $active }}" onClick="setGuideBook({{ $loop->index }});">
                                <div class="guideBookStepByStep_item_title">
                                    <span class="guideBookStepByStep_item_title_no">{{ $loop->index + 1 }}</span>{{ $item['title'] }}
                                </div>
                                <div class="guideBookStepByStep_item_text">
                                    {!! $item['content'] !!} 
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>  
        </div>
    </div>
    <!-- Mobile -->
    <div class="show-767">
        <div class="container">
            @include('main.snippets.listingHeadRow', [
                'kicker' => $kicker ?? t('kicker_book_tour'),
                'title' => $title ?? '',
                'withSectionTitleClass' => false,
            ])
            @include('main.snippets.guideBookMobilePanel')
        </div>
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">
        function guideBookSlideWidthPx() {
            var $first = $('#js_setGuideBook_image').children().first();
            var w = $first.length ? $first.outerWidth() : 0;
            return w > 0 ? w : 245;
        }

        function setGuideBookGalleryTransform(index) {
            var w = guideBookSlideWidthPx();
            var x = index * w;
            $('#js_setGuideBook_image').css(
                'transform',
                'translate3d(-' + x + 'px, 0px, 0px)'
            );
        }

        var guideBookResizeTimer = null;

        $(window).ready(function () {
            prevNextGallery();
        });

        $(window).on('resize orientationchange', function () {
            clearTimeout(guideBookResizeTimer);
            guideBookResizeTimer = setTimeout(function () {
                var cur = parseInt($('#js_prevNextGallery_input').val(), 10);
                if (isNaN(cur)) cur = 0;
                setGuideBookGalleryTransform(cur);
            }, 120);
        });

        function prevNextGallery(action = null) {
            const valueNow = $('#js_prevNextGallery_input').val();
            let valueNew = 0;
            if (action == 'previous' && valueNow > 0) {
                valueNew = parseInt(valueNow, 10) - 1;
            } else if (
                action == 'next' &&
                valueNow < parseInt($('#js_setGuideBook_box').children().length, 10) - 1
            ) {
                valueNew = parseInt(valueNow, 10) + 1;
            }
            hideShowButtonGallery(valueNew);
            setGuideBook(valueNew);
        }

        function hideShowButtonGallery(valueCompare) {
            if (valueCompare == 0) {
                $('#js_prevNextGallery_prev').css('display', 'none');
            } else {
                $('#js_prevNextGallery_prev').css('display', 'block');
            }
            if (
                valueCompare ==
                parseInt($('#js_setGuideBook_box').children().length, 10) - 1
            ) {
                $('#js_prevNextGallery_next').css('display', 'none');
            } else {
                $('#js_prevNextGallery_next').css('display', 'block');
            }
            $('#js_prevNextGallery_input').val(valueCompare);
        }

        function setGuideBook(valueSet) {
            $('#js_setGuideBook_box').children().each(function () {
                $(this).removeClass('active');
            });
            $('#js_setGuideBook_box').children().eq(valueSet).addClass('active');
            setGuideBookGalleryTransform(valueSet);
            hideShowButtonGallery(valueSet);
        }

    </script>
    @include('main.snippets.guideBookMobileSheetScript')
@endpush