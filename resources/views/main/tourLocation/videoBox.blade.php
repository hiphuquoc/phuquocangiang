@if(!empty($item->seo->video))
    @php
        $videoHeadingId = 'tour-media-video-' . ($item->id ?? 'x');
    @endphp
    <section class="tourMediaBlock tourMediaBlock--video" aria-labelledby="{{ $videoHeadingId }}">
        <div class="container">
            <header class="tourMediaBlock_head">
                @include('main.snippets.listingHeadRow', [
                    'kicker' => t('video'),
                    'tag' => 'h2',
                    'id' => $videoHeadingId,
                    'allowHtml' => true,
                    'titleHtml' => $title ?? '',
                    'withSectionTitleClass' => false,
                ])
            </header>
            <div class="tourMediaBlock_player videoYoutubeBox">
                <div class="videoYoutubeBox_video">
                    {!! $item->seo->video !!}
                </div>
            </div>
        </div>
    </section>
@endif
