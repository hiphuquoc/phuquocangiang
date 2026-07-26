{{--
   Region Switcher — Language + Currency hợp nhất, deferred-apply.

   Props:
   - $variant: 'desktop' | 'mobile' (mặc định 'desktop')
       + desktop: trigger pill (flag + code + sep + currency code).
       + mobile : trigger icon button (30×30, chỉ hiển thị flag), khi mở
                  dropdown sẽ phủ fullscreen (đồng bộ với UX các action
                  button khác trong header mobile).

   Bao gồm 1 lần JS xử lý (qua `@once @push('scripts-custom')`) — kể cả
   khi partial này được include nhiều lần trong layout, JS chỉ đăng ký 1
   lần và sẽ tự bind tới mọi node `[data-region-switcher]` trong DOM.
--}}
@php
   $variant = $variant ?? 'desktop';
   $isMobile = $variant === 'mobile';

   $__currentLocale  = current_locale();
   $__activeLangs    = \App\Models\Language::active();
   $__currentSeo     = $data ?? ($item->seo ?? ($itemSeo ?? null));
   $__currentSeoMdl  = null;
   if ($__currentSeo instanceof \App\Models\Seo) {
       $__currentSeoMdl = $__currentSeo;
   } elseif (is_object($__currentSeo) && isset($__currentSeo->seo) && $__currentSeo->seo instanceof \App\Models\Seo) {
       $__currentSeoMdl = $__currentSeo->seo;
   } elseif (is_object($__currentSeo) && method_exists($__currentSeo, 'getKey')) {
       $__currentSeoMdl = $__currentSeo->seo ?? null;
   }

   $__currentPath = '/' . ltrim(request()->path(), '/');
   foreach ($__activeLangs as $__l) {
       if (!$__l->is_default && preg_match('#^/' . preg_quote($__l->code, '#') . '(/|$)#', $__currentPath)) {
           $__currentPath = preg_replace('#^/' . preg_quote($__l->code, '#') . '#', '', $__currentPath);
           break;
       }
   }
   $__currentPath = '/' . ltrim($__currentPath, '/');

   $__currentLang = $__activeLangs->firstWhere('code', $__currentLocale) ?? $__activeLangs->first();
   $__languageUxMap = [
      'vi' => ['native' => 'Tiếng Việt', 'english' => 'Vietnamese', 'note' => 'Phù hợp cho khách nội địa'],
      'en' => ['native' => 'English',    'english' => 'English',    'note' => 'Recommended for international guests'],
      'ko' => ['native' => '한국어',     'english' => 'Korean',     'note' => 'Dành cho khách Hàn Quốc'],
      'ja' => ['native' => '日本語',      'english' => 'Japanese',   'note' => 'Dành cho khách Nhật Bản'],
      'zh' => ['native' => '中文',        'english' => 'Chinese',    'note' => 'Dành cho khách Trung Quốc'],
      'es' => ['native' => 'Español',    'english' => 'Spanish',    'note' => 'Dành cho khách nói Tây Ban Nha'],
      'ar' => ['native' => 'العربية',     'english' => 'Arabic',     'note' => 'Dành cho khách Trung Đông'],
      'id' => ['native' => 'Indonesia',  'english' => 'Indonesian', 'note' => 'Dành cho khách Indonesia'],
      'pt' => ['native' => 'Português',  'english' => 'Portuguese', 'note' => 'Dành cho khách Brazil / Bồ Đào Nha'],
      'fr' => ['native' => 'Français',   'english' => 'French',     'note' => 'Dành cho khách Pháp ngữ'],
      'de' => ['native' => 'Deutsch',    'english' => 'German',     'note' => 'Dành cho khách Đức'],
      'ru' => ['native' => 'Русский',    'english' => 'Russian',    'note' => 'Dành cho khách Nga'],
      'th' => ['native' => 'ไทย',         'english' => 'Thai',       'note' => 'Dành cho khách Thái Lan'],
   ];
   $__currentLangCode = strtolower($__currentLang->code ?? $__currentLocale ?? 'vi');
   $__currentLangUx = $__languageUxMap[$__currentLangCode] ?? [
      'native' => $__currentLang->name_native ?? strtoupper($__currentLangCode),
      'english' => $__currentLang->name ?? strtoupper($__currentLangCode),
      'note' => t('region_lang_note_default'),
   ];

   $__currencies      = $availableCurrencies ?? available_currencies();
   $__currentCurrency = $currentCurrency ?? current_currency();
   $__currentCurMeta  = $currentCurrencyMeta ?? current_currency_meta();
   $__rateBase        = currency_rate_base();
   $__rateBaseMeta    = currency_manager()->meta($__rateBase);
   $__switchEndpoint  = (string) config('currency.switch_endpoint', '/currency/switch');
   $__cookieName      = (string) config('currency.cookie.name', 'app_currency');
   $__cookieTtlDays   = (int)    config('currency.cookie.ttl_days', 365);

   $__rootClass = 'regionSwitcher' . ($isMobile ? ' regionSwitcher--mobile' : '');
   $__triggerClass = 'regionSwitcher_trigger' . ($isMobile ? ' regionSwitcher_trigger--mobile' : '');
