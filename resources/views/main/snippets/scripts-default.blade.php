<!-- BEGIN: SLICK -->
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<!-- END: SLICK -->

<!-- Google tag (gtag.js) GOOGLE ADS -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-16473804050"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-16473804050');
</script>

@php
    $languageUxConfig = [
        'vi' => ['native' => 'Tiếng Việt', 'english' => 'Vietnamese', 'note' => 'Phù hợp cho khách nội địa'],
        'en' => ['native' => 'English', 'english' => 'English', 'note' => 'Recommended for international guests'],
        'ko' => ['native' => '한국어', 'english' => 'Korean', 'note' => 'Dành cho khách Hàn Quốc'],
        'ja' => ['native' => '日本語', 'english' => 'Japanese', 'note' => 'Dành cho khách Nhật Bản'],
        'zh' => ['native' => '中文', 'english' => 'Chinese', 'note' => 'Dành cho khách Trung Quốc'],
        'fr' => ['native' => 'Français', 'english' => 'French', 'note' => 'Dành cho khách Pháp ngữ'],
        'de' => ['native' => 'Deutsch', 'english' => 'German', 'note' => 'Dành cho khách Đức'],
        'ru' => ['native' => 'Русский', 'english' => 'Russian', 'note' => 'Dành cho khách Nga'],
        'th' => ['native' => 'ไทย', 'english' => 'Thai', 'note' => 'Dành cho khách Thái Lan'],
    ];
    $activeLanguagesPayload = \App\Models\Language::active()->map(function ($lang) {
        return [
            'code' => $lang->code,
            'name' => $lang->name_native ?: $lang->name,
            'flag' => $lang->flag,
            'is_default' => (bool) $lang->is_default,
        ];
    })->values()->all();
