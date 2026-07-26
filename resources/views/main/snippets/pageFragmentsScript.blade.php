@push('scripts-custom')
<script type="text/javascript">
(function () {
    'use strict';

    function isFullPageHtml(html) {
        if (!html || html.length < 200) return false;
        var sample = html.slice(0, 8000).toLowerCase();
        if (sample.indexOf('<!doctype') !== -1 || sample.indexOf('<html') !== -1) return true;
        /* Chỉ chặn full layout khi có header site + không phải partial tour grid */
        return sample.indexOf('class="pagelisting"') !== -1
            && sample.indexOf('tourlist_item') === -1
            && sample.indexOf('sd-card--deal') === -1
            && sample.indexOf('serviceticketcatalog') === -1
            && sample.indexOf('data-page-fragment') === -1;
    }

    function hydrateLazyImagesIn(root) {
        if (!root || typeof jQuery === 'undefined') return;
        var $root = jQuery(root);
        $root.find('img[data-src]').not('.loaded').each(function () {
            var $img = jQuery(this);
            var dataSrc = $img.attr('data-src');
            if (!dataSrc) return;
            if (typeof loadImageFromHosting === 'function') {
                loadImageFromHosting($img);
            } else {
                $img.attr('src', dataSrc);
            }
            $img.addClass('loaded');
        });
        if (typeof window.lazyLoadImages === 'function') {
            window.lazyLoadImages($root);
        }
        if (typeof window.lazyLoadImagesGoogleCloud === 'function') {
            window.lazyLoadImagesGoogleCloud($root);
        }
    }

    function initSlickIn(root) {
        if (typeof jQuery === 'undefined' || !root) return;
        jQuery(root).find('.slickBox').each(function () {
            var $el = jQuery(this);
            if ($el.hasClass('slick-initialized')) return;
            $el.slick({
                infinite: false,
                slidesToShow: 3.01,
                slidesToScroll: 3,
                arrows: true,
                prevArrow: '<button class="slick-arrow slick-prev" aria-label="Previous"><i class="fa-solid fa-angle-left"></i></button>',
                nextArrow: '<button class="slick-arrow slick-next" aria-label="Next"><i class="fa-solid fa-angle-right"></i></button>',
                responsive: [
                    { breakpoint: 1023, settings: { infinite: false, slidesToShow: 2.6, slidesToScroll: 2, arrows: true } },
                    { breakpoint: 767, settings: { infinite: false, slidesToShow: 1.7, slidesToScroll: 1, arrows: true } },
                    { breakpoint: 577, settings: { infinite: false, slidesToShow: 1.3, slidesToScroll: 1, arrows: false } }
                ]
            });
        });
    }

    function clearFragmentPlaceholders(el) {
        if (!el) return;
        el.querySelectorAll('.pageFragment_skeleton').forEach(function (node) {
            node.remove();
        });
        el.querySelectorAll('.loadingGridBox--pageFragment').forEach(function (node) {
            node.remove();
        });
        el.querySelectorAll('.pageFragment_content .loadingGridBox, .pageFragment_content .loadingGridBox_note').forEach(function (node) {
            node.remove();
        });
    }

    function onFragmentLoaded(el, html) {
        if (isFullPageHtml(html)) {
            throw new Error('full page html');
        }
        var content = el.querySelector('.pageFragment_content');
        if (content) {
            content.innerHTML = html;
        } else {
            var target = el.querySelector('.pageFragment_inner') || el;
            target.innerHTML = html;
        }
        clearFragmentPlaceholders(el);

        el.setAttribute('data-fragment-loaded', '1');
        el.removeAttribute('aria-busy');
        el.style.minHeight = '0';
        el.classList.remove('pageFragment--loading');
        el.classList.add('pageFragment--loaded');

        if (!html || !String(html).trim()) {
            el.classList.add('pageFragment--empty');
        }

        if (typeof window.refreshCollapsibleGrids === 'function') {
            window.refreshCollapsibleGrids(el);
        }
        initSlickIn(el);
        hydrateLazyImagesIn(el);
        requestAnimationFrame(function () { hydrateLazyImagesIn(el); });

        if (typeof window.initTourLocationFilter === 'function') {
            window.initTourLocationFilter(el.closest('.sd-tour-location-page') || document);
        }

        var serviceCatalog = el.querySelector('#js_serviceFilter_parent');
        if (serviceCatalog) {
            serviceCatalog.classList.add('tourGrid');
        }
    }

    function showFragmentError(el, detail) {
        el.removeAttribute('aria-busy');
        el.classList.remove('pageFragment--loading');
        el.classList.add('pageFragment--error');
        if (detail && window.console && console.warn) {
            console.warn('[pageFragment]', el.getAttribute('data-fragment-section'), detail);
        }
        el.innerHTML = '<p class="pageFragment_error" role="alert">Không tải được nội dung. Vui lòng tải lại trang.</p>';
    }

    function resolveFragmentUrl(raw) {
        var url = (raw || '').trim();
        if (!url) return '';
        if (url.indexOf('fragments/') === -1) return '';
        /* Bỏ absolute URL sai host/protocol — chỉ giữ path+query */
        if (/^https?:\/\//i.test(url)) {
            try {
                var u = new URL(url, window.location.origin);
                url = u.pathname + u.search;
            } catch (e) {
                return '';
            }
        }
        if (url.charAt(0) !== '/') url = '/' + url;
        return url;
    }

    function getPageLocale(el) {
        return document.documentElement.getAttribute('data-current-locale')
            || (el && el.getAttribute('data-fragment-locale'))
            || document.documentElement.lang
            || 'vi';
    }

    function buildCanonicalFragmentUrl(seoId, section, locale, kind) {
        var pageType = (kind || 'tour-location').trim();
        var q = 'section=' + encodeURIComponent(section) + '&locale=' + encodeURIComponent(locale || 'vi');
        return '/fragments/' + pageType + '/' + seoId + '?' + q;
    }

    function buildFragmentUrlFromEl(el) {
        var seoId = el.getAttribute('data-fragment-seo-id');
        var section = el.getAttribute('data-fragment-section');
        var kind = el.getAttribute('data-fragment-kind') || 'tour-location';
        if (seoId && section) {
            return buildCanonicalFragmentUrl(seoId, section, getPageLocale(el), kind);
        }
        return resolveFragmentUrl(el.getAttribute('data-fragment-url'));
    }

    function loadFragment(el) {
        if (el.getAttribute('data-fragment-loaded') === '1') return;
        var url = buildFragmentUrlFromEl(el);
        if (!url) {
            showFragmentError(el, 'missing or invalid data-fragment-url');
            return;
        }

        el.classList.add('pageFragment--loading');
        el.setAttribute('aria-busy', 'true');

        fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }
                return res.text();
            })
            .then(function (html) { onFragmentLoaded(el, html); })
            .catch(function (err) { showFragmentError(el, err && err.message ? err.message : 'fetch failed'); });
    }

    window.initPageFragments = function (root) {
        var scope = root || document;
        var nodes = scope.querySelectorAll('[data-page-fragment]:not([data-fragment-loaded])');
        for (var i = 0; i < nodes.length; i++) loadFragment(nodes[i]);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { window.initPageFragments(); });
    } else {
        window.initPageFragments();
    }
})();
</script>
@endpush