@endphp

<div class="{{ $__rootClass }}"
     data-region-switcher
     data-variant="{{ $variant }}"
     data-current-locale="{{ $__currentLocale }}"
     data-current-currency="{{ $__currentCurrency }}"
     data-cookie-name="{{ $__cookieName }}"
     data-cookie-days="{{ $__cookieTtlDays }}">

   {{-- =================== TRIGGER =================== --}}
   <button type="button"
           class="{{ $__triggerClass }}"
           aria-haspopup="true" aria-expanded="false"
           aria-label="{{ t('choose_language_currency') }}">
      @if($isMobile)
         {{-- Mobile: flag + dot separator + currency code (vd: 🇨🇳 · CNY).
              Đồng bộ với header mobile action buttons (cao 30px) nhưng pill
              tự co giãn theo nội dung. --}}
         <span class="regionSwitcher_label">
            @if(!empty($__currentLang->flag))
               {{-- Cờ trigger: placeholder + hydrate sau bind (không tải SVG cờ khi parse HTML). --}}
               <img class="regionSwitcher_flag"
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                    width="24"
                    height="24"
                    decoding="async"
                    alt="{{ $__currentLang->name_native }}"
                    data-lazy-src="{{ e($__currentLang->flag) }}" />
            @else
               <span class="regionSwitcher_lang">{{ strtoupper($__currentLang->code ?? $__currentLocale) }}</span>
            @endif
            <span class="regionSwitcher_sep" aria-hidden="true">·</span>
            <span class="regionSwitcher_currency_code">{{ $__currentCurrency }}</span>
         </span>
      @else
         {{-- Desktop: pill đầy đủ — flag + lang code + sep · + currency code.
              (Bỏ currency_symbol để gọn hơn.) --}}
         <span class="regionSwitcher_label">
            @if(!empty($__currentLang->flag))
               <img class="regionSwitcher_flag"
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                    width="24"
                    height="24"
                    decoding="async"
                    alt="{{ $__currentLang->name_native }}"
                    data-lazy-src="{{ e($__currentLang->flag) }}" />
            @endif
            <span class="regionSwitcher_lang">{{ strtoupper($__currentLang->code ?? $__currentLocale) }}</span>
            <span class="regionSwitcher_sep" aria-hidden="true">·</span>
            <span class="regionSwitcher_currency_code">{{ $__currentCurrency }}</span>
         </span>
         <i class="fa-solid fa-chevron-down regionSwitcher_chevron" aria-hidden="true"></i>
      @endif
   </button>

   {{-- =================== MENU =================== --}}
   <div class="regionSwitcher_menu" role="dialog" aria-label="{{ t('choose_language_currency') }}">

      {{-- Mobile-only: header với title + close button (X) --}}
      @if($isMobile)
         <div class="regionSwitcher_mobileHeader">
            <div class="regionSwitcher_mobileHeader_title">
               <i class="fa-solid fa-globe" aria-hidden="true"></i>
               <span>{{ t('language_and_currency') }}</span>
            </div>
            <button type="button" class="regionSwitcher_mobileHeader_close" data-region-cancel aria-label="{{ t('close') }}">
               <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
         </div>
      @endif

      <div class="regionSwitcher_grid">

         {{-- ========== SECTION 1: LANGUAGE (1 cột) ========== --}}
         <section class="regionSwitcher_col regionSwitcher_col--lang" aria-label="{{ t('language') }}">
            <header class="regionSwitcher_col_header">
               <i class="fa-solid fa-language" aria-hidden="true"></i>
               <span class="regionSwitcher_col_header_title">{{ t('language') }}</span>
               <span class="regionSwitcher_col_header_sub">{{ t('display_content') }}</span>
            </header>
            <div class="regionSwitcher_col_list regionSwitcher_col_list--1col" role="group" data-region-col="lang">
               @foreach($__activeLangs as $__lang)
                  @php
                     $__isCurrent = $__lang->code === $__currentLocale;
                     $__href      = locale_url($__lang->code, $__currentSeoMdl);
                     $__langCode  = strtolower($__lang->code);
                     $__langUx    = $__languageUxMap[$__langCode] ?? [
                        'native' => $__lang->name_native ?: strtoupper($__lang->code),
                        'english' => $__lang->name ?: strtoupper($__lang->code),
                        'note' => t('region_lang_note_native'),
                     ];
                  @endphp
                  <button type="button"
                          role="menuitemradio"
                          aria-checked="{{ $__isCurrent ? 'true' : 'false' }}"
                          title="{{ $__lang->name_native }}"
                          class="regionSwitcher_item regionSwitcher_item--lang{{ $__isCurrent ? ' is-selected is-current' : '' }}"
                          data-lang-code="{{ $__lang->code }}"
                          data-lang-href="{{ $__href }}">
                     @if(!empty($__lang->flag))
                        {{-- Cờ trong menu đóng: không tải URL thật cho đến khi mở (hitourHydrateLazySrcImages). --}}
                        <img class="regionSwitcher_item_flag regionSwitcher_item_flag--img"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                             width="24"
                             height="24"
                             decoding="async"
                             alt="{{ $__lang->name_native }}"
                             data-lazy-src="{{ e($__lang->flag) }}" />
                     @else
                        <span class="regionSwitcher_item_flag">{{ strtoupper(substr($__lang->code, 0, 2)) }}</span>
                     @endif
                     <span class="regionSwitcher_item_text">
                        <span class="regionSwitcher_item_title">{{ $__langUx['native'] }}</span>
                        <span class="regionSwitcher_item_sub">{{ $__langUx['english'] }} · {{ strtoupper($__lang->code) }}</span>
                     </span>
                     <span class="regionSwitcher_item_check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                  </button>
               @endforeach
            </div>
         </section>

         {{-- ========== SECTION 2: CURRENCY (1 header — 2 sub-cột song song) ========== --}}
         @if(!empty($__currencies))
         <section class="regionSwitcher_col regionSwitcher_col--currency" aria-label="{{ t('currency') }}">
            <header class="regionSwitcher_col_header">
               <i class="fa-solid fa-coins" aria-hidden="true"></i>
               <span class="regionSwitcher_col_header_title">{{ t('currency') }}</span>
               <span class="regionSwitcher_col_header_sub">{{ t('convert_from') }} {{ $__rateBase }}</span>
            </header>
            <div class="regionSwitcher_col_list regionSwitcher_col_list--2col" role="group" data-region-col="currency">
               @foreach($__currencies as $__code => $__cur)
                  @php
                     $__isCur     = strtoupper($__code) === strtoupper($__currentCurrency);
                     $__isBase    = strtoupper($__code) === strtoupper($__rateBase);
                     $__rateHtml  = format_rate_from_base($__code, $__rateBase, true);
                  @endphp
                  <button type="button"
                          role="menuitemradio"
                          aria-checked="{{ $__isCur ? 'true' : 'false' }}"
                          class="regionSwitcher_item regionSwitcher_item--currency{{ $__isCur ? ' is-selected is-current' : '' }}"
                          data-currency-code="{{ $__code }}"
                          title="{{ ($__cur['name_local'] ?? $__cur['name'] ?? $__code) }} ({{ $__code }})">
                     <span class="regionSwitcher_item_flag" aria-hidden="true">{{ $__cur['flag'] ?? '💱' }}</span>
                     <span class="regionSwitcher_item_text">
                        <span class="regionSwitcher_item_title">
                           {{ $__code }}
                           <span class="regionSwitcher_item_subname">· {{ $__cur['name_local'] ?? ($__cur['name'] ?? $__code) }}</span>
                        </span>
                        @if($__isBase)
                           <span class="regionSwitcher_item_sub regionSwitcher_item_sub--base">{{ t('international_reference') }}</span>
                        @else
                           <span class="regionSwitcher_item_sub">1 {{ $__rateBase }} ≈ {!! $__rateHtml !!}</span>
                        @endif
                     </span>
                     <span class="regionSwitcher_item_symbol" aria-hidden="true">{!! $__cur['symbol_html'] ?? ($__cur['symbol'] ?? '') !!}</span>
                     <span class="regionSwitcher_item_check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                  </button>
               @endforeach
            </div>
         </section>
         @endif

      </div>{{-- /.regionSwitcher_grid --}}

      {{-- ========== FOOTER: APPLY / CANCEL ========== --}}
      <footer class="regionSwitcher_footer">
         <button type="button" class="regionSwitcher_btn regionSwitcher_btn--ghost" data-region-cancel>
            {{ t('cancel') }}
         </button>
         <button type="button" class="regionSwitcher_btn regionSwitcher_btn--primary" data-region-apply>
            <span class="regionSwitcher_btn_label">{{ t('apply') }}</span>
            <i class="fa-solid fa-arrow-right regionSwitcher_btn_icon" aria-hidden="true"></i>
            <span class="regionSwitcher_btn_spinner" aria-hidden="true"></span>
         </button>
      </footer>

      <div class="regionSwitcher_loading" aria-hidden="true">
         <div class="regionSwitcher_loading_spinner"></div>
         <div class="regionSwitcher_loading_text">{{ t('applying') }}</div>
      </div>

      <noscript>
         <form method="post" action="{{ $__switchEndpoint }}" class="regionSwitcher_noscript">
            <input type="hidden" name="redirect" value="{{ $__currentPath }}" />
            <label>{{ t('currency_colon') }}
               <select name="to" onchange="this.form.submit()">
                  @foreach($__currencies as $__code => $__cur)
                     <option value="{{ $__code }}" @selected(strtoupper($__code) === strtoupper($__currentCurrency))>
                        {{ $__code }} · {{ $__cur['name_local'] ?? ($__cur['name'] ?? $__code) }}
                     </option>
                  @endforeach
               </select>
            </label>
         </form>
      </noscript>
   </div>
