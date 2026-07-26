{{-- Sheet mobile: sticky hero ngang + cuộn dọc + anchor + scroll spy (không slick). --}}
<script type="text/javascript">
    function guideBookMobileDestroy($panel) {
        if (!$panel || !$panel.length) return;
        var tm = $panel.data('guideBookMobilePendingTimer');
        if (tm) clearTimeout(tm);
        $panel.removeData('guideBookMobilePending');
        $panel.removeData('guideBookMobilePendingTimer');
        var $scroll = $panel.find('.guideBookBoxMobile_sheet_scroll');
        $scroll.off('scroll.guideBookMobile');
        $(window).off('resize.guideBookMobile');
        $panel.find('[data-guide-target]').off('click.guideBookMobile');
    }

    function guideBookMobileScrollToStep($panel, index) {
        var $scroll = $panel.find('.guideBookBoxMobile_sheet_scroll');
        var $sticky = $panel.find('.guideBookBoxMobile_stickyHero');
        var $sec = $panel.find('.guideBookBoxMobile_section[data-guide-step="' + index + '"]');
        if (!$scroll.length || !$sec.length) return;

        var stickyH = $sticky.length ? $sticky.outerHeight() : 0;
        var motion =
            window.matchMedia &&
            !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var sr = $scroll[0];
        var sec = $sec[0];
        var y =
            sec.getBoundingClientRect().top -
            sr.getBoundingClientRect().top +
            sr.scrollTop -
            stickyH -
            10;

        sr.scrollTo({
            top: Math.max(0, y),
            behavior: motion ? 'smooth' : 'auto',
        });
    }

    /**
     * Chỉ cuộn track hero ngang — tránh scrollIntoView làm rung / cuộn nhầm vùng dọc .sheet_scroll.
     */
    function guideBookMobileScrollPillIntoView($panel, $pill) {
        var $track = $panel.find('.guideBookBoxMobile_stickyHero_track');
        if (!$track.length || !$pill.length) return;
        var track = $track[0];
        var pill = $pill[0];
        var trackRect = track.getBoundingClientRect();
        var pillRect = pill.getBoundingClientRect();
        var delta =
            pillRect.left +
            pillRect.width / 2 -
            (trackRect.left + trackRect.width / 2);
        var next = track.scrollLeft + delta;
        var max = Math.max(0, track.scrollWidth - track.clientWidth);
        var motion =
            window.matchMedia &&
            !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        track.scrollTo({
            left: Math.max(0, Math.min(max, next)),
            behavior: motion ? 'smooth' : 'auto',
        });
    }

    function guideBookMobileSyncPills($panel, idx) {
        var $pills = $panel.find('[data-guide-target]');
        $pills.removeClass('is-active').attr('aria-selected', 'false').attr('tabindex', '-1');
        var $on = $pills.filter('[data-guide-step="' + idx + '"]');
        $on.addClass('is-active').attr('aria-selected', 'true').attr('tabindex', '0');
        $panel.attr('data-current-step', idx);
        guideBookMobileScrollPillIntoView($panel, $on);
    }

    function guideBookMobileSpy($panel) {
        var $scroll = $panel.find('.guideBookBoxMobile_sheet_scroll');
        var $sticky = $panel.find('.guideBookBoxMobile_stickyHero');
        var sections = $panel.find('.guideBookBoxMobile_section').toArray();
        if (!$scroll.length || !sections.length) return;

        var sr = $scroll[0];
        var stickyH = $sticky.length ? $sticky.outerHeight() : 0;
        var st = sr.scrollTop;
        var active = 0;

        for (var i = sections.length - 1; i >= 0; i--) {
            var sec = sections[i];
            var top =
                sec.getBoundingClientRect().top -
                sr.getBoundingClientRect().top +
                sr.scrollTop;
            if (st + stickyH >= top - 12) {
                active = i;
                break;
            }
        }

        /* Đang cuộn tới bước user vừa chọn: spy vẫn thấy bước cũ → không đè is-active (tránh nhấp nháy). */
        var pending = $panel.data('guideBookMobilePending');
        if (pending !== undefined && pending !== null) {
            var pi = parseInt(pending, 10);
            if (!isNaN(pi) && pi === active) {
                $panel.removeData('guideBookMobilePending');
                var tm = $panel.data('guideBookMobilePendingTimer');
                if (tm) clearTimeout(tm);
                $panel.removeData('guideBookMobilePendingTimer');
            } else {
                return;
            }
        }

        var cur = parseInt($panel.attr('data-current-step'), 10);
        if (isNaN(cur) || cur !== active) {
            guideBookMobileSyncPills($panel, active);
        }
    }

    function guideBookMobileInit($panel) {
        if (!$panel || !$panel.length) return;
        var $scroll = $panel.find('.guideBookBoxMobile_sheet_scroll');
        if (!$scroll.length) return;

        guideBookMobileDestroy($panel);

        $scroll.scrollTop(0);
        guideBookMobileSyncPills($panel, 0);

        $scroll.on('scroll.guideBookMobile', function () {
            guideBookMobileSpy($panel);
        });

        $panel.find('[data-guide-target]').on('click.guideBookMobile', function () {
            var i = parseInt($(this).attr('data-guide-step'), 10);
            if (!isNaN(i)) {
                $panel.data('guideBookMobilePending', i);
                var prevTm = $panel.data('guideBookMobilePendingTimer');
                if (prevTm) clearTimeout(prevTm);
                var clearTm = setTimeout(function () {
                    $panel.removeData('guideBookMobilePending');
                    $panel.removeData('guideBookMobilePendingTimer');
                    guideBookMobileSpy($panel);
                }, 1400);
                $panel.data('guideBookMobilePendingTimer', clearTm);
                guideBookMobileSyncPills($panel, i);
                guideBookMobileScrollToStep($panel, i);
            }
        });

        $(window).on('resize.guideBookMobile', function () {
            guideBookMobileSpy($panel);
        });

        requestAnimationFrame(function () {
            guideBookMobileSpy($panel);
        });
    }

    function showHideElement(idElement) {
        var $panel = $('#' + idElement);
        if (!$panel.length) return;
        var $root = $panel.closest('.guideBookBoxMobile');
        var $backdrop = $root.find('.guideBookBoxMobile_backdrop');
        var $scroll = $panel.find('.guideBookBoxMobile_sheet_scroll');
        var isHidden = $panel.is(':hidden');
        if (isHidden) {
            guideBookMobileDestroy($panel);
            $backdrop.css('display', 'block');
            $panel.css('display', 'flex');
            $('body').addClass('guideBookMobileSheet_open');
            requestAnimationFrame(function () {
                guideBookMobileInit($panel);
            });
        } else {
            guideBookMobileDestroy($panel);
            if ($scroll.length) {
                $scroll.scrollTop(0);
            }
            $backdrop.css('display', 'none');
            $panel.css('display', 'none');
            $('body').removeClass('guideBookMobileSheet_open');
        }
    }
</script>
