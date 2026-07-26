{{--
    Tour Explore Grid — Magazine card grid (2026).
    Dùng cho "Điểm đến" và "Đặc sản" trong trang danh mục tour.

    Cấu trúc card:
        .tourExploreGrid_link
          .tourExploreGrid_media        → ảnh (4/3) + badge top-left (highlight tag)
          .tourExploreGrid_body         → icon chip TRÁI (SVG round) + title bold clamp-2

    Design language đồng bộ family với airGrid / productGridUnified:
      • Surface: radius 18px, border slate nhẹ, single-layer shadow, hover lift -4px.
      • Ảnh: zoom 1.06× cubic-bezier 550ms khi hover (Ken-Burns).
      • Icon: SVG inline 24×24 (round line-cap), chip tròn 2.4rem brand-blue tint;
        hover → chip fill solid brand-blue + scale 1.06.
      • Title hover → đổi sang $colorLv1; clamp 2 dòng để row card đều chiều cao.

    Props:
        $list           – Collection các blog/category items (bắt buộc).
        $kind           – 'destination' (default) | 'specialty'. Quyết định icon SVG
                          hiển thị trong chip TRÁI tiêu đề. Section context (gold accent)
                          được kiểm soát riêng qua wrapper `.tourExploreSection--specialties`.
        $limit          – int. Số item tối đa render vào DOM. Default null = không giới hạn.
                          Khi $collapsible=true, $limit thường bỏ qua để JS xử lý "1 hàng đầu".
        $link           – string. Slug để CTA "Xem thêm" dẫn tới. CHỈ dùng khi $collapsible=false
                          (legacy behavior). Khi $collapsible=true, link bị bỏ qua.
        $collapsible    – false (default) | true. Nếu true: rút gọn còn 1 hàng đầu + nút
                          "Xem thêm" reveal-in-place (xem `snippets/collapsibleGridScript`).
        $countNoun      – 'điểm đến' (default). Đơn vị trong nhãn "Xem thêm N <countNoun>".

    Behaviour:
      • $collapsible=true → render ALL items vào DOM (mất giới hạn $limit), gắn
        `data-collapsible-grid`, JS giấu rows 2+ và toggle reveal.
      • $collapsible=false → giữ nguyên hành vi cũ: cắt theo $limit + link CTA.
--}}
@if(!empty($list) && $list->isNotEmpty())
    @php
        $isCollapsible  = !empty($collapsible) && $collapsible === true;
        $maxItems       = isset($limit) ? (int) $limit : null;
        $tagConfig      = config('blog.highlight_tags', []);
        $gridId         = 'tourExploreGrid_'.uniqid();
        $toggleId       = 'tourExploreToggle_'.uniqid();
        $countNounText  = $countNoun ?? t('count_destinations');
        /* `kind` quyết định icon SVG hiển thị trong chip TRÁI title.
         * - 'destination' (default): map-pin (teardrop + dot filled bên trong)
         * - 'specialty'           : chef-hat (dome puff + band + seam line) */
        $kindResolved   = isset($kind) && $kind === 'specialty' ? 'specialty' : 'destination';
        $iconAriaLabel  = $kindResolved === 'specialty' ? t('specialty') : t('destination');
    @endphp
    <div
        id="{{ $gridId }}"
        class="tourExploreGrid {{ $isCollapsible ? 'tourExploreGrid--collapsible is-pending' : '' }}"
        role="list"
        @if($isCollapsible) data-collapsible-grid data-cg-toggle="{{ $toggleId }}" @endif
    >
        @foreach($list as $blog)
            @if(!$isCollapsible && $maxItems !== null && $loop->index >= $maxItems)
                @break
            @endif
            @php
                $blogTitle      = $blog->name ?? $blog->seo->title ?? $blog->seo->seo_title ?? null;
                $blogSlug       = $blog->seo->slug_full ?? null;
                $imgSrc         = $blog->seo->image_small ?? $blog->seo->image ?? config('admin.images.default_750x460');
                $tagKey         = $blog->highlight_tag ?? null;
                $tagMeta        = ($tagKey && !empty($tagConfig[$tagKey])) ? $tagConfig[$tagKey] : null;
                $tagLabel       = $tagMeta['label'] ?? null;
                $tagVariant     = $tagMeta['variant'] ?? $tagKey;
            @endphp
            <article class="tourExploreGrid_item" role="listitem">
                <a href="/{{ $blogSlug }}" class="tourExploreGrid_link" title="{{ $blogTitle }}" aria-label="{{ t('explore_item', ['name' => $blogTitle]) }}">
                    <div class="tourExploreGrid_media">
                        <img
                            src="{{ config('main.svg.loading_main') }}"
                            data-src="{{ $imgSrc }}"
                            alt="{{ $blogTitle }}"
                            loading="lazy"
                            decoding="async"
                            width="400"
                            height="300"
                        />
                        @if(!empty($tagLabel))
                            <span class="tourExploreGrid_badge tourExploreGrid_badge--{{ $tagVariant }}">{{ $tagLabel }}</span>
                        @endif
                    </div>
                    <div class="tourExploreGrid_body">
                        {{-- Icon chip TRÁI — SVG inline 24×24, round line-cap, fill currentColor
                             để kế thừa màu từ chip wrapper. Bằng SVG (không Font Awesome) cho
                             nét đồng nhất, scale crisp ở mọi DPR, dễ tùy biến stroke-width. --}}
                        <span class="tourExploreGrid_icon tourExploreGrid_icon--{{ $kindResolved }}" role="img" aria-label="{{ $iconAriaLabel }}">
                            @if($kindResolved === 'specialty')
                                {{-- Chef hat: dome puff (3 cuộn mây) + band (mũ thân) + seam line.
                                     Biểu tượng "ẩm thực địa phương / đặc sản" phổ quát. --}}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                    <path d="M6 13.5c-1.93 0-3.5-1.57-3.5-3.5 0-1.65 1.14-3.04 2.68-3.4A4 4 0 0 1 9 4a4 4 0 0 1 6 0 4 4 0 0 1 3.82 2.6c1.54.36 2.68 1.75 2.68 3.4 0 1.93-1.57 3.5-3.5 3.5"/>
                                    <path d="M6 13.5v5.75c0 .69.56 1.25 1.25 1.25h9.5c.69 0 1.25-.56 1.25-1.25V13.5"/>
                                    <line x1="7" y1="16.75" x2="17" y2="16.75"/>
                                </svg>
                            @else
                                {{-- Map pin: teardrop ngoài (stroke) + dot filled center (chấm
                                     nhấn). Biểu tượng "điểm đến / vị trí địa lý" trực quan. --}}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                                    <path d="M12 21.5c-.6-.4-7-7.3-7-12.5a7 7 0 1 1 14 0c0 5.2-6.4 12.1-7 12.5Z"/>
                                    <circle cx="12" cy="9.5" r="2.4" fill="currentColor" stroke="none"/>
                                </svg>
                            @endif
                        </span>
                        <h3 class="tourExploreGrid_title maxLine_2">{{ $blogTitle }}</h3>
                    </div>
                </a>
            </article>
        @endforeach
    </div>

    @if($isCollapsible)
        <div id="{{ $toggleId }}" class="viewMore viewMorePill tourExploreGrid_toggle" style="display:none;">
            <button
                type="button"
                class="viewMorePill_btn tourExploreGrid_toggle_btn"
                data-cg-btn
                data-cg-collapsed-label="{{ t('view_more') }}"
                data-cg-expanded-label="{{ t('collapse') }}"
                aria-expanded="false"
                aria-controls="{{ $gridId }}"
            >
                <span class="viewMorePill_btn_label">
                    <span data-cg-label>{{ t('view_more') }}</span><span data-cg-count-wrap> <span data-cg-count>0</span> {{ $countNounText }}</span>
                </span>
                <span class="viewMorePill_btn_icon tourExploreGrid_toggle_icon" aria-hidden="true">
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </button>
        </div>
        @include('main.snippets.collapsibleGridScript')
    @elseif(!empty($link))
        <div class="viewMore viewMorePill">
            <a href="{{ seo_url($link) }}" title="{{ t('view_more') }}" class="viewMorePill_btn">
                <span class="viewMorePill_btn_label">{{ t('view_more') }}</span>
                <span class="viewMorePill_btn_icon" aria-hidden="true">
                    <i class="fa-solid fa-arrow-right-long"></i>
                </span>
            </a>
        </div>
    @endif
@endif