</div>

@once
@push('scripts-custom')
<script>
/**
 * Region switcher — Language + Currency, deferred apply.
 *
 * Hỗ trợ 2 variant qua attribute `data-variant`:
 *  - desktop (mặc định): pill nhỏ + dropdown thả xuống.
 *  - mobile : icon button + menu fullscreen (CSS xử lý positioning).
 *
 * Khi mở variant mobile, body sẽ được khoá scroll qua class
 * `.is-regionSwitcherFullscreen`. Cancel/X/Apply/click-outside (chỉ với
 * desktop) đều unlock body.
 */
(function() {
   if (window.__regionSwitcherInit) return;
   window.__regionSwitcherInit = true;

   function setCookie(name, value, days) {
      var maxAge = (parseInt(days, 10) || 365) * 24 * 60 * 60;
      var secure = (location.protocol === 'https:') ? '; Secure' : '';
      document.cookie = name + '=' + encodeURIComponent(value) +
         '; Max-Age=' + maxAge +
         '; Path=/' +
         '; SameSite=Lax' + secure;
   }

   function lockBodyScroll(yes) {
      try {
         document.body.classList.toggle('is-regionSwitcherFullscreen', !!yes);
      } catch (e) {}
   }

   function closeAllOthers(except) {
      document.querySelectorAll('[data-region-switcher].open').forEach(function(el){
         if (el === except) return;
         el.classList.remove('open');
         var t = el.querySelector('.regionSwitcher_trigger');
         if (t) t.setAttribute('aria-expanded', 'false');
      });
   }

   function anyOpenMobile() {
      return !!document.querySelector('[data-region-switcher][data-variant="mobile"].open');
   }

   function bindOne(root) {
      if (!root || root.dataset.bound === '1') return;
      root.dataset.bound = '1';

      var trigger = root.querySelector('.regionSwitcher_trigger');
      var menu    = root.querySelector('.regionSwitcher_menu');
      if (!trigger || !menu) return;

      try {
         if (typeof window.hitourHydrateLazySrcImages === 'function') {
            window.hitourHydrateLazySrcImages(trigger);
         }
      } catch (e) { /* noop */ }

      var applyBtn   = menu.querySelector('[data-region-apply]');
      var cancelBtns = menu.querySelectorAll('[data-region-cancel]');
      var langCol    = menu.querySelector('[data-region-col="lang"]');
      var curCol     = menu.querySelector('[data-region-col="currency"]');

      var currentLang     = root.getAttribute('data-current-locale')   || '';
      var currentCurrency = root.getAttribute('data-current-currency') || '';
      var isMobile        = (root.getAttribute('data-variant') === 'mobile');

      function getSelected(col) {
         return col ? col.querySelector('.regionSwitcher_item.is-selected') : null;
      }

      function pendingState() {
         var ls = getSelected(langCol);
         var cs = getSelected(curCol);
         var langCode = ls ? (ls.getAttribute('data-lang-code')     || '') : currentLang;
         var curCode  = cs ? (cs.getAttribute('data-currency-code') || '') : currentCurrency;
         return {
            langCode: langCode,
            langHref: ls ? (ls.getAttribute('data-lang-href') || '') : '',
            curCode:  curCode,
            langChanged: langCode && langCode !== currentLang,
            curChanged:  curCode  && curCode  !== currentCurrency,
         };
      }

      function refreshApply() {
         var s = pendingState();
         var changed = !!(s.langChanged || s.curChanged);
         applyBtn.removeAttribute('disabled');
         applyBtn.classList.toggle('is-ready', changed);
      }

      function resetSelection() {
         menu.querySelectorAll('.regionSwitcher_item.is-selected').forEach(function(el){
            if (!el.classList.contains('is-current')) el.classList.remove('is-selected');
         });
         menu.querySelectorAll('.regionSwitcher_item.is-current').forEach(function(el){
            el.classList.add('is-selected');
         });
         refreshApply();
      }

      function closeMenu() {
         if (!root.classList.contains('open')) return;
         root.classList.remove('open');
         trigger.setAttribute('aria-expanded', 'false');
         resetSelection();
         if (!anyOpenMobile()) lockBodyScroll(false);
      }

      trigger.addEventListener('click', function(e) {
         e.stopPropagation();
         var willOpen = !root.classList.contains('open');
         closeAllOthers(root);
         root.classList.toggle('open', willOpen);
         trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
         if (willOpen) {
            try {
               if (typeof window.hitourHydrateLazySrcImages === 'function') {
                  window.hitourHydrateLazySrcImages(menu);
               }
            } catch (e) { /* noop */ }
            resetSelection();
            if (isMobile) lockBodyScroll(true);
         } else {
            if (isMobile && !anyOpenMobile()) lockBodyScroll(false);
         }
      });

      // Click outside (chỉ áp dụng cho desktop variant — mobile fullscreen
      // không có "outside" để click).
      document.addEventListener('click', function(e) {
         if (isMobile) return;
         if (!root.contains(e.target)) closeMenu();
      });
      document.addEventListener('keydown', function(e) {
         if (e.key === 'Escape' && root.classList.contains('open')) {
            closeMenu();
            try { trigger.focus(); } catch (err) {}
         }
      });

      function bindItems(col) {
         if (!col) return;
         col.querySelectorAll('.regionSwitcher_item').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
               e.preventDefault();
               col.querySelectorAll('.is-selected').forEach(function(el){ el.classList.remove('is-selected'); });
               btn.classList.add('is-selected');
               refreshApply();
            });
         });
      }
      bindItems(langCol);
      bindItems(curCol);

      applyBtn.addEventListener('click', function() {
         var s = pendingState();
         if (!s.langChanged && !s.curChanged) {
            closeMenu();
            return;
         }

         root.classList.add('is-switching');
         applyBtn.classList.add('is-loading');
         applyBtn.setAttribute('disabled', 'disabled');
         cancelBtns.forEach(function(c){ c.setAttribute('disabled', 'disabled'); });

         if (s.curChanged) {
            var cookieName = root.getAttribute('data-cookie-name') || 'app_currency';
            var days       = root.getAttribute('data-cookie-days') || '365';
            setCookie(cookieName, s.curCode, days);
         }

         try {
            if (s.langChanged && s.langHref) {
               window.location.assign(s.langHref);
            } else {
               window.location.reload();
            }
         } catch (err) {
            window.location.href = s.langChanged && s.langHref ? s.langHref : window.location.href;
         }
      });

      cancelBtns.forEach(function(btn){
         btn.addEventListener('click', function() { closeMenu(); });
      });

      resetSelection();
   }

   function init() {
      document.querySelectorAll('[data-region-switcher]').forEach(bindOne);
   }

   if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
   } else {
      init();
   }
})();
</script>
@endpush
@endonce
