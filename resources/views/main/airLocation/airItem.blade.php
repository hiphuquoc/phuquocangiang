{{--
    Vé máy bay — Air card grid (2026).
    Đồng bộ family với productGridUnified (tour/vé vui chơi): cùng surface
    radius/shadow/hover, cùng cấu trúc gallery → info → action; thêm decoration
    riêng cho air: partner chip top-left, route line giữa info.

    Props (tất cả optional, giữ tương thích call site cũ):
        $list           – Collection các Air model (bắt buộc).
        $itemHeading    – 'h2' (default) | 'h3'. Tag cho title trong card.
        $collapsible    – false (default) | true. Nếu true: dùng helper
                          `data-collapsible-grid` để rút gọn còn 1 hàng đầu +
                          nút "Xem thêm" (xem `snippets/collapsibleGridScript`).

    Markup-level lưu ý:
      • Render TẤT CẢ items vào DOM kể cả khi collapsible — JS sau đó dùng
        offsetTop detect row 1 và ẩn các item phía sau. Đảm bảo SEO/no-JS
        fallback vẫn thấy đủ data.
      • Lazyload ảnh: src placeholder + data-src; khi expand, helper script
        gọi lại lazyLoadImages() để các ảnh row 2+ được load đúng lúc.
--}}
@if(!empty($list)&&$list->isNotEmpty())
@php
    $airGridId          = 'airGrid_'.uniqid();
    $airToggleId        = 'airGridToggle_'.uniqid();
    $isCollapsible      = !empty($collapsible) && $collapsible === true;
    $headingTag         = ($itemHeading ?? 'h3') === 'h2' ? 'h2' : 'h3';
@endphp

<div
    id="{{ $airGridId }}"
    class="tourList tourGrid productGridUnified airGrid {{ $isCollapsible ? 'airGrid--collapsible is-pending' : '' }}"
    @if($isCollapsible) data-collapsible-grid data-cg-toggle="{{ $airToggleId }}" @endif
>
    @foreach($list as $tour)
        @php
            /* Đối tác hãng bay — lấy tên duy nhất, hiển thị 1 + "+N" */
            $airPartners        = [];
            if(!empty($tour->partners) && $tour->partners->isNotEmpty()){
                foreach($tour->partners as $rel){
                    $name       = $rel->infoPartner->name ?? null;
                    if(!empty($name) && !in_array($name, $airPartners)) $airPartners[] = $name;
                }
            }
            $partnerPrimary     = $airPartners[0] ?? null;
            $partnerExtra       = max(0, count($airPartners) - 1);

            /* Route — tên hiển thị 2 đầu chặng.
             * `AirDeparture` chỉ có field `name`; `AirLocation` ưu tiên
             * `display_name` (đã đa ngôn ngữ) → fallback `name`. */
            $depCity            = $tour->departure->name ?? null;
            $locCity            = $tour->location->display_name ?? ($tour->location->name ?? null);

            /* Rating */
            $rating             = 0;
            $ratingCount        = 0;
            if(!empty($tour->comments) && $tour->comments->isNotEmpty()){
                $tmpTotal       = 0;
                foreach($tour->comments as $comment){
                    $tmpTotal   += $comment->rating;
                    $ratingCount++;
                }
                if($ratingCount > 0) $rating = number_format($tmpTotal/$ratingCount, 1);
            }
            $ratingText         = !empty($ratingCount) ? \App\Helpers\Rating::getTextRatingByRule($rating) : null;

            $href               = '/'.($tour->seo->slug_full ?? '');
            $title              = $tour->name ?? $tour->seo->title ?? null;
            $imgSrc             = $tour->seo->image ?? config('main.images.default_750x460');
        @endphp
        <article class="tourList_item airGrid_item">
            <a href="{{ $href }}" class="tourList_item_gallery airGrid_item_gallery" title="{{ $title }}" aria-label="{{ t('view_detail_of', ['name' => $title]) }}">
                <div class="tourList_item_gallery_top airGrid_item_gallery_top">
                    <img
                        src="{{ config('main.svg.loading_main_nobg') }}"
                        data-src="{{ $imgSrc }}"
                        alt="{{ $title }}"
                        title="{{ $title }}"
                        loading="lazy"
                        decoding="async"
                    />
                    @if(!empty($partnerPrimary))
                        <div class="airGrid_item_partnerChip" aria-label="{{ t('air_partner_aria', ['partner' => $partnerPrimary, 'extra' => $partnerExtra ? ' '.t('air_and_others', ['count' => $partnerExtra]) : '']) }}">
                            <i class="fa-solid fa-plane" aria-hidden="true"></i>
                            <span>{{ $partnerPrimary }}@if($partnerExtra)<span class="airGrid_item_partnerChip_more"> · +{{ $partnerExtra }}</span>@endif</span>
                        </div>
                    @endif
                </div>
            </a>

            <div class="tourList_item_info airGrid_item_info">
                <div class="tourList_item_info_title">
                    <a href="{{ $href }}" title="{{ $title }}">
                        <{{ $headingTag }}>{{ $title }}</{{ $headingTag }}>
                    </a>
                </div>

                @if(!empty($ratingCount))
                    <div class="tourList_item_info_rating airGrid_item_rating">
                        <div class="tourList_item_info_rating_number">
                            <img src="/storage/images/svg/icon-comment.svg" alt="{{ t('air_rating_for', ['name' => $title]) }}" title="{{ t('air_rating') }}">
                            <span>{{ $rating }}</span> ({{ $ratingCount }})
                        </div>
                        @if(!empty($ratingText))
                            <div class="tourList_item_info_rating_text">
                                {{ $ratingText }}
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($depCity) || !empty($locCity))
                    <div class="cardRouteLine airGrid_item_route" aria-label="{{ t('ship_journey_aria', ['from' => $depCity, 'to' => $locCity]) }}">
                        <span class="cardRouteLine_city maxLine_1" title="{{ $depCity }}">{{ $depCity ?? '—' }}</span>
                        <span class="cardRouteLine_path" aria-hidden="true">
                            <i class="fa-solid fa-plane"></i>
                        </span>
                        <span class="cardRouteLine_city cardRouteLine_city--end maxLine_1" title="{{ $locCity }}">{{ $locCity ?? '—' }}</span>
                    </div>
                @endif
            </div>

            <div class="tourList_item_action airGrid_item_action">
                <span class="airGrid_item_actionMeta">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>{{ t('air_meta_eticket') }}</span>
                </span>
                <a href="{{ $href }}" class="tourList_item_action_button airGrid_item_btn" title="{{ t('view_detail_of', ['name' => $title]) }}">
                    <span>{{ t('view_detail') }}</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </article>
    @endforeach
</div>

@if($isCollapsible)
    <div id="{{ $airToggleId }}" class="viewMore viewMorePill airGrid_toggle" style="display:none;">
        <button
            type="button"
            class="viewMorePill_btn airGrid_toggle_btn"
            data-cg-btn
            data-cg-collapsed-label="{{ t('view_more') }}"
            data-cg-expanded-label="{{ t('collapse') }}"
            aria-expanded="false"
            aria-controls="{{ $airGridId }}"
        >
            <span class="viewMorePill_btn_label">
                <span data-cg-label>{{ t('view_more') }}</span><span data-cg-count-wrap> <span data-cg-count>0</span> {{ t('air_flights_count_unit') }}</span>
            </span>
            <span class="viewMorePill_btn_icon airGrid_toggle_icon" aria-hidden="true">
                <i class="fa-solid fa-chevron-down"></i>
            </span>
        </button>
    </div>
    @include('main.snippets.collapsibleGridScript')
@endif
@endif