@endphp
<script type="text/javascript">
    const CURRENT_LOCALE = @json(current_locale());
    const DEFAULT_LOCALE = @json(default_locale());
    const ACTIVE_LANGUAGES = @json($activeLanguagesPayload);
    const LANGUAGE_UX_CONFIG = @json($languageUxConfig);
    $(window).ready(function(){
        normalizeLocalizedLinks();
        addTableResponsive()
        checkLoginAndSetShow()
        /* fixed sidebar khi scroll */
        const elemt                         = $('.js_scrollFixed');
        const widthElemt                    = elemt.parent().width();
        const widthResponsive               = $(window).width();
        if(elemt.length>0&&widthResponsive>991){
            const positionTopElemt          = elemt.offset().top;
            $(window).scroll(function(){
                const flagScroll            = $('#js_scrollFixed_flag').val();
                if(flagScroll!='false'){
                    const heightFooter      = 700;
                    const positionScrollbar = $(window).scrollTop();
                    const scrollHeight      = $('body').prop('scrollHeight');
                    const heightLimit       = parseInt(scrollHeight - heightFooter - elemt.outerHeight());
                    if(positionScrollbar>positionTopElemt&&positionScrollbar<heightLimit){
                        elemt.addClass('scrollFixedSidebar').css({
                            'width'         : widthElemt,
                            'margin-top'    : '1.5rem'
                        });
                    }else {
                        elemt.removeClass('scrollFixedSidebar').css({
                            'width'         : 'unset',
                            'margin-top'    : 0
                        });
                    }
                }
            });
        }
        
        lazyLoadImagesGoogleCloud();
        lazyLoadImages();
        $(window).scroll(function() {
            lazyLoadImagesGoogleCloud();
            lazyLoadImages();
        });
        /* Mega Tour / Khách sạn: ảnh nền lazy — hydrate khi vào <li> header hoặc đổi tab. */
        $('.headerMain .headerMain_item > ul').on('mouseenter pointerenter focusin', 'li', function(){
            var $mega = $(this).find('.megaMenu--tourTravel, .megaMenu--hotelTravel');
            if ($mega.length) {
                hydrateMegaMenuTourTravelLazyBgs($mega);
            }
        });
    });

    function normalizeLocalizedLinks() {
        if (!CURRENT_LOCALE || CURRENT_LOCALE === DEFAULT_LOCALE) return;
        const localePrefix = '/' + CURRENT_LOCALE;
        const skipPrefixes = ['/images/', '/storage/', '/css/', '/js/', '/fonts/', '/vendor/', '/api/'];
        document.querySelectorAll('a[href]').forEach(function(anchor) {
            const raw = anchor.getAttribute('href');
            if (!raw || raw[0] !== '/' || raw.indexOf('//') === 0 || raw.indexOf('/#') === 0) return;
            if (raw === '/' || raw === '') {
                anchor.setAttribute('href', localePrefix);
                return;
            }
            if (raw === localePrefix || raw.indexOf(localePrefix + '/') === 0) return;
            if (skipPrefixes.some(function(p) { return raw.indexOf(p) === 0; })) return;
            anchor.setAttribute('href', localePrefix + raw);
        });
    }

    function normalizePathWithoutLocale(pathname) {
        if (!pathname) return '/';
        let segments = pathname.split('/').filter(Boolean);
        if (!segments.length) return '/';
        const first = segments[0].toLowerCase();
        if (ACTIVE_LANGUAGES.some(function (lang) { return lang.code.toLowerCase() === first; })) {
            segments.shift();
        }
        return '/' + segments.join('/');
    }

    function buildLocalePath(targetCode) {
        const currentPath = normalizePathWithoutLocale(window.location.pathname || '/');
        const lang = ACTIVE_LANGUAGES.find(function (x) { return x.code === targetCode; });
        if (!lang) return currentPath;
        if (lang.is_default) return currentPath;
        return '/' + lang.code + (currentPath === '/' ? '' : currentPath);
    }

    /* Mobile language switcher cũ đã được thay bằng partial regionSwitcher
       (variant=mobile) — xem main/snippets/regionSwitcher.blade.php.
       Các function initMobileLanguageMenu / toggleMobileLanguageMenu giờ
       không còn cần thiết. */

    /* lazyload image google cloud — query mới mỗi lần (hỗ trợ HTML inject AJAX / fragment) */
    function lazyLoadImagesGoogleCloud($scope) {
        const $root = ($scope && $scope.length) ? $scope : $(document);
        const windowTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        $root.find('img[data-google-cloud]').not('.loaded').each(function() {
            const image = $(this);
            if (!image.is(':visible')) return;
            const imageTop = image.offset().top;
            if (imageTop < windowTop + windowHeight + 2000) {
                loadImageFromGoogleCloud(image);
                image.addClass('loaded');
            }
        });
    }
    /* lazyload image */
    function lazyLoadImages($scope) {
        const $root = ($scope && $scope.length) ? $scope : $(document);
        const windowTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        $root.find('img[data-src]').not('.loaded').each(function() {
            const image = $(this);
            if (!image.is(':visible')) return;
            const imageTop = image.offset().top;
            if (imageTop < windowTop + windowHeight + 2000) {
                loadImageFromHosting(image);
                image.addClass('loaded');
            }
        });
    }
    window.lazyLoadImages = lazyLoadImages;
    window.lazyLoadImagesGoogleCloud = lazyLoadImagesGoogleCloud;
    /* load image */
    function loadImageFromHosting(imageElement){
        $(imageElement).attr('src', $(imageElement).attr('data-src'));
    }
    /* load image from goole cloud */
    function loadImageFromGoogleCloud(imageElement) {
        const urlGoogleCloud = $(imageElement).attr('data-google-cloud');
        const size = $(imageElement).attr('data-size');
        
        $.ajax({
            url: '{{ route("ajax.loadImageFromGoogleCloud") }}',
            type: 'get',
            dataType: 'html',
            data: {
                url_google_cloud: urlGoogleCloud,
                size
            },
            success: function(response) {
                $(imageElement).attr('src', response);
            }
        });
    }
    /* check đăng nhập */
    function checkLoginAndSetShow(){
        const language = $('#language').val();
        $.ajax({
            url         : '{{ route("ajax.checkLoginAndSetShow") }}',
            type        : 'get',
            dataType    : 'json',
            data        : {
                '_token'            : '{{ csrf_token() }}',
                language
            },
            success     : function(response){
                /* button desktop */
                $('#js_checkLoginAndSetShow_button').html(response.button);
                $('#js_checkLoginAndSetShow_button').css('display', 'flex');
                /* button mobile */
                $('#js_checkLoginAndSetShow_buttonMobile').html(response.button_mobile);
                /* modal chung */
                $('#js_checkLoginAndSetShow_modal').html(response.modal);
            }
        });
    }
    /* Go to top — hiện sau khi cuộn, scroll mượt, không ghi đè window.onscroll */
    (function initGotoTop() {
        var $btn = $('#gotoTop');
        if (!$btn.length) return;

        var scrollThreshold = 320;
        var ticking = false;

        function updateGotoTopVisibility() {
            var scrolled = window.pageYOffset || document.documentElement.scrollTop || 0;
            var visible = scrolled > scrollThreshold;
            $btn.toggleClass('is-visible', visible);
            $btn.attr('aria-hidden', visible ? 'false' : 'true');
            ticking = false;
        }

        function onGotoTopScroll() {
            if (!ticking) {
                window.requestAnimationFrame(updateGotoTopVisibility);
                ticking = true;
            }
        }

        $(window).on('scroll.gotoTop', onGotoTopScroll);
        updateGotoTopVisibility();

        $btn.on('click', function (e) {
            e.preventDefault();
            var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({
                top: 0,
                left: 0,
                behavior: prefersReduced ? 'auto' : 'smooth'
            });
        });
    })();
    /* toc content */
    function buildTocContentSidebar(idElement){
        var dataTocContent      = {};
        var i                   = 0;
        var indexToc            = 0;
        $('#'+idElement).find('h2').each(function(){
            let dataId        = $(this).attr('id');
            if(typeof dataId=='undefined'){
                dataId          = 'randomIdTocContent_'+i;
                $(this).attr('id', dataId);
                ++indexToc;
            }
            const name          = $(this)[0].localName;
            const dataTitle     = $(this).html();
            dataTocContent[i]   = {
                id      : dataId,
                name    : name,
                title   : dataTitle
            };
            ++i;
        });
        $.ajax({
            url         : '{{ route("main.buildTocContentSidebar") }}',
            type        : 'get',
            dataType    : 'html',
            data        : {
                dataSend    : dataTocContent
            },
            success     : function(data){
                /* tính toán chiều cao sidebar */
                const heightW       = $(window).height();
                const heightUsed    = $('#js_buildTocContentSidebar_idWrite').parent().outerHeight();
                const height        = parseInt(heightW - heightUsed);
                $('#js_buildTocContentSidebar_idWrite').css('max-height', 'calc('+height+'px - 3rem)').html(data);
                // $('#js_buildTocContentSidebar_idWrite')
            }
        });
    }

    function buildTocContentMain(idElement){
        var dataTocContent      = {};
        var i                   = 0;
        var indexToc            = 0;
        $('#'+idElement).find('h2').each(function(){
            let dataId        = $(this).attr('id');
            if(typeof dataId=='undefined'){
                dataId          = 'randomIdTocContent_'+i;
                $(this).attr('id', dataId);
                ++indexToc;
            }
            const name          = $(this)[0].localName;
            const dataTitle     = $(this).html();
            dataTocContent[i]   = {
                id      : dataId,
                name    : name,
                title   : dataTitle
            };
            ++i;
        });
        $.ajax({
            url         : '{{ route("main.buildTocContentMain") }}',
            type        : 'get', 
            dataType    : 'html',
            data        : {
                dataSend    : dataTocContent
            },
            success     : function(data){
                if(data!=''){
                    $('#tocContentMain').html(data);
                    fixedTocContentIcon();
                    setHeightTocFixed();

                    $(window).resize(function() {
                        fixedTocContentIcon();
                        setHeightTocFixed();
                    });

                    $('.tocFixedIcon, .tocContentMain.tocFixed .tocContentMain_close').click(function(){
                        let elementMenu = $('.tocContentMain.tocFixed');
                        let displayMenu = elementMenu.css('display');
                        if(displayMenu=='none'){
                            elementMenu.css('display', 'block');
                        }else {
                            elementMenu.css('display', 'none');
                        }
                        // fixedTocContentIcon();
                    });

                    $('.tocContentMain_title, .tocContentMain_close').click(function(){
                        let elemtMenu   = $('.tocContentMain .tocContentMain_list');
                        let displayMenu = elemtMenu.css('display');
                        if(displayMenu=='none'){
                            elemtMenu.css('display', 'block');
                            $('.tocContentMain_close').removeClass('hidden');
                        }else {
                            elemtMenu.css('display', 'none');
                            $('.tocContentMain_close').addClass('hidden');
                        }
                    });

                    function fixedTocContentIcon(){
                        let widthS      = $(window).width();
                        let widthC      = $('.container').outerWidth();
                        let leftE       = parseInt((widthS - widthC - 70) / 2);
                        if($(window).width() < 1200){
                            leftE       = parseInt((widthS - widthC + 20) / 2);
                        }
                        $('.tocFixedIcon').css('left', leftE);
                    }

                    function setHeightTocFixed(){
                        let heightToc   = parseInt($(window).height() - 210);
                        $('.tocContentMain.tocFixed .tocContentMain_list').css('height', heightToc+'px');
                    }

                    let element         = $('#tocContentMain');
                    let positionE       = element.offset().top;
                    let heightE         = element.outerHeight();
                    let boxContent      = $('#'+idElement);
                    let positionB       = boxContent.offset().top;
                    let heightB         = boxContent.outerHeight();
                    let heightFooter    = $('.footer').outerHeight();
                    $(document).scroll(function(){
                        let scrollNow   = $(document).scrollTop();
                        let minScroll   = parseInt(heightE + positionE);
                        let maxScroll   = parseInt(heightB + positionB - heightFooter);
                        if(scrollNow > minScroll && scrollNow < maxScroll){ 
                            $('.tocFixedIcon').css('display', 'block');
                        }else {
                            $('.tocFixedIcon').css('display', 'none');
                        }
                    });
                }else {
                    $('#tocContentMain').remove();
                }
                
            }
        });
    }

    function addTableResponsive(){
        $(document).find('table:not(.noResponsive)').each(function(){
            $(this).wrap('<div class="customScrollBar-x"></div>');
        })

        // .wrap('<div class="tableResponsive"></div>')
    }

    /* ===== START:: MENU */
    $(window).on('load', function () {
        /* fixed headerMobile khi scroll */
        const elemt                 = $('.header');
        const positionTopElemt      = elemt.offset().top;
        $(window).scroll(function(){
            const positionScrollbar = $(window).scrollTop();
            // const scrollHeight      = $('body').prop('scrollHeight');
            // const heightLimit       = parseInt(scrollHeight - heightFooter - elemt.outerHeight());
            if(positionScrollbar>parseInt(positionTopElemt+50)){
                elemt.css({
                    'top'       : '0',
                    'position'  : 'fixed',
                    'left'      : 0
                });
            }else {
                elemt.css({
                    'top'       : '0',
                    'position'  : 'relative',
                    'left'      : 0
                });
            }
        });
    });
    function showHideListMenuMobile(thisD){
        let elemtC      = $(thisD).parent().find('> ul');
        let displayC    = elemtC.css('display');
        if(displayC=='none'){
            elemtC.css('display', 'block');
            $(thisD).addClass('is-open');
        }else {
            elemtC.css('display', 'none');
            $(thisD).removeClass('is-open');
        }
    }

    /*
     * Đánh dấu active item trong drawer mobile (nav-mobile) theo URL
     * hiện tại (slug_full). Cũng tự động expand mọi tổ tiên submenu
     * để active item nhìn thấy ngay khi user mở drawer.
     *
     * Chiến lược matching:
     *   • Lấy pathname hiện tại, trim trailing slash.
     *   • Với mỗi `<a href>` trong drawer, parse pathname tương tự và
     *     so sánh exact match (an toàn hơn startsWith vì tránh false
     *     positive khi 1 slug là prefix của slug khác).
     */
    $(document).ready(function(){
        const $drawer = $('#nav-mobile');
        if($drawer.length === 0) return;
        const normalize = (path) => {
            if(!path) return '';
            let p = path.replace(/\/+$/, '');
            return p === '' ? '/' : p;
        };
        const currentPath = normalize(window.location.pathname);
        $drawer.find('.nav-mobile li > a').each(function(){
            const href = $(this).attr('href');
            if(!href || href === '#') return;
            try {
                const url = new URL(href, window.location.origin);
                if(normalize(url.pathname) === currentPath){
                    const $li = $(this).closest('li');
                    $li.addClass('is-active');
                    /* Mở mọi tổ tiên submenu để active item visible */
                    $li.parentsUntil('.nav-mobile_main', 'li').each(function(){
                        $(this).children('ul').css('display', 'block');
                        $(this).children('div').addClass('is-open');
                    });
                    return false; /* break each() khi đã tìm thấy match */
                }
            } catch(e) { /* href không phải URL hợp lệ — bỏ qua */ }
        });
    });
    function openCloseElemt(idElemt){
        let displayE    = $('#' + idElemt).css('display');
        if(displayE=='none'){
            $('#' + idElemt).css('display', 'block');
            $('body').css('overflow', 'hidden');
        }else {
            $('#' + idElemt).css('display', 'none');
            $('body').css('overflow', 'unset');
        }
    }
    function openMegaMenu(id){
        var elemt	= $('#'+id);
        elemt.siblings().removeClass('selected');
        elemt.addClass('selected');
        $('[data-menu]').each(function(){
            var key	= $(this).attr('data-menu');
            if(key==id){
            $(this).css('display', 'flex');
            }else {
                $(this).css('display', 'none');
            }
        });
    }
    function openMegaMenuTourContinent(id){
        var elemt	= $('#'+id);
        elemt.siblings().removeClass('selected');
        elemt.addClass('selected');
        $('[data-menu-tourcontinent]').each(function(){
            var key	= $(this).attr('data-menu-tourcontinent');
            if(key==id){
            $(this).css('display', 'flex');
            }else {
                $(this).css('display', 'none');
            }
        });
    }
    function openMegaMenuTourTravel(id){
        var elemt	= $('#'+id);
        elemt.siblings().removeClass('selected');
        elemt.addClass('selected');
        $('[data-menu-tour-travel]').each(function(){
            var key	= $(this).attr('data-menu-tour-travel');
            if(key==id){
            $(this).css('display', 'flex');
            }else {
            $(this).css('display', 'none');
            }
        });
        scheduleHydrateMegaMenuTourTravelLazyBgs();
    }
    function openMegaMenuHotelTravel(id){
        var elemt	= $('#'+id);
        elemt.siblings().removeClass('selected');
        elemt.addClass('selected');
        $('[data-menu-hotel-travel]').each(function(){
            var key	= $(this).attr('data-menu-hotel-travel');
            if(key==id){
            $(this).css('display', 'flex');
            }else {
            $(this).css('display', 'none');
            }
        });
        var $hotelMega = elemt.closest('.megaMenu--hotelTravel');
        hydrateMegaMenuTourTravelLazyBgs($hotelMega.length ? $hotelMega : $('.megaMenu--hotelTravel'));
    }
    /**
     * Ảnh nền mega Tour (rail VN + hero châu/đảo): không gắn background-image trong HTML
     * để tránh tải sớm khi trang load; chỉ tải sau khi menu Tour / Khách sạn được mở (hover/focus header hoặc đổi tab).
     */
    function hydrateMegaMenuTourTravelLazyBgs($root){
        var $scope = ($root && $root.length) ? $root : $('.megaMenu--tourTravel, .megaMenu--hotelTravel');
        if (!$scope.length) return;
        $scope.find('[data-lazy-bg]').each(function(){
            var el = this;
            if (el.getAttribute('data-lazy-bg-done') === '1') return;
            var raw = el.getAttribute('data-lazy-bg');
            if (!raw || !String(raw).trim()) return;
            el.setAttribute('data-lazy-bg-done', '1');
            var img = new Image();
            img.decoding = 'async';
            img.onload = function(){
                try {
                    el.style.backgroundImage = 'url(' + JSON.stringify(raw) + ')';
                } catch (e) { /* noop */ }
                el.classList.remove('megaMenu_ttVietnamRail__media--placeholder', 'megaMenu_ttTourHero__media--placeholder');
            };
            img.onerror = function(){
                el.setAttribute('data-lazy-bg-error', '1');
                el.removeAttribute('data-lazy-bg-done');
            };
            img.src = raw;
        });
    }
    function scheduleHydrateMegaMenuTourTravelLazyBgs(){
        var $mega = $('.megaMenu--tourTravel');
        if (!$mega.length) return;
        hydrateMegaMenuTourTravelLazyBgs($mega);
    }
    /**
     * Ảnh <img> lazy: src ban đầu là placeholder (1×1), URL thật ở data-lazy-src.
     * Gọi khi mở dropdown / panel — không tải cờ (hoặc ảnh khác) khi trang vừa load.
     * @see docs/performance-lazy-assets.md
     */
    function hydrateLazySrcImages(root) {
        var scope = root || document;
        if (!scope || !scope.querySelectorAll) return;
        scope.querySelectorAll('img[data-lazy-src]').forEach(function (img) {
            if (img.getAttribute('data-lazy-src-done') === '1') return;
            var url = img.getAttribute('data-lazy-src');
            if (!url || !String(url).trim()) return;
            img.setAttribute('data-lazy-src-done', '1');
            img.src = url;
            img.removeAttribute('data-lazy-src');
        });
    }
    window.hitourHydrateLazySrcImages = hydrateLazySrcImages;
    /** Tab miền trong panel Tour Việt Nam (rail ảnh + miền active mở rộng). */
    function switchVietnamTourTab(btn){
        var $btn = $(btn);
        if ($btn.prop('disabled')) return;
        var slug = $btn.attr('data-vn-tab');
        if (!slug) return;
        var $root = $btn.closest('.megaMenu_tourTravelPanel--vietnam');
        var $newPanel = $root.find('[data-vn-tab-panel="' + slug + '"]');
        var $oldPanel = $root.find('[data-vn-tab-panel]:not([hidden])');
        if ($oldPanel.length && $newPanel.length && $oldPanel[0] === $newPanel[0]) {
            return;
        }
        $root.find('.megaMenu_ttVietnamRail').removeClass('is-active').attr('aria-selected', 'false').attr('tabindex', '-1');
        $btn.addClass('is-active').attr('aria-selected', 'true').attr('tabindex', '0');
        $root.find('[data-vn-tab-panel]').each(function(){
            var $p = $(this);
            if ($p.attr('data-vn-tab-panel') === slug) {
                $p.removeAttr('hidden');
            } else {
                $p.attr('hidden', true);
            }
        });
        var $g = $newPanel.find('.megaMenu_vnDestGrid');
        if ($g.length) {
            $g.removeClass('js-vnDestAnim');
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(function(){
                    $g.addClass('js-vnDestAnim');
                    window.setTimeout(function(){ $g.removeClass('js-vnDestAnim'); }, 520);
                });
            } else {
                $g.addClass('js-vnDestAnim');
                window.setTimeout(function(){ $g.removeClass('js-vnDestAnim'); }, 520);
            }
        }
    }
    /* ===== END:: MENU */

    /* ===== In Tour */
    function printContent(el) {
        var restorepage = document.body.innerHTML;
        var printableArea = document.getElementById(el);
        var notToPrints = document.getElementsByClassName("notPrint");
        // Thêm image header vào trang
        var header      = '<img src="https://hitour.vn/storage/images/upload/blue-modern-tour-and-travel-twitter-header-type-manager-upload.webp" style="width:100%;margin-bottom:20px;" />';
        document.body.innerHTML = header + printableArea.innerHTML;
        // Lọc các phần tử có thuộc tính position: fixed và xóa chúng khỏi nội dung in ra
        $(printableArea).find("*").filter(function() {
            return $(this).css("position") === "fixed";
        }).remove();

        while (notToPrints.length > 0) {
            notToPrints[0].parentNode.removeChild(notToPrints[0]);
        }

        window.print();

        // // Đặt lại nội dung trang
        // document.body.innerHTML = restorepage;

        // print xong không thực hiện được các chức năng => reload() lại
        location.reload();
    }
    /* validate form khi nhập */
    function validateWhenType(elementInput, type = 'empty'){
        const idElement         = $(elementInput).attr('id');
        const parent            = $(document).find('[for*="'+idElement+'"]').parent();
        /* validate empty */
        if(type=='empty'){
            const valueElement  = $.trim($(elementInput).val());
            if(valueElement!=''&&valueElement!='0'){
                parent.removeClass('validateErrorEmpty');
                parent.addClass('validateSuccess');
            }else {
                parent.removeClass('validateSuccess');
                parent.addClass('validateErrorEmpty');
            }
        }
        /* validate phone */ 
        if(type=='phone'){
            const valueElement = $.trim($(elementInput).val());
            if(valueElement.length>=10&&/^\d+$/.test(valueElement)){
                parent.removeClass('validateErrorPhone');
                parent.addClass('validateSuccess');
            }else {
                parent.removeClass('validateSuccess');
                parent.addClass('validateErrorPhone');
            }
        }
        /* validate email */ 
        if(type=='email'){
            const valueElement = $.trim($(elementInput).val());
            /* check empty (nếu required) */
            if($(elementInput).prop('required')){
                if(valueElement==''){
                    parent.removeClass('validateSuccess');
                    parent.removeClass('validateErrorEmail');
                    parent.addClass('validateErrorEmpty');
                    return false;
                }
                /* check email hợp lệ */
                if(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valueElement)){
                    parent.removeClass('validateErrorEmail');
                    parent.removeClass('validateErrorEmpty');
                    parent.addClass('validateSuccess');
                }else {
                    parent.removeClass('validateSuccess');
                    parent.removeClass('validateErrorEmpty');
                    parent.addClass('validateErrorEmail');
                }
            }else {
                /* check email hợp lệ */
                if(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valueElement)){
                    parent.removeClass('validateErrorEmail');
                    parent.removeClass('validateErrorEmpty');
                    parent.addClass('validateSuccess');
                }
            }
        }
    }
    /* tính năng registry email ở footer */
    function submitFormRegistryEmail(idForm){
        event.preventDefault();
        const inputEmail = $('#'+idForm).find('[name*=registry_email]');
        const valueEmail = inputEmail.val();
        if(isValidEmail(valueEmail)){
            $.ajax({
                url         : '{{ route("ajax.registryEmail") }}',
                type        : 'get',
                dataType    : 'json',
                data        : {
                    registry_email : valueEmail
                },
                success     : function(response){
                    /* bật thông báo */
                    setMessageModal(response.title, response.content);
                    /* clear value input */
                    inputEmail.val('');
                }
            });
        }else {
            inputEmail.val('');
            inputEmail.attr('placeholder', 'Email không hợp lệ!');
        }
    }
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    /* hiện thông báo modal thành công */
    function openCloseModal(idModal, action = null){
        const elementModal  = $('#'+idModal);
        const flag          = elementModal.css('display');
        /* tooggle */
        if(action==null){
            if(flag=='none'){
                elementModal.css('display', 'flex');
                $('#js_openCloseModal_blur').addClass('blurBackground');
                $('body').css('overflow', 'hidden');
            }else {
                elementModal.css('display', 'none');
                $('#js_openCloseModal_blur').removeClass('blurBackground');
                $('body').css('overflow', 'unset');
            }
        }
        /* đóng */
        if(action=='close'){
            elementModal.css('display', 'none');
            $('#js_openCloseModal_blur').removeClass('blurBackground');
            $('body').css('overflow', 'unset');
        }
        /* mở */
        if(action=='open'){
            elementModal.css('display', 'flex');
            $('#js_openCloseModal_blur').addClass('blurBackground');
            $('body').css('overflow', 'hidden');
        }
    }
</script>