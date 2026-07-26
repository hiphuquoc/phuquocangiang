@php
    $arrayData  = [
        0 => [
            'img'       => '/storage/images/upload/huong-dan-dat-ve-vui-choi-giai-tri-1-type-manager-upload.webp',
            'title'     => t('service_guidebook_step1_title'),
            'content'   => t('service_guidebook_step1_content'),
        ],
        1 => [
            'img'       => '/storage/images/upload/huong-dan-dat-ve-vui-choi-giai-tri-2-type-manager-upload.webp',
            'title'     => t('service_guidebook_step2_title'),
            'content'   => t('service_guidebook_step2_content'),
        ],
        2 => [
            'img'       => '/storage/images/upload/huong-dan-dat-tour-3-type-manager-upload.webp',
            'title'     => t('service_guidebook_step3_title'),
            'content'   => t('service_guidebook_step3_content', ['brand' => config('main.name')]),
        ],
        3 => [
            'img'       => '/storage/images/upload/huong-dan-dat-ve-vui-choi-giai-tri-4-type-manager-upload.webp',
            'title'     => t('service_guidebook_step4_title'),
            'content'   => t('service_guidebook_step4_content'),
        ]
    ]
@endphp    

<div class="sectionBox backgroundSecondary">
    <!-- Desktop --> 
    <div class="hide-767">
        <div class="container">
            <div style="text-align:center;">
                <h2 class="sectionBox_title" style="margin-bottom:1.5rem !important;">{{ $title ?? null }}</h2>
            </div>
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
            <h2 class="sectionBox_title">{{ $title ?? null }}</h2>
            @include('main.snippets.guideBookMobilePanel')
        </div>
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">
        $(window).ready(function(){
            prevNextGallery();
        })

        function prevNextGallery(action = null){
            const valueNow      = $('#js_prevNextGallery_input').val();
            /* thực hiện */
            let valueNew    = 0;
            if(action=='previous'&&valueNow>0) {
                valueNew    = parseInt(valueNow) - 1;
            }else if(action=='next'&&valueNow<parseInt($('#js_setGuideBook_box').children().length)-1) {
                valueNew    = parseInt(valueNow) + 1;
            }
            hideShowButtonGallery(valueNew);
            setGuideBook(valueNew);
        }

        function hideShowButtonGallery(valueCompare){
            /* ẩn button privious nếu là phần tử đầu tiên */
            if(valueCompare==0){
                $('#js_prevNextGallery_prev').css('display', 'none');
            }else {
                $('#js_prevNextGallery_prev').css('display', 'block');
            }
            /* ẩn button next nếu là phần tử cuối cùng */
            if(valueCompare==parseInt($('#js_setGuideBook_box').children().length)-1){
                $('#js_prevNextGallery_next').css('display', 'none');
            }else {
                $('#js_prevNextGallery_next').css('display', 'block');
            }
            $('#js_prevNextGallery_input').val(valueCompare);
        }

        function setGuideBook(valueSet){
            /* active */
            $('#js_setGuideBook_box').children().each(function(){
                $(this).removeClass('active');
            });
            $('#js_setGuideBook_box').children().eq(valueSet).addClass('active');
            /* set transform */
            let valueTransform = parseInt(valueSet*245);
            $('#js_setGuideBook_image').css('transform', 'translate3d(-'+valueTransform+'px, 0px, 0px)');
            /* set value input */
            hideShowButtonGallery(valueSet);
        }

    </script>
    @include('main.snippets.guideBookMobileSheetScript')
@endpush