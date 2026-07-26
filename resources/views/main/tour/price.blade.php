<div class="callBookTour">
    <div class="callBookTour_price">
        <div>{{ t('price_from') }}: <span>{!! !empty($item->price_show) ? format_price($item->price_show) : t('contact_price') !!}</span></div>
    </div>
    <div class="callBookTour_content">
        <b>{{ t('tour_program_highlights') }}:</b>
        <div class="contentWithViewMore">
            <div id="js_viewMoreContent_content" class="contentWithViewMore_content" style="height:80px;">
                {!! $item->content->special_list ?? null !!}
            </div>
            <div class="contentWithViewMore_btn" onClick="viewMoreContent(this, 'js_viewMoreContent_content', 80);">
                {{ t('read_more') }}<i class="fa-solid fa-arrow-right-long"></i>
            </div>
        </div>
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">
        function viewMoreContent(btn, idContent, maxHeight){
            const heightNow     = $('#'+idContent).outerHeight();
            const heightFull    = $('#'+idContent+' ul').outerHeight();
            if(Math.floor(heightNow)<Math.floor(heightFull)){
                $('#'+idContent).css('height', heightFull);
                $(btn).html('{{ t('collapse') }}<i class="fa-solid fa-arrow-left-long"></i>');
                /* không cho scrol sidebar */
                $('#js_scrollFixed_flag').val('false');
            }else {
                $('#'+idContent).css('height', maxHeight);
                $(btn).html('{{ t('read_more') }}<i class="fa-solid fa-arrow-right-long"></i>');
                $('#js_scrollFixed_flag').val('true');
            }
        }
    </script>
@endpush