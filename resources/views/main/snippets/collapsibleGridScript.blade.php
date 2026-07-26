{{--
    Generic "Collapsible Grid" — script chia sẻ giữa các grid:
      • Vé máy bay (airGrid)
      • Điểm đến / Đặc sản (tourExploreGrid)
      • Bất kỳ grid nào muốn hành vi "1 hàng đầu + Xem thêm/Thu gọn"

    Cách dùng từ partial:
        @if($isCollapsible)
            @include('main.snippets.collapsibleGridScript')
        @endif

    Markup contract (mỗi grid emit):
        <div data-collapsible-grid data-cg-toggle="<toggleId>" class="... is-pending">
            <article>...</article> (n items)
        </div>
        <div id="<toggleId>" class="viewMore viewMorePill" style="display:none;">
            <button type="button" data-cg-btn
                aria-expanded="false" aria-controls="<gridId>"
                data-cg-collapsed-label="Xem thêm"
                data-cg-expanded-label="Thu gọn"
                class="viewMorePill_btn">
                <span class="viewMorePill_btn_label">
                    <span data-cg-label>Xem thêm</span>
                    <span data-cg-count-wrap> <span data-cg-count>0</span> mục</span>
                </span>
                <span class="viewMorePill_btn_icon" aria-hidden="true">
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </button>
        </div>

    Behaviour:
      • Render đầy đủ items vào DOM (SEO / no-JS fallback vẫn thấy hết).
      • JS đo `offsetTop` của items để detect ranh giới row 1 → ẩn rows phía sau
        bằng class `.is-hidden` (CSS `display:none !important`).
      • Nếu tất cả items vừa 1 hàng → toggle button tự ẩn.
      • Click → toggle Xem thêm / Thu gọn; gọi lại lazyLoadImages() khi expand.
      • Resize: debounce 120ms, tự re-detect row 1 khi đang collapsed.

    Dedupe: script wrap trong IIFE + `window.__collapsibleGridInit` flag.
    Có thể include từ NHIỀU partial trong cùng 1 trang — chỉ chạy đúng 1 lần.
--}}
@push('scripts-custom')
<script type="text/javascript">
    (function(){
        'use strict';
        if (window.__collapsibleGridInit) return;
        window.__collapsibleGridInit = true;

        function setupGrid(grid){
            if (!grid || grid.dataset.cgReady === '1') return;
            grid.dataset.cgReady = '1';

            var items = Array.prototype.slice.call(grid.querySelectorAll(':scope > *'));
            if (items.length === 0) {
                grid.classList.remove('is-pending');
                return;
            }

            var toggleId = grid.getAttribute('data-cg-toggle');
            var toggleEl = toggleId ? document.getElementById(toggleId) : null;
            var btnEl    = toggleEl ? toggleEl.querySelector('[data-cg-btn]') : null;
            var labelEl  = btnEl ? btnEl.querySelector('[data-cg-label]') : null;
            var countEl  = btnEl ? btnEl.querySelector('[data-cg-count]') : null;
            var countWrap= btnEl ? btnEl.querySelector('[data-cg-count-wrap]') : null;

            var collapsedLabel = (btnEl && btnEl.getAttribute('data-cg-collapsed-label')) || 'Xem thêm';
            var expandedLabel  = (btnEl && btnEl.getAttribute('data-cg-expanded-label'))  || 'Thu gọn';

            var isExpanded = false;

            function relayoutLazyload(){
                /* Đánh thức lazyload (định nghĩa ở snippets/scripts-default.blade.php)
                 * để các <img data-src> trong rows mới reveal được load đúng lúc. */
                if (typeof window.lazyLoadImages === 'function') window.lazyLoadImages();
                if (typeof window.lazyLoadImagesGoogleCloud === 'function') window.lazyLoadImagesGoogleCloud();
            }

            function showAll(){
                for (var i = 0; i < items.length; i++) items[i].classList.remove('is-hidden');
            }

            function applyCollapsed(){
                /* Reset trước khi đo: items đang display:none có offsetTop=0 nên
                 * phải show all trước để measure chính xác. */
                showAll();

                var firstTop = items[0].offsetTop;
                var firstRowEnd = items.length;
                for (var i = 1; i < items.length; i++) {
                    if (Math.abs(items[i].offsetTop - firstTop) > 4) {
                        firstRowEnd = i;
                        break;
                    }
                }

                if (firstRowEnd >= items.length) {
                    /* Tất cả vừa trong 1 hàng → không cần toggle */
                    if (toggleEl) toggleEl.style.display = 'none';
                    return;
                }

                for (var j = firstRowEnd; j < items.length; j++) items[j].classList.add('is-hidden');

                if (toggleEl) {
                    toggleEl.style.display = '';
                    if (countEl)   countEl.textContent = (items.length - firstRowEnd);
                    if (labelEl)   labelEl.textContent = collapsedLabel;
                    if (countWrap) countWrap.style.display = '';
                    if (btnEl)     btnEl.setAttribute('aria-expanded', 'false');
                }
            }

            function applyExpanded(){
                showAll();
                if (toggleEl) {
                    if (labelEl)   labelEl.textContent = expandedLabel;
                    if (countWrap) countWrap.style.display = 'none';
                    if (btnEl)     btnEl.setAttribute('aria-expanded', 'true');
                }
                relayoutLazyload();
            }

            if (btnEl) {
                btnEl.addEventListener('click', function(e){
                    e.preventDefault();
                    isExpanded = !isExpanded;
                    if (isExpanded) applyExpanded();
                    else applyCollapsed();
                });
            }

            applyCollapsed();
            grid.classList.remove('is-pending');

            var resizeTimer = null;
            window.addEventListener('resize', function(){
                if (isExpanded) return; /* Đang xem đầy đủ — không can thiệp */
                if (resizeTimer) clearTimeout(resizeTimer);
                resizeTimer = setTimeout(applyCollapsed, 120);
            });
        }

        function initAll(){
            var grids = document.querySelectorAll('[data-collapsible-grid]');
            for (var i = 0; i < grids.length; i++) setupGrid(grids[i]);
        }

        window.refreshCollapsibleGrids = function(root) {
            var scope = root || document;
            var grids = scope.querySelectorAll('[data-collapsible-grid]');
            for (var i = 0; i < grids.length; i++) {
                if (grids[i].dataset.cgReady === '1') continue;
                setupGrid(grids[i]);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    })();
</script>
@endpush
