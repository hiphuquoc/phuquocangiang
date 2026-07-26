(() => {
  window.hitourHydrateLazySrcImages = function hydrateLazySrcImages(root) {
    const scope = root || document;
    if (!scope?.querySelectorAll) return;
    scope.querySelectorAll('img[data-lazy-src]').forEach((img) => {
      if (img.getAttribute('data-lazy-src-done') === '1') return;
      const url = img.getAttribute('data-lazy-src');
      if (!url?.trim()) return;
      img.setAttribute('data-lazy-src-done', '1');
      img.src = url;
      img.removeAttribute('data-lazy-src');
    });
  };

  window.hitourHydrateLazySrcImages(document);

  const header = document.querySelector('.sd-header');
  const toggle = document.querySelector('.sd-header__toggle');
  const mobileNav = document.querySelector('.sd-mobile-nav');

  const onScroll = () => {
    header?.classList.toggle('sd-header--scrolled', window.scrollY > 80);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  toggle?.addEventListener('click', () => {
    const open = mobileNav?.classList.toggle('is-open');
    toggle.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
  });

  mobileNav?.querySelector('.sd-mobile-nav__backdrop')?.addEventListener('click', () => {
    mobileNav.classList.remove('is-open');
    toggle?.classList.remove('is-open');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  });

  /* Booking tabs */
  document.querySelectorAll('.sd-booking__tabs').forEach((tabs) => {
    const root = tabs.closest('.sd-booking');
    const activateTab = (tab) => {
      if (!tab) return;
      const target = tab.dataset.tab;
      tabs.querySelectorAll('.sd-booking__tab').forEach((t) => {
        t.classList.remove('is-active');
        t.setAttribute('aria-selected', 'false');
      });
      tab.classList.add('is-active');
      tab.setAttribute('aria-selected', 'true');
      root?.querySelectorAll('.sd-booking__panel').forEach((panel) => {
        const active = panel.dataset.panel === target;
        panel.classList.toggle('is-active', active);
        panel.hidden = !active;
      });
    };

    tabs.querySelectorAll('.sd-booking__tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        activateTab(tab);
        closeAllOverlays();
      });
    });

    const defaultTab = root?.dataset.defaultTab;
    if (defaultTab) {
      activateTab(tabs.querySelector(`.sd-booking__tab[data-tab="${defaultTab}"]`));
    }
  });

  /* Custom select & passenger overlays */
  const closeAllPassengers = (except) => {
    document.querySelectorAll('[data-sd-passengers].is-open').forEach((el) => {
      if (el === except) return;
      el.classList.remove('is-open');
      const trigger = el.querySelector('[data-pax-trigger]');
      const panel = el.querySelector('[data-pax-panel]');
      trigger?.setAttribute('aria-expanded', 'false');
      panel?.setAttribute('hidden', '');
    });
  };

  const closeAllSelects = (except) => {
    document.querySelectorAll('[data-sd-select].is-open').forEach((el) => {
      if (el === except) return;
      el.classList.remove('is-open');
      const trigger = el.querySelector('.sd-fctrl__trigger');
      const menu = el.querySelector('.sd-fctrl__menu');
      trigger?.setAttribute('aria-expanded', 'false');
      menu?.setAttribute('hidden', '');
    });
  };

  const syncHeroOverlay = () => {
    const hero = document.querySelector('.sd-hero');
    if (!hero) return;
    const hasOpen = hero.querySelector(
      '[data-sd-date-picker].is-open, [data-sd-passengers].is-open, [data-sd-select].is-open',
    );
    hero.classList.toggle('is-overlay-open', !!hasOpen);
    hero.closest('.sd-hero-shell')?.classList.toggle('is-overlay-open', !!hasOpen);
  };

  const getDatePanelAnchor = (root) => root.querySelector('.sd-fctrl__box--date');

  const resetFloatingDatePanel = (panel, root) => {
    if (!panel) return;
    panel.classList.remove('is-fixed', 'is-portaled');
    panel.style.position = '';
    panel.style.top = '';
    panel.style.left = '';
    panel.style.width = '';
    panel.style.right = '';
    panel.style.zIndex = '';
    if (root?._sdDateReflow) {
      window.removeEventListener('scroll', root._sdDateReflow, true);
      window.removeEventListener('resize', root._sdDateReflow);
      delete root._sdDateReflow;
    }
    if (panel.dataset.sdCalPortaled === '1') {
      const anchor = panel._sdCalAnchor || getDatePanelAnchor(root);
      anchor?.appendChild(panel);
      delete panel._sdCalAnchor;
      delete panel.dataset.sdCalPortaled;
    }
  };

  const positionFloatingDatePanel = (panel, trigger, root) => {
    if (!panel || !trigger || panel.hasAttribute('hidden')) return;

    if (panel.dataset.sdCalPortaled !== '1') {
      const anchor = getDatePanelAnchor(root);
      panel._sdCalAnchor = anchor;
      panel.dataset.sdCalPortaled = '1';
      document.body.appendChild(panel);
    }

    const gap = 6;
    const minWidth = 320;
    const margin = 8;
    const rect = trigger.getBoundingClientRect();
    const width = Math.max(rect.width, minWidth);
    let left = rect.left;
    const vw = window.innerWidth;

    if (left + width > vw - margin) left = Math.max(margin, vw - width - margin);
    if (left < margin) left = margin;

    panel.classList.add('is-fixed', 'is-portaled');
    panel.style.position = 'fixed';
    panel.style.top = `${rect.bottom + gap}px`;
    panel.style.left = `${left}px`;
    panel.style.width = `${width}px`;
    panel.style.right = 'auto';
    panel.style.zIndex = '220';

    const panelHeight = panel.offsetHeight;
    if (panelHeight > 0 && rect.bottom + gap + panelHeight > window.innerHeight - margin) {
      const topAbove = rect.top - gap - panelHeight;
      if (topAbove >= margin) panel.style.top = `${topAbove}px`;
    }

    const reflow = () => {
      if (panel.hasAttribute('hidden') || !root.classList.contains('is-open')) return;
      positionFloatingDatePanel(panel, trigger, root);
    };

    if (!root._sdDateReflow) {
      root._sdDateReflow = reflow;
      window.addEventListener('scroll', reflow, { passive: true, capture: true });
      window.addEventListener('resize', reflow, { passive: true });
    }
  };

  const closeAllDatePickers = (except) => {
    document.querySelectorAll('[data-sd-date-picker].is-open').forEach((el) => {
      if (el === except) return;
      el.classList.remove('is-open');
      const trigger = el.querySelector('[data-date-trigger]');
      const panel = el.querySelector('[data-date-panel]');
      trigger?.setAttribute('aria-expanded', 'false');
      panel?.setAttribute('hidden', '');
      resetFloatingDatePanel(panel, el);
    });
  };

  const closeAllOverlays = (except) => {
    closeAllSelects(except?.hasAttribute?.('data-sd-select') ? except : null);
    closeAllPassengers(except?.hasAttribute?.('data-sd-passengers') ? except : null);
    closeAllDatePickers(except?.hasAttribute?.('data-sd-date-picker') ? except : null);
    syncHeroOverlay();
  };

  const formatPaxSummary = (counts) => {
    const parts = [];
    if (counts.adult > 0) parts.push(`${counts.adult} người lớn`);
    if (counts.child > 0) parts.push(`${counts.child} trẻ em`);
    if (counts.senior > 0) parts.push(`${counts.senior} cao tuổi`);
    return parts.length ? parts.join(', ') : 'Chọn hành khách';
  };

  document.querySelectorAll('[data-sd-select]').forEach((root) => {
    const trigger = root.querySelector('.sd-fctrl__trigger');
    const menu = root.querySelector('.sd-fctrl__menu');
    const valueEl = root.querySelector('.sd-fctrl__value');
    const hidden = root.querySelector('input[type="hidden"]');
    if (!trigger || !menu) return;

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const willOpen = !root.classList.contains('is-open');
      closeAllOverlays(root);
      root.classList.toggle('is-open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (willOpen) menu.removeAttribute('hidden');
      else menu.setAttribute('hidden', '');
      syncHeroOverlay();
    });

    menu.querySelectorAll('[role="option"]').forEach((opt) => {
      opt.addEventListener('click', () => {
        const val = opt.getAttribute('data-value') || '';
        const label = opt.textContent?.trim() || '';
        menu.querySelectorAll('[role="option"]').forEach((o) => {
          o.classList.remove('is-selected');
          o.setAttribute('aria-selected', 'false');
        });
        opt.classList.add('is-selected');
        opt.setAttribute('aria-selected', 'true');
        if (valueEl) valueEl.textContent = label;
        if (hidden) hidden.value = val;
        root.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        menu.setAttribute('hidden', '');
        syncHeroOverlay();
      });
    });
  });

  document.addEventListener('click', (e) => {
    if (e.target.closest(
      '[data-date-trigger], [data-date-panel], [data-pax-trigger], [data-pax-panel], [data-sd-select] .sd-fctrl__trigger, [data-sd-select] .sd-fctrl__menu',
    )) return;
    closeAllOverlays();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllOverlays();
  });

  /* Passenger picker */
  document.querySelectorAll('[data-sd-passengers]').forEach((root) => {
    const maxTotal = parseInt(root.getAttribute('data-max-total') || '9', 10);
    const minAdult = parseInt(root.getAttribute('data-min-adult') || '1', 10);
    const trigger = root.querySelector('[data-pax-trigger]');
    const panel = root.querySelector('[data-pax-panel]');
    const summary = root.querySelector('[data-pax-summary]');
    const types = ['adult', 'child', 'senior'];
    const inputs = {};
    const vals = {};
    types.forEach((type) => {
      inputs[type] = root.querySelector(`[data-pax-input="${type}"]`);
      vals[type] = root.querySelector(`[data-pax-value="${type}"]`);
    });

    const getCounts = () => {
      const counts = { adult: 0, child: 0, senior: 0 };
      types.forEach((type) => {
        counts[type] = parseInt(inputs[type]?.value || '0', 10);
      });
      return counts;
    };

    const totalOf = (counts) => counts.adult + counts.child + counts.senior;

    const sync = () => {
      const counts = getCounts();
      if (summary) summary.textContent = formatPaxSummary(counts);
      types.forEach((type) => {
        if (vals[type]) vals[type].textContent = String(counts[type]);
      });
      root.querySelectorAll('[data-pax-step]').forEach((btn) => {
        const type = btn.getAttribute('data-pax-type');
        const dir = btn.getAttribute('data-pax-step');
        const val = counts[type];
        let disabled = false;
        if (dir === 'down') {
          disabled = type === 'adult' ? val <= minAdult : val <= 0;
        } else {
          disabled = totalOf(counts) >= maxTotal;
        }
        btn.disabled = disabled;
      });
    };

    trigger?.addEventListener('click', (e) => {
      e.stopPropagation();
      const willOpen = !root.classList.contains('is-open');
      closeAllOverlays(willOpen ? root : null);
      root.classList.toggle('is-open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (willOpen) panel?.removeAttribute('hidden');
      else panel?.setAttribute('hidden', '');
      syncHeroOverlay();
    });

    panel?.addEventListener('click', (e) => e.stopPropagation());

    root.querySelector('[data-pax-done]')?.addEventListener('click', () => {
      root.classList.remove('is-open');
      trigger?.setAttribute('aria-expanded', 'false');
      panel?.setAttribute('hidden', '');
      syncHeroOverlay();
    });

    root.querySelectorAll('[data-pax-step]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const type = btn.getAttribute('data-pax-type');
        const dir = btn.getAttribute('data-pax-step');
        const counts = getCounts();
        if (dir === 'up') {
          if (totalOf(counts) >= maxTotal) return;
          counts[type] += 1;
        } else {
          if (type === 'adult' && counts.adult <= minAdult) return;
          if (type !== 'adult' && counts[type] <= 0) return;
          counts[type] -= 1;
        }
        if (inputs[type]) inputs[type].value = String(counts[type]);
        sync();
      });
    });

    sync();
  });

  /* Date picker */
  const VI_MONTHS = [
    'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
    'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12',
  ];

  const parseJsonAttr = (el, attr, fallback) => {
    try {
      const raw = el.getAttribute(attr);
      if (!raw) return fallback;
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : fallback;
    } catch {
      return fallback;
    }
  };

  const parseISODate = (iso) => {
    if (!iso) return null;
    const parts = iso.split('-').map(Number);
    if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
  };

  const formatISO = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  };

  const formatDisplay = (iso) => {
    if (!iso) return '';
    const [y, m, d] = iso.split('-');
    return `${d}/${m}/${y}`;
  };

  const sameDay = (a, b) => a && b && a.getTime() === b.getTime();

  const isBeforeDay = (a, b) => a && b && a.getTime() < b.getTime();

  const isAfterDay = (a, b) => a && b && a.getTime() > b.getTime();

  const startOfMonth = (date) => new Date(date.getFullYear(), date.getMonth(), 1);

  const daysInMonth = (year, month) => new Date(year, month + 1, 0).getDate();

  document.querySelectorAll('[data-sd-date-picker]').forEach((root) => {
    const mode = root.getAttribute('data-mode') === 'range' ? 'range' : 'single';
    const minDate = parseISODate(root.getAttribute('data-min') || '');
    const maxDate = parseISODate(root.getAttribute('data-max') || '');
    const disabledSet = new Set(parseJsonAttr(root, 'data-disabled-dates', []));
    const unavailableSet = new Set(parseJsonAttr(root, 'data-unavailable-dates', []));
    const availableList = parseJsonAttr(root, 'data-available-dates', []);
    let availableSet = availableList.length ? new Set(availableList) : null;
    const showLegend = root.getAttribute('data-show-legend') !== 'false';

    const trigger = root.querySelector('[data-date-trigger]');
    const panel = root.querySelector('[data-date-panel]');
    const summary = root.querySelector('[data-date-summary]');
    const titleEl = root.querySelector('[data-date-title]');
    const grid = root.querySelector('[data-date-grid]');
    const btnPrev = root.querySelector('[data-date-prev]');
    const btnNext = root.querySelector('[data-date-next]');
    const btnClear = root.querySelector('[data-date-clear]');
    const btnDone = root.querySelector('[data-date-done]');
    const inputSingle = root.querySelector('[data-date-input="single"]');
    const inputStart = root.querySelector('[data-date-input="start"]');
    const inputEnd = root.querySelector('[data-date-input="end"]');
    const placeholder = summary?.textContent?.trim() || (mode === 'range' ? 'Chọn ngày nhận – trả phòng' : 'Chọn ngày');

    let viewMonth = startOfMonth(parseISODate(root.getAttribute('data-value') || root.getAttribute('data-value-end') || '') || new Date());
    let selectedStart = parseISODate(root.getAttribute('data-value') || inputStart?.value || inputSingle?.value || '');
    let selectedEnd = mode === 'range' ? parseISODate(root.getAttribute('data-value-end') || inputEnd?.value || '') : null;
    let rangeDraft = null;

    const getDayState = (date) => {
      const iso = formatISO(date);
      if (minDate && isBeforeDay(date, minDate)) return 'disabled';
      if (maxDate && isAfterDay(date, maxDate)) return 'disabled';
      if (disabledSet.has(iso)) return 'disabled';
      if (unavailableSet.has(iso)) return 'unavailable';
      if (availableSet && !availableSet.has(iso)) return 'unavailable';
      return 'available';
    };

    const isSelectable = (date) => getDayState(date) === 'available';

    const syncInputs = () => {
      if (mode === 'range') {
        if (inputStart) inputStart.value = selectedStart ? formatISO(selectedStart) : '';
        if (inputEnd) inputEnd.value = selectedEnd ? formatISO(selectedEnd) : '';
      } else if (inputSingle) {
        inputSingle.value = selectedStart ? formatISO(selectedStart) : '';
      }
    };

    const syncSummary = () => {
      if (!summary) return;
      let text = placeholder;
      if (mode === 'range') {
        if (selectedStart && selectedEnd) {
          text = `${formatDisplay(formatISO(selectedStart))} – ${formatDisplay(formatISO(selectedEnd))}`;
        } else if (selectedStart) {
          text = `${formatDisplay(formatISO(selectedStart))} – …`;
        }
      } else if (selectedStart) {
        text = formatDisplay(formatISO(selectedStart));
      }
      summary.textContent = text;
      summary.classList.toggle('is-placeholder', text === placeholder);
    };

    const closePanel = () => {
      root.classList.remove('is-open');
      trigger?.setAttribute('aria-expanded', 'false');
      panel?.setAttribute('hidden', '');
      rangeDraft = null;
      resetFloatingDatePanel(panel, root);
      syncHeroOverlay();
    };

    const reflowCalendar = () => {
      if (!root.classList.contains('is-open') || panel?.hasAttribute('hidden')) return;
      requestAnimationFrame(() => positionFloatingDatePanel(panel, trigger, root));
    };

    const selectDate = (date) => {
      if (!isSelectable(date)) return;

      if (mode === 'single') {
        selectedStart = date;
        selectedEnd = null;
        syncInputs();
        syncSummary();
        renderCalendar();
        return;
      }

      if (!selectedStart || (selectedStart && selectedEnd)) {
        selectedStart = date;
        selectedEnd = null;
        rangeDraft = date;
      } else if (rangeDraft) {
        if (isBeforeDay(date, selectedStart)) {
          selectedEnd = selectedStart;
          selectedStart = date;
        } else if (sameDay(date, selectedStart)) {
          selectedEnd = null;
        } else {
          selectedEnd = date;
        }
        rangeDraft = null;
      }

      syncInputs();
      syncSummary();
      renderCalendar();
    };

    const clearSelection = () => {
      selectedStart = null;
      selectedEnd = null;
      rangeDraft = null;
      syncInputs();
      syncSummary();
      renderCalendar();
    };

    const renderCalendar = () => {
      if (!grid || !titleEl) return;

      const year = viewMonth.getFullYear();
      const month = viewMonth.getMonth();
      titleEl.textContent = `${VI_MONTHS[month]} ${year}`;

      const firstWeekday = (viewMonth.getDay() + 6) % 7;
      const totalDays = daysInMonth(year, month);
      const prevMonthDays = daysInMonth(year, month - 1);
      const cells = [];

      for (let i = firstWeekday - 1; i >= 0; i -= 1) {
        const day = prevMonthDays - i;
        const date = new Date(year, month - 1, day);
        cells.push({ date, outside: true });
      }

      for (let day = 1; day <= totalDays; day += 1) {
        cells.push({ date: new Date(year, month, day), outside: false });
      }

      while (cells.length % 7 !== 0) {
        const day = cells.length - (firstWeekday + totalDays) + 1;
        cells.push({ date: new Date(year, month + 1, day), outside: true });
      }

      grid.innerHTML = '';

      const today = new Date();
      today.setHours(0, 0, 0, 0);

      cells.forEach(({ date, outside }) => {
        const iso = formatISO(date);
        const state = getDayState(date);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sd-cal__day';
        btn.textContent = String(date.getDate());
        btn.setAttribute('data-date-day', iso);
        btn.setAttribute('role', 'gridcell');
        btn.setAttribute('aria-label', formatDisplay(iso));

        if (outside) btn.classList.add('sd-cal__day--outside');
        if (sameDay(date, today)) btn.classList.add('sd-cal__day--today');
        if (state === 'disabled') {
          btn.classList.add('sd-cal__day--disabled');
          btn.disabled = true;
          btn.setAttribute('aria-disabled', 'true');
        } else if (state === 'unavailable') {
          btn.classList.add('sd-cal__day--unavailable');
          btn.disabled = true;
          btn.setAttribute('aria-disabled', 'true');
        } else {
          btn.classList.add('sd-cal__day--available');
        }

        const inRange = mode === 'range'
          && selectedStart
          && selectedEnd
          && date.getTime() > selectedStart.getTime()
          && date.getTime() < selectedEnd.getTime();

        if (sameDay(date, selectedStart)) {
          btn.classList.add('sd-cal__day--selected');
          btn.setAttribute('aria-selected', 'true');
        }
        if (mode === 'range' && selectedEnd && sameDay(date, selectedEnd)) {
          btn.classList.add('sd-cal__day--range-end');
          btn.setAttribute('aria-selected', 'true');
        }
        if (inRange) btn.classList.add('sd-cal__day--in-range');

        if (isSelectable(date)) {
          btn.addEventListener('click', () => selectDate(date));
        }

        grid.appendChild(btn);
      });

      const monthStart = startOfMonth(viewMonth);
      const monthEnd = new Date(viewMonth.getFullYear(), viewMonth.getMonth() + 1, 0);
      if (btnPrev) {
        btnPrev.disabled = !!(minDate && monthEnd.getTime() < minDate.getTime());
      }
      if (btnNext) {
        btnNext.disabled = !!(maxDate && monthStart.getTime() > maxDate.getTime());
      }

      const legend = root.querySelector('.sd-cal__legend');
      if (legend) legend.hidden = !showLegend;

      if (root.classList.contains('is-open')) reflowCalendar();
    };

    trigger?.addEventListener('click', (e) => {
      e.stopPropagation();
      const willOpen = !root.classList.contains('is-open');
      closeAllOverlays(willOpen ? root : null);
      root.classList.toggle('is-open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (willOpen) {
        panel?.removeAttribute('hidden');
        if (selectedStart) viewMonth = startOfMonth(selectedStart);
        else if (minDate) viewMonth = startOfMonth(minDate);
        renderCalendar();
        requestAnimationFrame(() => {
          requestAnimationFrame(() => positionFloatingDatePanel(panel, trigger, root));
        });
        syncHeroOverlay();
      } else {
        panel?.setAttribute('hidden', '');
        resetFloatingDatePanel(panel, root);
        syncHeroOverlay();
      }
    });

    panel?.addEventListener('click', (e) => e.stopPropagation());

    btnPrev?.addEventListener('click', () => {
      viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() - 1, 1);
      renderCalendar();
    });

    btnNext?.addEventListener('click', () => {
      viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() + 1, 1);
      renderCalendar();
    });

    btnClear?.addEventListener('click', () => clearSelection());
    btnDone?.addEventListener('click', () => closePanel());

    syncInputs();
    syncSummary();
    renderCalendar();

    root.sdDatePicker = {
      getValue: () => (mode === 'range'
        ? {
          start: selectedStart ? formatISO(selectedStart) : '',
          end: selectedEnd ? formatISO(selectedEnd) : '',
        }
        : { date: selectedStart ? formatISO(selectedStart) : '' }),
      setUnavailable: (dates) => {
        unavailableSet.clear();
        (dates || []).forEach((d) => unavailableSet.add(d));
        renderCalendar();
      },
      setDisabled: (dates) => {
        disabledSet.clear();
        (dates || []).forEach((d) => disabledSet.add(d));
        renderCalendar();
      },
      setAvailable: (dates) => {
        if (!dates?.length) {
          availableSet = null;
        } else {
          availableSet = new Set(dates);
        }
        renderCalendar();
      },
      clear: () => clearSelection(),
    };
  });

  /* Stepper */
  document.querySelectorAll('[data-sd-stepper]').forEach((root) => {
    const min = parseInt(root.getAttribute('data-min') || '1', 10);
    const max = parseInt(root.getAttribute('data-max') || '9', 10);
    const valEl = root.querySelector('[data-step-value]');
    const hidden = root.querySelector('input[type="hidden"]');
    const btnDown = root.querySelector('[data-step="down"]');
    const btnUp = root.querySelector('[data-step="up"]');

    const sync = (next) => {
      const v = Math.min(max, Math.max(min, next));
      if (valEl) valEl.textContent = String(v);
      if (hidden) hidden.value = String(v);
      if (btnDown) btnDown.disabled = v <= min;
      if (btnUp) btnUp.disabled = v >= max;
      return v;
    };

    let current = parseInt(hidden?.value || valEl?.textContent || String(min), 10);
    sync(current);

    btnDown?.addEventListener('click', () => { current = sync(current - 1); });
    btnUp?.addEventListener('click', () => { current = sync(current + 1); });
  });

  /* Route swap */
  document.querySelectorAll('[data-sd-route-swap]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const route = btn.closest('[data-sd-route]');
      if (!route) return;
      const cols = route.querySelectorAll('.sd-route__col[data-sd-select]');
      if (cols.length < 2) return;

      const swapCol = (col) => {
        const hidden = col.querySelector('input[type="hidden"]');
        const valueEl = col.querySelector('.sd-fctrl__value');
        const menu = col.querySelector('.sd-fctrl__menu');
        return {
          val: hidden?.value || '',
          label: valueEl?.textContent?.trim() || '',
          hidden,
          valueEl,
          options: Array.from(menu?.querySelectorAll('[role="option"]') || []),
        };
      };

      const a = swapCol(cols[0]);
      const b = swapCol(cols[1]);

      if (a.hidden) a.hidden.value = b.val;
      if (a.valueEl) a.valueEl.textContent = b.label;
      a.options.forEach((o) => {
        const sel = o.getAttribute('data-value') === b.val;
        o.classList.toggle('is-selected', sel);
        o.setAttribute('aria-selected', sel ? 'true' : 'false');
      });

      if (b.hidden) b.hidden.value = a.val;
      if (b.valueEl) b.valueEl.textContent = a.label;
      b.options.forEach((o) => {
        const sel = o.getAttribute('data-value') === a.val;
        o.classList.toggle('is-selected', sel);
        o.setAttribute('aria-selected', sel ? 'true' : 'false');
      });
    });
  });

  /* Option cards & chips */
  document.querySelectorAll('[data-sd-options] .sd-optcard, [data-sd-chips] .sd-chip').forEach((card) => {
    card.addEventListener('click', () => {
      const group = card.closest('[data-sd-options], [data-sd-chips]');
      if (!group) return;
      const selector = group.hasAttribute('data-sd-options') ? '.sd-optcard' : '.sd-chip';
      group.querySelectorAll(selector).forEach((c) => c.classList.remove('is-selected'));
      card.classList.add('is-selected');
      const input = card.querySelector('input[type="radio"]');
      if (input) input.checked = true;
    });
  });

  document.querySelectorAll('.sd-faq__question').forEach((btn) => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.sd-faq__item');
      const list = item?.closest('.sd-faq');
      const willOpen = !item?.classList.contains('is-open');

      list?.querySelectorAll('.sd-faq__item.is-open').forEach((openItem) => {
        if (openItem === item) return;
        openItem.classList.remove('is-open');
        openItem.querySelector('.sd-faq__question')?.setAttribute('aria-expanded', 'false');
      });

      item?.classList.toggle('is-open', willOpen);
      btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
  });

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const heroMedia = document.querySelector('[data-hero-slider]');
  const heroSlides = heroMedia ? Array.from(heroMedia.querySelectorAll('img')) : [];
  const heroDots = document.querySelectorAll('[data-hero-dot]');
  let heroIndex = 0;
  let heroTimer = null;

  const setHeroSlide = (index) => {
    if (!heroSlides.length) return;
    heroIndex = (index + heroSlides.length) % heroSlides.length;
    heroSlides.forEach((img, i) => {
      img.classList.toggle('is-active', i === heroIndex);
      if (i !== heroIndex) img.style.transform = '';
    });
    heroDots.forEach((dot, i) => dot.classList.toggle('is-active', i === heroIndex));
  };

  const startHeroSlider = () => {
    if (heroSlides.length < 2 || reduceMotion) return;
    heroTimer = window.setInterval(() => setHeroSlide(heroIndex + 1), 6000);
  };

  heroDots.forEach((dot) => {
    dot.addEventListener('click', () => {
      setHeroSlide(parseInt(dot.getAttribute('data-hero-dot') || '0', 10));
      if (heroTimer) {
        clearInterval(heroTimer);
        startHeroSlider();
      }
    });
  });

  startHeroSlider();

  const revealSelector = [
    '[data-reveal]',
    '.sd-section-head',
    '.sd-card',
    '.sd-card--deal',
    '.sd-card--xp',
    '.sd-ship-route__item',
    '.sd-gallery__grid',
    '.sd-voices__head',
    '.sd-voices__rail',
    '.sd-letter__stage',
    '.sd-faq-section__aside',
  ].join(',');

  const revealEls = Array.from(document.querySelectorAll(revealSelector));
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealEls.forEach((el) => el.classList.add('sd-reveal', 'is-in'));
  } else {
    revealEls.forEach((el) => {
      el.classList.add('sd-reveal');
      const siblings = Array.from(el.parentElement?.children || []).filter((c) => c.classList.contains('sd-reveal'));
      const idx = siblings.indexOf(el);
      if (idx > 0) el.style.transitionDelay = Math.min(idx, 6) * 70 + 'ms';
    });
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    revealEls.forEach((el) => io.observe(el));
  }

  if (heroMedia && !reduceMotion) {
    let ticking = false;
    const parallax = () => {
      const y = window.scrollY;
      if (y < window.innerHeight) {
        heroSlides.forEach((img) => {
          if (img.classList.contains('is-active')) {
            const offset = -y * 0.08;
            img.style.transform = 'translate(-50%, calc(-50% + ' + offset + 'px)) scale(1.06)';
          }
        });
      }
      ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (!ticking) { window.requestAnimationFrame(parallax); ticking = true; }
    }, { passive: true });
    parallax();
  }

  /* travelGuide — reveal + mouse parallax (hitour tourLocation) */
  document.querySelectorAll('.travelGuide').forEach((section) => {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      section.classList.add('is-revealed');
    } else {
      const tgIo = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            tgIo.unobserve(entry.target);
          }
        });
      }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' });
      tgIo.observe(section);
    }

    if (reduceMotion) return;
    const stage = section.querySelector('[data-tg-stage]');
    const inner = section.querySelector('.travelGuide_inner');
    if (!stage || !inner) return;
    if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;

    let rafId = null;
    let lastDx = 0;
    let lastDy = 0;

    const applyParallax = () => {
      rafId = null;
      stage.style.setProperty('--pxr', lastDx.toFixed(3));
      stage.style.setProperty('--pyr', lastDy.toFixed(3));
    };

    const onMove = (e) => {
      const rect = stage.getBoundingClientRect();
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      lastDx = Math.max(-0.5, Math.min(0.5, (e.clientX - cx) / rect.width));
      lastDy = Math.max(-0.5, Math.min(0.5, (e.clientY - cy) / rect.height));
      if (rafId == null) rafId = requestAnimationFrame(applyParallax);
    };

    const onLeave = () => {
      lastDx = 0;
      lastDy = 0;
      if (rafId == null) rafId = requestAnimationFrame(applyParallax);
    };

    inner.addEventListener('mousemove', onMove, { passive: true });
    inner.addEventListener('mouseleave', onLeave, { passive: true });
  });

  /* sd-rental — reveal + deck tilt + press feedback */
  document.querySelectorAll('.sd-rental').forEach((section) => {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      section.classList.add('is-revealed');
    } else {
      const rentIo = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            rentIo.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });
      rentIo.observe(section);
    }

    const deck = section.querySelector('[data-rental-deck]');
    if (!deck || reduceMotion) return;

    if (!(window.matchMedia && window.matchMedia('(hover: none)').matches)) {
      let rafId = null;
      let rx = 0;
      let ry = 0;

      const applyTilt = () => {
        rafId = null;
        deck.style.setProperty('--rx', rx.toFixed(3));
        deck.style.setProperty('--ry', ry.toFixed(3));
      };

      deck.addEventListener('mousemove', (e) => {
        const rect = deck.getBoundingClientRect();
        rx = Math.max(-0.5, Math.min(0.5, (e.clientX - rect.left) / rect.width - 0.5));
        ry = Math.max(-0.5, Math.min(0.5, (e.clientY - rect.top) / rect.height - 0.5));
        if (rafId == null) rafId = requestAnimationFrame(applyTilt);
      }, { passive: true });

      deck.addEventListener('mouseleave', () => {
        rx = 0;
        ry = 0;
        if (rafId == null) rafId = requestAnimationFrame(applyTilt);
      }, { passive: true });
    }

    deck.querySelectorAll('[data-rental-pass]').forEach((pass) => {
      const pressOn = () => pass.classList.add('is-pressed');
      const pressOff = () => pass.classList.remove('is-pressed');
      pass.addEventListener('pointerdown', pressOn);
      pass.addEventListener('pointerup', pressOff);
      pass.addEventListener('pointerleave', pressOff);
      pass.addEventListener('pointercancel', pressOff);
    });
  });

  /* sd-gallery — lightbox (hoangsa #beauty) */
  const galleryLightbox = document.getElementById('sdGalleryLightbox');
  const galleryLightboxImg = document.getElementById('sdGalleryLightboxImg');
  const galleryLightboxCap = document.getElementById('sdGalleryLightboxCap');
  const galleryLightboxClose = document.getElementById('sdGalleryLightboxClose');
  const galleryLightboxPrev = document.getElementById('sdGalleryLightboxPrev');
  const galleryLightboxNext = document.getElementById('sdGalleryLightboxNext');
  const galleryLinks = galleryLightbox
    ? [...document.querySelectorAll('[data-sd-gallery-lightbox]')]
    : [];
  let galleryIdx = 0;

  if (galleryLightbox && galleryLightbox.parentElement !== document.body) {
    document.body.appendChild(galleryLightbox);
  }

  const fitGalleryLightbox = () => {
    if (!galleryLightboxImg || !galleryLightbox?.classList.contains('is-open') || !galleryLightboxImg.naturalWidth) return;
    const styles = getComputedStyle(galleryLightbox);
    const padX = parseFloat(styles.paddingLeft) + parseFloat(styles.paddingRight);
    const padY = parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom);
    const footerH = (galleryLightboxCap?.offsetHeight || 0) + 12;
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const isLandscape = vw >= vh;
    const ratio = galleryLightboxImg.naturalWidth / galleryLightboxImg.naturalHeight;
    const maxW = vw - padX;
    const maxH = vh - padY - footerH;
    let w;
    let h;
    if (isLandscape) {
      h = maxH;
      w = h * ratio;
      if (w > maxW) {
        w = maxW;
        h = w / ratio;
      }
    } else {
      w = maxW;
      h = w / ratio;
      if (h > maxH) {
        h = maxH;
        w = h * ratio;
      }
    }
    galleryLightboxImg.style.width = `${Math.round(w)}px`;
    galleryLightboxImg.style.height = `${Math.round(h)}px`;
  };

  const showGallerySlide = (i) => {
    if (!galleryLinks.length || !galleryLightboxImg) return;
    galleryIdx = (i + galleryLinks.length) % galleryLinks.length;
    const link = galleryLinks[galleryIdx];
    const cardImg = link.querySelector('img');
    const src = link.dataset.src || cardImg?.currentSrc || cardImg?.src || '';
    galleryLightboxImg.removeAttribute('style');
    galleryLightboxImg.alt = link.dataset.alt || link.dataset.title || cardImg?.alt || '';
    galleryLightboxImg.style.objectPosition = link.dataset.pos || 'center center';
    if (galleryLightboxCap) {
      const title = link.dataset.title || '';
      const tag = link.dataset.tag || '';
      galleryLightboxCap.textContent = tag ? `${title} · ${tag}` : title;
    }
    galleryLightboxImg.onload = () => {
      fitGalleryLightbox();
      galleryLightboxImg.onload = null;
    };
    galleryLightboxImg.src = src;
    galleryLightbox.classList.add('is-open');
    galleryLightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('sd-gallery-lightbox-open');
    if (galleryLightboxImg.complete) fitGalleryLightbox();
  };

  const hideGalleryLightbox = () => {
    if (!galleryLightbox) return;
    galleryLightbox.classList.remove('is-open');
    galleryLightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('sd-gallery-lightbox-open');
    if (galleryLightboxImg) {
      galleryLightboxImg.removeAttribute('src');
      galleryLightboxImg.removeAttribute('style');
      galleryLightboxImg.onload = null;
    }
  };

  galleryLinks.forEach((link, i) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      showGallerySlide(i);
    });
  });

  galleryLightboxClose?.addEventListener('click', hideGalleryLightbox);
  galleryLightboxPrev?.addEventListener('click', () => showGallerySlide(galleryIdx - 1));
  galleryLightboxNext?.addEventListener('click', () => showGallerySlide(galleryIdx + 1));
  galleryLightbox?.addEventListener('click', (e) => {
    if (e.target === galleryLightbox) hideGalleryLightbox();
  });
  document.addEventListener('keydown', (e) => {
    if (!galleryLightbox?.classList.contains('is-open')) return;
    if (e.key === 'Escape') hideGalleryLightbox();
    if (e.key === 'ArrowLeft') showGallerySlide(galleryIdx - 1);
    if (e.key === 'ArrowRight') showGallerySlide(galleryIdx + 1);
  });
  window.addEventListener('resize', () => {
    if (galleryLightbox?.classList.contains('is-open')) fitGalleryLightbox();
  });

  /* sd-voices — stagger reveal */
  document.querySelectorAll('.sd-voices').forEach((section) => {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      section.classList.add('is-revealed');
    } else {
      const rvIo = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            rvIo.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
      rvIo.observe(section);
    }
  });

  /* sd-voices — paginate grid (3 desktop / 1 mobile) */
  document.querySelectorAll('[data-sd-voices-pager]').forEach((wrap) => {
    const cards = [...wrap.querySelectorAll('[data-sd-voice-card]')];
    const pager = wrap.querySelector('.sd-voices__pager');
    const prevBtn = wrap.querySelector('[data-sd-voices-prev]');
    const nextBtn = wrap.querySelector('[data-sd-voices-next]');
    const counter = wrap.querySelector('[data-sd-voices-counter]');
    const mdQuery = window.matchMedia('(min-width: 768px)');
    const tilts = [-0.4, 0.35, -0.2];
    let page = 0;

    if (cards.length === 0) return;

    const getPerPage = () => (mdQuery.matches ? 3 : 1);

    const update = () => {
      const perPage = getPerPage();
      const totalPages = Math.max(1, Math.ceil(cards.length / perPage));
      page = Math.max(0, Math.min(page, totalPages - 1));

      cards.forEach((card, index) => {
        const visible = index >= page * perPage && index < (page + 1) * perPage;
        card.hidden = !visible;
        if (visible) {
          const slot = index - page * perPage;
          card.style.setProperty('--vc-delay', `${slot * 90}ms`);
          card.style.setProperty('--vc-tilt', `${tilts[slot % 3]}deg`);
        }
      });

      if (prevBtn) prevBtn.disabled = page === 0;
      if (nextBtn) nextBtn.disabled = page >= totalPages - 1;
      if (counter) counter.textContent = `${page + 1} / ${totalPages}`;
      if (pager) pager.hidden = totalPages <= 1;
    };

    prevBtn?.addEventListener('click', () => {
      page -= 1;
      update();
    });
    nextBtn?.addEventListener('click', () => {
      page += 1;
      update();
    });
    mdQuery.addEventListener('change', () => {
      page = 0;
      update();
    });
    update();
  });

  /* sd-faq-section — stagger reveal */
  document.querySelectorAll('.sd-faq-section').forEach((section) => {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      section.classList.add('is-revealed');
    } else {
      const fqIo = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            fqIo.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -8% 0px' });
      fqIo.observe(section);
    }
  });

  /* sd-float — back to top */
  const floatTop = document.getElementById('sd-float-top');
  if (floatTop) {
    const motionReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const toggleFloatTop = () => {
      const show = window.scrollY > 480;
      floatTop.hidden = !show;
      floatTop.classList.toggle('is-visible', show);
    };
    window.addEventListener('scroll', toggleFloatTop, { passive: true });
    toggleFloatTop();
    floatTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: motionReduce ? 'auto' : 'smooth' });
    });
  }
})();
