@if(!empty($list)&&$list->isNotEmpty())
    @php
        $blockId = $catalogId ?? ('serviceTicketCatalog_'.uniqid());
        $hiddenId = $hiddenId ?? ('serviceTicketCatalogHidden_'.uniqid());
    @endphp
    <div id="{{ $blockId }}" class="serviceTicketCatalog tourList tourGrid productGridUnified">
        @foreach($list as $tour)
            @php
                $rating = 0;
                $ratingCount = 0;
                if(!empty($tour->comments)&&$tour->comments->isNotEmpty()){
                    $tmpTotal = 0;
                    foreach($tour->comments as $comment){
                        $tmpTotal += $comment->rating;
                        $ratingCount += 1;
                    }
                    $rating = number_format($tmpTotal/$ratingCount, 1);
                }
                $ratingText = !empty($ratingCount) ? \App\Helpers\Rating::getTextRatingByRule($rating) : null;
                $saleoff = (!empty($tour->price_del)&&!empty($tour->price_show)&&$tour->price_del>$tour->price_show) ? \App\Helpers\Number::calculatorSaleoff($tour->price_show, $tour->price_del) : 0;
                $optionCount = !empty($tour->options) ? $tour->options->count() : 0;
                $ticketFilter = [];
                $ticketFilter[] = 'tat-ca-ve';
                if(!empty($saleoff)) $ticketFilter[] = 've-giam-gia';
                if(!empty($ratingCount)&&$rating>=4.5) $ticketFilter[] = 've-danh-gia-cao';
            @endphp
            <article class="tourList_item serviceTicketCatalog_item" data-filter-ticket="{{ implode(' ', $ticketFilter) }}">
                <a href="/{{ $tour->seo->slug_full ?? null }}" class="tourList_item_gallery serviceTicketCatalog_item_gallery">
                    <div class="tourList_item_gallery_top">
                        <img src="{{ config('main.svg.loading_main_nobg') }}" data-src="{{ $tour->seo->image ?? config('main.images.default_750x460') }}" alt="{{ $tour->name ?? null }}" title="{{ $tour->name ?? null }}" />
                        @if(!empty($tour->serviceLocation->display_name))
                            <div class="tourList_item_gallery_top_time">
                                <i class="fa-solid fa-location-dot"></i>{{ $tour->serviceLocation->display_name }}
                            </div>
                        @endif
                        @if(!empty($saleoff))
                            <div class="serviceTicketCatalog_badge">{{ t('discount_percent', ['percent' => $saleoff]) }}</div>
                        @endif
                    </div>
                </a>

                <div class="tourList_item_info serviceTicketCatalog_item_info">
                    <div class="tourList_item_info_title">
                        <a href="/{{ $tour->seo->slug_full ?? null }}">
                            <h3>{{ $tour->name ?? $tour->seo->title ?? null}}</h3>
                        </a>
                    </div>

                    @if(!empty($ratingCount))
                        <div class="tourList_item_info_rating serviceTicketCatalog_item_rating">
                            <div class="tourList_item_info_rating_number">
                                <img src="/storage/images/svg/icon-comment.svg" alt="{{ t('service_rating') }}" title="{{ t('service_rating') }}">
                                <span>{{ $rating }}</span> ({{ $ratingCount }})
                            </div>
                            <div class="tourList_item_info_rating_text">
                                {{ $ratingText }}
                            </div>
                        </div>
                    @endif

                    <div class="serviceTicketCatalog_item_meta">
                        @if(!empty($optionCount))
                            <span>{{ t('service_packages_count', ['count' => $optionCount]) }}</span>
                        @endif
                        <span>{{ t('service_e_ticket') }}</span>
                        <span>{{ t('service_support_247') }}</span>
                    </div>

                    @if(!empty($tour->seo->description))
                        <p class="serviceTicketCatalog_item_desc">{{ $tour->seo->description }}</p>
                    @endif

                    @if(!empty($tour->departure?->display_name) && !empty($tour->location?->display_name))
                        <div class="serviceTicketCatalog_item_route">
                            <div class="shipGrid_item_content_table_row__dp maxLine_1">{{ $tour->departure->display_name }}</div>
                            <i class="fa-solid fa-arrow-right-long"></i>
                            <div class="shipGrid_item_content_table_row__dp maxLine_1">{{ $tour->location->display_name }}</div>
                        </div>
                    @endif
                </div>

                <div class="tourList_item_action serviceTicketCatalog_item_action">
                    <div class="tourList_item_action_price">
                        @if(!empty($tour->price_del)&&$tour->price_del>$tour->price_show)
                            <div class="tourList_item_action_price_old">
                                <div class="tourList_item_action_price_old_number">
                                    {!! format_price($tour->price_del) !!}
                                </div>
                            </div>
                        @endif
                        <div class="tourList_item_action_price_now">
                            {!! !empty($tour->price_show) ? format_price($tour->price_show) : t('contact_price') !!}
                        </div>
                    </div>
                    <a href="/{{ $tour->seo->slug_full ?? null }}" class="tourList_item_action_button" title="{{ t('view_detail_of', ['name' => $tour->name ?? $tour->seo->title ?? '']) }}">
                        <span>{{ t('view_detail') }}</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </article>
        @endforeach
    </div>
    <div id="{{ $hiddenId }}" style="display:none;"></div>
@endif