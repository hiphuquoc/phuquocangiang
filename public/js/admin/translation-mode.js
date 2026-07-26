/* ============================================================
 * V3.1 — Admin Translation Mode JS
 *
 * Trách nhiệm:
 *  1) Override action của form chính (#formAction) → POST về
 *     /he-thong/translation/{locale}/{seoId} thay vì controller gốc.
 *  2) Auto-disable mọi input/textarea/select có name KHÔNG nằm
 *     trong window.TRANSLATABLE_INPUTS (whitelist do server gửi).
 *  3) Visual highlight các trường dịch được (data-translatable="1").
 *  4) Ẩn các nút thao tác master data (xoá, đổi giá, ngày, ...).
 *  5) Override AJAX endpoint admin-tour-* để chuyển về translation
 *     equivalent (nếu đã có); ẩn nút mở modal Add nếu chưa hỗ trợ.
 *
 * Globals (set bởi layout):
 *  - window.TRANSLATION_MODE          boolean
 *  - window.TRANSLATION_LOCALE        string
 *  - window.TRANSLATION_SEO_ID        int
 *  - window.TRANSLATION_DEFAULT_CODE  string
 *  - window.TRANSLATABLE_INPUTS       array<string>  whitelist input names
 *  - window.TRANSLATION_SAVE_URL      string
 *  - window.TRANSLATION_BACK_URL      string
 *  - window.TRANSLATION_LANGUAGE      {code,name,flag}
 *  - window.TRANSLATION_DEFAULT_LANGUAGE {code,name}
 * ============================================================ */
(function () {
    'use strict';

    var WHITELIST = new Set(window.TRANSLATABLE_INPUTS || []);
    // Inputs LUÔN ALLOW (meta, csrf)
    var ALWAYS_ALLOW = new Set([
        '_token', '_method', 'id', 'type',
        // Một số form gốc dùng các fields chính kèm dịch
    ]);

    document.addEventListener('DOMContentLoaded', function () {
        addPreviewButton();

        if (!window.TRANSLATION_MODE) return;

        document.body.classList.add('translation-mode');

        overrideMainFormAction();
        markAndDisableInputs();
        hideMasterDataActions();
        addPreviewButton();
        injectBodyContentField();
        addAiTranslationPanel();
        injectPerFieldTranslateButtons();
        prefillTranslatedValues();
    });

    /* === 1) Override main form action === */
    function overrideMainFormAction() {
        var form = document.getElementById('formAction');
        if (!form) {
            // Cố tìm form chính bằng heuristic: form duy nhất chứa nhiều input + button submit
            var forms = document.querySelectorAll('form');
            forms.forEach(function (f) {
                if (!f.id) f.id = 'formActionTranslation';
            });
            form = document.querySelector('form');
        }
        if (!form) return;

        if (window.TRANSLATION_SAVE_URL) {
            form.setAttribute('action', window.TRANSLATION_SAVE_URL);
        }
        // Inject hidden marker
        var marker = document.createElement('input');
        marker.type = 'hidden';
        marker.name = '_translation_mode';
        marker.value = '1';
        form.appendChild(marker);

        var localeMarker = document.createElement('input');
        localeMarker.type = 'hidden';
        localeMarker.name = '_translation_locale';
        localeMarker.value = window.TRANSLATION_LOCALE || '';
        form.appendChild(localeMarker);
    }

    /* === 2) Mark + disable inputs theo whitelist === */
    function markAndDisableInputs() {
        var elements = document.querySelectorAll('input, textarea, select');
        elements.forEach(function (el) {
            var name = el.getAttribute('name');
            if (!name) return;

            // Skip elements ngoài form (modal mẫu, etc.)
            if (!el.closest('form')) return;

            // Hidden inputs: luôn allow nếu có ALWAYS_ALLOW; nhưng disable inputs ID khác (FK)
            if (el.type === 'hidden') {
                if (ALWAYS_ALLOW.has(name) || name.indexOf('_translation_') === 0) {
                    el.dataset.translatable = '0';
                    return;
                }
                // Hidden input có thể là id của relation row → cần giữ để server map
                // (server sẽ ignore nếu không match alias)
                if (isHiddenIdInput(name)) {
                    el.dataset.translatable = '0';
                    return;
                }
                // Các hidden khác: drop value để không submit master data
                el.disabled = true;
                el.dataset.translatable = '0';
                return;
            }

            // Buttons không cần xử lý
            if (el.type === 'submit' || el.type === 'button') return;

            // CSRF
            if (ALWAYS_ALLOW.has(name)) {
                el.dataset.translatable = '0';
                return;
            }

            // Resolve base name (strip array indexes)
            var baseName = baseNameOf(name);

            if (WHITELIST.has(baseName)) {
                el.dataset.translatable = '1';
                // Mark đã có translation hay chưa (heuristic: value khác rỗng & locale != default)
                // Đây là setting visual; thực sự "đã dịch" cần server check
                if (el.value && el.value.trim() !== '') {
                    el.dataset.translated = '1';
                }
                return;
            }

            // Outside whitelist → disable (giữ value để translator thấy bản gốc)
            el.disabled = true;
            el.dataset.translatable = '0';
            el.title = 'Trường này không cần dịch (chỉ chỉnh sửa trên bản gốc ' +
                (window.TRANSLATION_DEFAULT_LANGUAGE && window.TRANSLATION_DEFAULT_LANGUAGE.code
                 ? window.TRANSLATION_DEFAULT_LANGUAGE.code.toUpperCase()
                 : 'gốc') + ').';
        });
    }

    /**
     * Trả về base name của input (strip [..] suffixes).
     * VD: 'timetable[2][tour_timetable_title]' → 'tour_timetable_title'
     *     'name'                                → 'name'
     *     'repeater[0][apply_age]'              → 'apply_age'
     */
    function baseNameOf(name) {
        // Lấy phần cuối cùng trong [..] nếu có; nếu không có [..] thì trả luôn
        var match = name.match(/\[([^\[\]]+)\]\s*$/);
        if (match) return match[1];
        return name;
    }

    function isHiddenIdInput(name) {
        var b = baseNameOf(name);
        // Common ID patterns
        return /(_id|_info_id|tour_option_id|question_answer_info_id)$/.test(b);
    }

    /* === 3) Ẩn các nút/elements thao tác master data === */
    function hideMasterDataActions() {
        // Heuristic selectors
        var selectors = [
            // Nút thêm relation (data-repeater-create)
            'button[data-repeater-create]',
            // Nút xoá (data-repeater-delete)
            '[data-repeater-delete]',
            // Nút tạo mới option/timetable
            '.btnCreateOption', '.btnCreateTimetable',
            // Modal trigger để upload ảnh master
            // (không ẩn — translator có thể xem)
        ];
        selectors.forEach(function (sel) {
            document.querySelectorAll(sel).forEach(function (el) {
                el.classList.add('translation-hide');
            });
        });

        // Giữ nguyên label nút "Lưu" như form gốc (không override text).
    }

    function addPreviewButton() {
        var previewUrl = window.TRANSLATION_PREVIEW_URL || '';
        if (!previewUrl) return;

        var actionBox = document.querySelector('.pageAdminWithRightSidebar_main_rightSidebar_item.buttonAction');
        if (!actionBox) return;
        if (actionBox.querySelector('.js-translation-preview-btn')) return;

        var backBtn = actionBox.querySelector('a.btn-secondary');
        var previewBtn = document.createElement('a');
        previewBtn.href = previewUrl;
        previewBtn.target = '_blank';
        previewBtn.rel = 'noopener noreferrer';
        previewBtn.className = 'js-translation-preview-btn';
        previewBtn.setAttribute('aria-label', 'Xem trang public');
        previewBtn.title = 'Xem trang public';
        previewBtn.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;color:#6c757d;font-size:18px;text-decoration:none;padding:0 4px;';
        previewBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';

        if (backBtn) {
            actionBox.insertBefore(previewBtn, backBtn);
        } else {
            actionBox.prepend(previewBtn);
        }
    }

    /* === 4) Inject body content textarea (cho seo_content_translations) === */
    function injectBodyContentField() {
        var form = document.getElementById('formAction') || document.querySelector('form');
        if (!form) return;

        var supportsBodyContent = window.TRANSLATION_SUPPORTS_BODY_CONTENT !== false;
        var mainContentArea = form.querySelector('.pageAdminWithRightSidebar_main_content')
            || form.querySelector('.app-content')
            || form;
        var legacyContentInput = form.querySelector('textarea[name="content"]');
        var legacyContentCardWrapper = legacyContentInput
            ? legacyContentInput.closest('.pageAdminWithRightSidebar_main_content_item')
            : null;

        // Những loại trang không dùng body HTML (vd tour detail): bỏ hẳn card này trong mode dịch.
        if (!supportsBodyContent) {
            if (legacyContentCardWrapper) legacyContentCardWrapper.remove();
            return;
        }

        var hasBodyContent = (typeof window.TRANSLATION_BODY_CONTENT !== 'undefined');
        if (!hasBodyContent) return;

        var card = document.createElement('div');
        card.className = 'pageAdminWithRightSidebar_main_content_item width100';
        card.innerHTML = ''
            + '<div class="card">'
            + '  <div class="card-header border-bottom">'
            + '    <h4 class="card-title">'
            + '      <i class="fa-solid fa-language"></i> Nội dung trang dạng HTML — bản dịch'
            + '    </h4>'
            + '  </div>'
            + '  <div class="card-body">'
            + '    <p class="text-muted" style="margin-bottom:8px;font-size:12px;">'
            + '      Đây là phần thân bài (Mô tả chi tiết) hiển thị giữa breadcrumb và FAQ trên frontend.'
            + '      Mỗi ngôn ngữ có 1 phiên bản riêng. Chấp nhận HTML.'
            + '    </p>'
            + '    <div class="formBox">'
            + '      <div class="formBox_full">'
            + '        @if(default_content)' /* placeholder, replaced bên dưới */
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '</div>';

        // Build inner content
        var inner = card.querySelector('.formBox_full');
        inner.innerHTML = '';

        // Bản gốc tham khảo (collapsible)
        if (window.TRANSLATION_BODY_DEFAULT) {
            var defaultBox = document.createElement('div');
            defaultBox.className = 'formBox_full_item';
            defaultBox.innerHTML = ''
                + '<details style="margin-bottom:12px;border:1px dashed #d1d5db;padding:8px;border-radius:4px;">'
                + '  <summary style="cursor:pointer;color:#6b7280;font-weight:500;">'
                + '    Xem bản gốc (' + (window.TRANSLATION_DEFAULT_LANGUAGE && window.TRANSLATION_DEFAULT_LANGUAGE.code ? window.TRANSLATION_DEFAULT_LANGUAGE.code.toUpperCase() : 'gốc') + ')'
                + '  </summary>'
                + '  <pre style="white-space:pre-wrap;background:#f9fafb;padding:8px;margin-top:8px;font-size:11px;max-height:400px;overflow:auto;"></pre>'
                + '</details>';
            defaultBox.querySelector('pre').textContent = window.TRANSLATION_BODY_DEFAULT;
            inner.appendChild(defaultBox);
        }

        // Textarea bản dịch
        var transBox = document.createElement('div');
        transBox.className = 'formBox_full_item';
        transBox.innerHTML = ''
            + '<label class="form-label">'
            + '  Nội dung bản dịch ' + (window.TRANSLATION_LOCALE || '').toUpperCase()
            + '</label>'
            + '<textarea class="form-control" name="body_content_translation" rows="20" style="font-family:monospace;font-size:12px;"></textarea>';
        var ta = transBox.querySelector('textarea');
        ta.value = window.TRANSLATION_BODY_CONTENT || '';
        ta.dataset.translatable = '1';
        if (ta.value.trim() !== '') ta.dataset.translated = '1';
        inner.appendChild(transBox);

        // Đặt đúng vị trí card "Nội dung" gốc để translator thao tác tự nhiên.
        if (legacyContentCardWrapper && legacyContentCardWrapper.parentNode) {
            legacyContentCardWrapper.parentNode.replaceChild(card, legacyContentCardWrapper);
        } else {
            var faqCard = findFaqCardWrapper(mainContentArea);
            if (faqCard && faqCard.parentNode) {
                faqCard.parentNode.insertBefore(card, faqCard);
            } else {
                mainContentArea.appendChild(card);
            }
        }
    }

    function findFaqCardWrapper(root) {
        if (!root) return null;
        var cardTitles = root.querySelectorAll('.card-title');
        for (var i = 0; i < cardTitles.length; i++) {
            var t = (cardTitles[i].textContent || '').toLowerCase();
            if (t.indexOf('câu hỏi thường gặp') !== -1 || t.indexOf('faq') !== -1) {
                return cardTitles[i].closest('.pageAdminWithRightSidebar_main_content_item');
            }
        }
        return null;
    }

    /* === 5) Pre-fill translated values từ DB qua API ===
     *
     * Vì server đã set app()->setLocale() trước khi render view, magic accessor
     * của HasTranslations đã trả về bản dịch sẵn. Front-end không cần làm gì thêm.
     * Nếu cần override (ví dụ tour_options loaded qua AJAX sau page load), gọi:
     *   GET /he-thong/translation/{locale}/{seoId}/values?fields=...
     */
    function prefillTranslatedValues() {
        // No-op for now — handled by server-side magic accessor.
    }

    function addAiTranslationPanel() {
        var actionBox = document.querySelector('.pageAdminWithRightSidebar_main_rightSidebar_item.buttonAction');
        if (!actionBox) return;
        if (document.querySelector('.translationAiPanel')) return;

        var panel = document.createElement('div');
        panel.className = 'pageAdminWithRightSidebar_main_rightSidebar_item translationAiPanel';

        var models = Array.isArray(window.TRANSLATION_AI_MODELS) ? window.TRANSLATION_AI_MODELS : [];
        var modelOptions = models.map(function (m) {
            return '<option value="' + escapeHtml(m) + '">' + escapeHtml(m) + '</option>';
        }).join('');

        panel.innerHTML = ''
            + '<div class="translationAiPanel_title"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Translation</div>'
            + '<p class="translationAiPanel_desc">Bấm <i class="fa-solid fa-wand-magic-sparkles"></i> cạnh từng nhãn để dịch một trường, hoặc dùng nút bên dưới để dịch tất cả.</p>'
            + '<label class="translationAiPanel_label" for="translation-ai-model">Chọn mô hình AI</label>'
            + '<select id="translation-ai-model" class="form-control translationAiPanel_select">' + modelOptions + '</select>'
            + '<label class="translationAiPanel_label" for="translation-ai-template">Prompt template (dùng chung)</label>'
            + '<div class="translationAiPanel_inline">'
            + '  <select id="translation-ai-template" class="form-control translationAiPanel_select"></select>'
            + '  <button type="button" class="btn btn-outline-secondary translationAiPanel_iconBtn" id="translation-ai-template-refresh" title="Tải lại templates"><i class="fa-solid fa-rotate"></i></button>'
            + '</div>'
            + '<label class="translationAiPanel_label" for="translation-ai-template-text">Nội dung prompt</label>'
            + '<textarea id="translation-ai-template-text" class="form-control translationAiPanel_textarea" rows="8" placeholder="Dịch nội dung sau sang ngôn ngữ [target_language]&#10;Yêu cầu:&#10;- Chuẩn văn phong...&#10;Nội dung cần dịch:&#10;&quot;[source]&quot;"></textarea>'
            + '<div class="translationAiPanel_tokens">Token tự thay khi dịch: <code>[source]</code> <code>[target_language]</code> <code>[locale]</code> <code>[seo_type]</code> — dùng chung mọi trang/ngôn ngữ.</div>'
            + '<label class="translationAiPanel_label" for="translation-ai-template-name">Tên template</label>'
            + '<input type="text" id="translation-ai-template-name" class="form-control translationAiPanel_input" placeholder="Chọn template ở trên hoặc nhập tên khi lưu mới">'
            + '<div class="translationAiPanel_actions">'
            + '  <button type="button" class="btn btn-primary translationAiPanel_actionBtn translationAiPanel_actionBtn--hidden" id="translation-ai-template-update" title="Ghi đè template đang chọn">Cập nhật</button>'
            + '  <button type="button" class="btn btn-outline-primary translationAiPanel_actionBtn" id="translation-ai-template-save-new" title="Tạo template mới">Lưu mới</button>'
            + '  <button type="button" class="btn btn-outline-danger translationAiPanel_actionBtn translationAiPanel_actionBtn--icon translationAiPanel_actionBtn--hidden" id="translation-ai-template-delete" title="Xóa template đang chọn"><i class="fa-solid fa-trash-can"></i></button>'
            + '</div>'
            + '<p class="translationAiPanel_hint" id="translation-ai-template-hint"></p>'
            + '<label class="translationAiPanel_debugLabel">'
            + '  <input type="checkbox" id="translation-ai-debug" class="form-check-input">'
            + '  Debug prompt (in ra Console DevTools)'
            + '</label>'
            + '<button type="button" class="btn btn-primary translationAiPanel_btn" id="translation-ai-run"><i class="fa-solid fa-language"></i> Dịch toàn bộ</button>'
            + '<button type="button" class="btn btn-outline-secondary translationAiPanel_btn translationAiPanel_btn--cancel translationAiPanel_actionBtn--hidden" id="translation-ai-cancel">Hủy</button>'
            + '<div class="translationAiPanel_progress translationAiPanel_actionBtn--hidden" id="translation-ai-progress" aria-hidden="true">'
            + '  <div class="translationAiPanel_progressBar" id="translation-ai-progress-bar"></div>'
            + '</div>'
            + '<div class="translationAiPanel_status" id="translation-ai-status"></div>';

        actionBox.insertAdjacentElement('afterend', panel);

        var runBtn = panel.querySelector('#translation-ai-run');
        runBtn.addEventListener('click', function () {
            runAiTranslationDraft(panel);
        });
        panel.querySelector('#translation-ai-cancel').addEventListener('click', function () {
            cancelAiTranslation(panel);
        });
        panel.querySelector('#translation-ai-template-refresh').addEventListener('click', function () {
            loadPromptTemplates(panel, null);
        });
        panel.querySelector('#translation-ai-template-update').addEventListener('click', function () {
            updatePromptTemplate(panel);
        });
        panel.querySelector('#translation-ai-template-save-new').addEventListener('click', function () {
            saveNewPromptTemplate(panel);
        });
        panel.querySelector('#translation-ai-template-delete').addEventListener('click', function () {
            deletePromptTemplate(panel);
        });
        panel.querySelector('#translation-ai-template').addEventListener('change', function () {
            applySelectedTemplate(panel);
            syncTemplateToolbar(panel);
        });

        syncTemplateToolbar(panel);
        loadPromptTemplates(panel, null);
    }

    /** Lưu danh sách template từ API để change handler không cần fetch lại */
    function setPanelPromptRows(panel, rows) {
        panel._promptTemplateRows = Array.isArray(rows) ? rows : [];
    }

    function getPanelPromptRows(panel) {
        return Array.isArray(panel._promptTemplateRows) ? panel._promptTemplateRows : [];
    }

    var aiTranslationAbort = null;
    var aiJobsKeyIndex = null;
    var aiJobsKeyIndexPromise = null;
    var aiBulkRunActive = false;

    function getMainForm() {
        return document.getElementById('formAction') || document.querySelector('form');
    }

    function getAiPanel() {
        return document.querySelector('.translationAiPanel');
    }

    function parseJsonResponse(res) {
        return res.text().then(function (text) {
            var ct = (res.headers.get('content-type') || '').toLowerCase();
            var looksJson = ct.indexOf('application/json') !== -1 || (text.trim().charAt(0) === '{' || text.trim().charAt(0) === '[');
            if (!looksJson) {
                if (res.status === 502 || res.status === 504) {
                    throw new Error('Máy chủ quá tải (HTTP ' + res.status + '). Đang dịch từng trường — nếu vẫn lỗi, thử lại sau vài giây.');
                }
                throw new Error('Phản hồi không hợp lệ (HTTP ' + res.status + ').');
            }
            var json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                throw new Error('Phản hồi không phải JSON hợp lệ.');
            }
            if (!res.ok) {
                throw new Error((json && json.message) ? json.message : ('HTTP ' + res.status));
            }
            return json;
        });
    }

    /**
     * Đọc model + prompt từ sidebar phải (cùng nguồn cho «Dịch toàn bộ» và dịch từng trường).
     */
    function getAiRequestPayload(panel) {
        panel = panel || getAiPanel();
        if (!panel) {
            return { model: '', prompt_template_id: null, prompt_template_text: '', debug: false };
        }
        var modelEl = panel.querySelector('#translation-ai-model');
        var debugEl = panel.querySelector('#translation-ai-debug');
        var templateTextEl = panel.querySelector('#translation-ai-template-text');
        var model = modelEl ? String(modelEl.value || '').trim() : '';
        var templateText = templateTextEl ? String(templateTextEl.value || '') : '';
        var templateId = getSelectedTemplateId(panel);
        return {
            model: model,
            prompt_template_id: templateId,
            prompt_template_text: templateText,
            debug: !!(debugEl && debugEl.checked)
        };
    }

    function validateAiSidebarBeforeTranslate(panel, statusEl) {
        panel = panel || getAiPanel();
        if (!panel) {
            setStatus(statusEl, 'Không tìm thấy panel AI Translation bên phải.', 'error');
            return false;
        }
        var payload = getAiRequestPayload(panel);
        if (!payload.model) {
            setStatus(statusEl, 'Chọn mô hình AI ở sidebar trước khi dịch.', 'error');
            return false;
        }
        return true;
    }

    /** Body POST /ai-translate-field — luôn lấy mới từ sidebar + key trường. */
    function buildTranslateFieldRequestBody(panel, jobKey) {
        return Object.assign({}, getAiRequestPayload(panel), { key: jobKey });
    }

    function logTranslationAiDebug(debug, fallbackLabel, requestBody) {
        if (!debug && !requestBody) return;
        var title = (debug && (debug.label || debug.field_key)) || fallbackLabel || 'AI Translation';
        console.groupCollapsed('%c[AI Translation] ' + title, 'color:#2563eb;font-weight:bold');
        if (requestBody) {
            console.log('%cSidebar → request', 'font-weight:bold;color:#7c3aed', {
                model: requestBody.model,
                prompt_template_id: requestBody.prompt_template_id,
                prompt_template_text: requestBody.prompt_template_text
            });
        }
        if (!debug) {
            console.groupEnd();
            return;
        }
        if (debug.provider || debug.model) {
            console.log('Provider:', debug.provider || '—', '| Model (API):', debug.model || '—');
        }
        if (debug.endpoint) {
            console.log('Endpoint:', debug.endpoint);
        }
        if (debug.system_prompt) {
            console.log('%cSystem prompt', 'font-weight:bold', debug.system_prompt);
        }
        if (debug.user_prompt) {
            console.log('%cUser prompt (gửi API)', 'font-weight:bold;color:#047857', debug.user_prompt);
        }
        if (Array.isArray(debug.messages) && debug.messages.length) {
            console.log('%cMessages payload', 'font-weight:bold');
            debug.messages.forEach(function (m) {
                console.log((m.role || 'unknown') + ':', m.content);
            });
        }
        if (debug.payload) {
            console.log('API payload (không gồm API key):', debug.payload);
        }
        console.groupEnd();
    }

    function setAiTranslationUiBusy(panel, busy) {
        if (!panel) panel = getAiPanel();
        var runBtn = panel ? panel.querySelector('#translation-ai-run') : null;
        var cancelBtn = panel ? panel.querySelector('#translation-ai-cancel') : null;
        var progressWrap = panel ? panel.querySelector('#translation-ai-progress') : null;
        if (runBtn) runBtn.disabled = !!busy;
        if (cancelBtn) {
            cancelBtn.classList.toggle('translationAiPanel_actionBtn--hidden', !busy);
        }
        if (progressWrap) {
            progressWrap.classList.toggle('translationAiPanel_actionBtn--hidden', !busy);
            progressWrap.setAttribute('aria-hidden', busy ? 'false' : 'true');
        }
    }

    /** Chỉ khóa Lưu / Quay lại / preview khi «Dịch toàn bộ». */
    function setNavigationLocked(locked) {
        document.body.classList.toggle('translation-ai-nav-locked', !!locked);
    }

    function getFieldTranslateBtn(inputEl) {
        var label = findLabelForInput(inputEl);
        return label ? label.querySelector('.translationFieldAiBtn') : null;
    }

    function setFieldTranslateBtnLocked(inputEl, locked) {
        var btn = getFieldTranslateBtn(inputEl);
        if (btn) btn.disabled = !!locked;
    }

    function setFieldAiState(el, state) {
        if (!el) return;
        var states = ['idle', 'translating', 'done', 'error'];
        states.forEach(function (s) {
            el.classList.remove('translation-field--' + s);
        });
        if (state && state !== 'idle') {
            el.classList.add('translation-field--' + state);
        }
        el.dataset.aiState = state || 'idle';
        if (state === 'translating') {
            el.setAttribute('readonly', 'readonly');
            el.setAttribute('aria-busy', 'true');
            setFieldTranslateBtnLocked(el, true);
            try { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { /* ignore */ }
        } else {
            el.removeAttribute('readonly');
            el.removeAttribute('aria-busy');
            setFieldTranslateBtnLocked(el, false);
        }
    }

    function clearAllFieldAiStates() {
        document.querySelectorAll('[data-translatable="1"]').forEach(function (el) {
            if (el.dataset.aiState === 'translating') {
                setFieldAiState(el, 'idle');
            }
        });
    }

    function findLabelForInput(el) {
        if (!el) return null;
        if (el.id) {
            var byFor = document.querySelector('label[for="' + cssEscape(el.id) + '"]');
            if (byFor) return byFor;
        }
        var item = el.closest('.formBox_full_item');
        if (item) {
            var inItem = item.querySelector('label.form-label');
            if (inItem) return inItem;
        }
        var cardBody = el.closest('.card-body');
        if (cardBody) {
            var inCard = cardBody.querySelector('label.form-label');
            if (inCard) return inCard;
        }
        return null;
    }

    function buildJobsKeyIndex(form, jobs) {
        var index = {};
        (jobs || []).forEach(function (job) {
            if (!job || !job.key) return;
            if (job.kind === 'field' || job.kind === 'body') {
                if (job.input_name) index[job.input_name] = job.key;
                return;
            }
            if (job.kind === 'array_row' && job.array_name && job.id_alias) {
                var rowIndex = findRowIndexById(form, job.array_name, job.id_alias, job.row_id);
                if (rowIndex < 0) return;
                var fullName = job.array_name + '[' + rowIndex + '][' + job.input_name + ']';
                index[fullName] = job.key;
            }
        });
        return index;
    }

    function fetchAiJobsKeyIndex(form, forceRefresh) {
        if (!forceRefresh && aiJobsKeyIndex) {
            return Promise.resolve(aiJobsKeyIndex);
        }
        if (!forceRefresh && aiJobsKeyIndexPromise) {
            return aiJobsKeyIndexPromise;
        }
        var sourceUrl = window.TRANSLATION_AI_SOURCE_URL || '';
        if (!sourceUrl) {
            return Promise.resolve({});
        }
        aiJobsKeyIndexPromise = fetch(sourceUrl, { headers: { 'Accept': 'application/json' } })
            .then(parseJsonResponse)
            .then(function (json) {
                var jobs = (json && json.success && json.data && json.data.jobs) ? json.data.jobs : [];
                aiJobsKeyIndex = buildJobsKeyIndex(form, jobs);
                return aiJobsKeyIndex;
            })
            .catch(function () {
                aiJobsKeyIndex = {};
                return aiJobsKeyIndex;
            })
            .finally(function () {
                aiJobsKeyIndexPromise = null;
            });
        return aiJobsKeyIndexPromise;
    }

    function resolveJobKeyForInput(form, inputEl) {
        if (!inputEl || !inputEl.name) return '';
        if (inputEl.dataset.aiJobKey) return inputEl.dataset.aiJobKey;
        if (aiJobsKeyIndex && aiJobsKeyIndex[inputEl.name]) {
            inputEl.dataset.aiJobKey = aiJobsKeyIndex[inputEl.name];
            return inputEl.dataset.aiJobKey;
        }
        return '';
    }

    function getTranslateFieldContext(panel) {
        panel = panel || getAiPanel();
        var form = getMainForm();
        var translateUrl = window.TRANSLATION_AI_TRANSLATE_FIELD_URL || '';
        var tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        if (!panel || !form || !translateUrl || !tokenEl || !tokenEl.value) {
            return null;
        }
        return {
            panel: panel,
            form: form,
            translateUrl: translateUrl,
            postHeaders: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenEl.value
            },
            statusEl: panel.querySelector('#translation-ai-status')
        };
    }

    function isFormFieldEl(el) {
        return el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT');
    }

    function translateFieldByKey(ctx, inputEl, jobKey, options) {
        options = options || {};
        if (!ctx || !jobKey) return Promise.reject(new Error('Thiếu thông tin dịch trường.'));

        var fieldEl = isFormFieldEl(inputEl) ? inputEl : null;

        if (fieldEl) setFieldAiState(fieldEl, 'translating');
        if (!options.silentStatus && ctx.statusEl) {
            var label = (fieldEl && fieldEl.getAttribute('name')) || jobKey;
            setStatus(ctx.statusEl, 'Đang dịch: ' + label + '...', 'loading');
        }

        var body = buildTranslateFieldRequestBody(ctx.panel, jobKey);

        return fetch(ctx.translateUrl, {
            method: 'POST',
            headers: ctx.postHeaders,
            body: JSON.stringify(body)
        })
            .then(parseJsonResponse)
            .then(function (json) {
                if (!json || !json.success || !json.data) {
                    throw new Error((json && json.message) ? json.message : 'Dịch trường thất bại');
                }
                applySingleTranslatedField(ctx.form, json.data);
                logTranslationAiDebug(json.debug, json.data.label || jobKey, body);
                if (fieldEl) setFieldAiState(fieldEl, 'done');
                if (!options.silentStatus && ctx.statusEl) {
                    setStatus(ctx.statusEl, 'Đã dịch xong trường này. Kiểm tra và bấm Lưu khi sẵn sàng.', 'success');
                }
                return json;
            })
            .catch(function (err) {
                if (fieldEl) setFieldAiState(fieldEl, 'error');
                if (!options.silentStatus && ctx.statusEl) {
                    setStatus(ctx.statusEl, (err && err.message) ? err.message : 'Lỗi dịch trường này.', 'error');
                }
                throw err;
            })
            .finally(function () { /* trạng thái nút/readonly do setFieldAiState xử lý */ });
    }

    function translateSingleInput(panel, inputEl) {
        panel = panel || getAiPanel();
        var ctx = getTranslateFieldContext(panel);
        if (!ctx) {
            var statusEl = panel ? panel.querySelector('#translation-ai-status') : null;
            setStatus(statusEl, 'Thiếu cấu hình hoặc CSRF token.', 'error');
            return Promise.resolve();
        }
        if (!validateAiSidebarBeforeTranslate(ctx.panel, ctx.statusEl)) {
            return Promise.resolve();
        }

        return fetchAiJobsKeyIndex(ctx.form).then(function () {
            var jobKey = resolveJobKeyForInput(ctx.form, inputEl);
            if (!jobKey) {
                setStatus(ctx.statusEl, 'Trường này không có nội dung gốc để dịch (hoặc chưa đồng bộ danh sách).', 'error');
                return;
            }
            if (aiTranslationAbort && aiTranslationAbort.aborted) return;
            return translateFieldByKey(ctx, inputEl, jobKey);
        });
    }

    function injectPerFieldTranslateButtons() {
        var form = getMainForm();
        if (!form) return;

        form.querySelectorAll('[data-translatable="1"]').forEach(function (inputEl) {
            if (inputEl.type === 'hidden') return;
            var label = findLabelForInput(inputEl);
            if (!label || label.querySelector('.translationFieldAiBtn')) return;

            label.classList.add('translation-fieldLabel');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'translationFieldAiBtn';
            btn.title = 'Dịch trường này bằng AI (dùng template bên phải)';
            btn.setAttribute('aria-label', 'Dịch trường này bằng AI');
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>';

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (btn.disabled || inputEl.dataset.aiState === 'translating') return;
                var panel = getAiPanel();
                translateSingleInput(panel, inputEl);
            });

            label.appendChild(btn);
        });

        fetchAiJobsKeyIndex(form).then(function (index) {
            form.querySelectorAll('[data-translatable="1"]').forEach(function (inputEl) {
                if (inputEl.name && index[inputEl.name]) {
                    inputEl.dataset.aiJobKey = index[inputEl.name];
                }
            });
        });
    }

    function updateAiProgress(panel, done, total) {
        var bar = panel.querySelector('#translation-ai-progress-bar');
        if (!bar) return;
        var pct = total > 0 ? Math.round((done / total) * 100) : 0;
        bar.style.width = pct + '%';
    }

    function cancelAiTranslation(panel) {
        if (aiTranslationAbort) {
            aiTranslationAbort.aborted = true;
            aiTranslationAbort = null;
        }
        aiBulkRunActive = false;
        setNavigationLocked(false);
        clearAllFieldAiStates();
        setAiTranslationUiBusy(panel, false);
        setStatus(panel.querySelector('#translation-ai-status'), 'Đã hủy dịch tự động.', 'error');
    }

    function findInputForJob(form, job) {
        if (!form || !job) return null;
        if (job.kind === 'field' || job.kind === 'body') {
            return form.querySelector('[name="' + cssEscape(job.input_name) + '"]');
        }
        if (job.kind === 'array_row' && job.array_name && job.id_alias) {
            var rowIndex = findRowIndexById(form, job.array_name, job.id_alias, job.row_id);
            if (rowIndex < 0) return null;
            var fullName = job.array_name + '[' + rowIndex + '][' + job.input_name + ']';
            return form.querySelector('[name="' + cssEscape(fullName) + '"]');
        }
        return null;
    }

    function runAiTranslationDraft(panel) {
        var sourceUrl = window.TRANSLATION_AI_SOURCE_URL || '';
        var translateUrl = window.TRANSLATION_AI_TRANSLATE_FIELD_URL || '';
        var statusEl = panel.querySelector('#translation-ai-status');
        var form = document.getElementById('formAction') || document.querySelector('form');
        if (!sourceUrl || !translateUrl || !form) {
            setStatus(statusEl, 'Thiếu cấu hình API dịch AI.', 'error');
            return;
        }

        var tokenEl = form.querySelector('input[name="_token"]');
        if (!tokenEl || !tokenEl.value) {
            setStatus(statusEl, 'Không tìm thấy CSRF token để gọi AI.', 'error');
            return;
        }

        var state = { aborted: false };
        aiTranslationAbort = state;
        aiBulkRunActive = true;
        setAiTranslationUiBusy(panel, true);
        setNavigationLocked(true);
        updateAiProgress(panel, 0, 1);
        setStatus(statusEl, 'Đang tải danh sách trường cần dịch...', 'loading');

        var headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': tokenEl.value
        };
        var postHeaders = Object.assign({
            'Content-Type': 'application/json'
        }, headers);

        if (!validateAiSidebarBeforeTranslate(panel, statusEl)) {
            return;
        }

        fetch(sourceUrl, { headers: headers })
            .then(parseJsonResponse)
            .then(function (json) {
                if (state.aborted) return null;
                if (!json || !json.success || !json.data) {
                    throw new Error((json && json.message) ? json.message : 'Không tải được danh sách trường.');
                }
                var jobs = Array.isArray(json.data.jobs) ? json.data.jobs : [];
                if (jobs.length === 0) {
                    throw new Error('Không có trường nào cần dịch từ bản gốc.');
                }
                aiJobsKeyIndex = buildJobsKeyIndex(form, jobs);
                return translateJobsSequentially(panel, form, jobs, translateUrl, postHeaders, state);
            })
            .then(function (result) {
                if (state.aborted || result === null) return;
                if (result.failed > 0) {
                    setStatus(statusEl, 'Hoàn tất ' + result.done + '/' + result.total + ' trường. ' + result.failed + ' trường lỗi — kiểm tra và thử lại.', 'error');
                } else {
                    setStatus(statusEl, 'Đã dịch ' + result.done + '/' + result.total + ' trường. Kiểm tra lại rồi bấm Lưu.', 'success');
                }
            })
            .catch(function (err) {
                if (!state.aborted) {
                    setStatus(statusEl, (err && err.message) ? err.message : 'Có lỗi khi dịch AI.', 'error');
                }
            })
            .finally(function () {
                if (aiTranslationAbort === state) aiTranslationAbort = null;
                aiBulkRunActive = false;
                setNavigationLocked(false);
                clearAllFieldAiStates();
                setAiTranslationUiBusy(panel, false);
            });
    }

    function translateJobsSequentially(panel, form, jobs, translateUrl, postHeaders, state) {
        var statusEl = panel.querySelector('#translation-ai-status');
        var total = jobs.length;
        var done = 0;
        var failed = 0;
        var index = 0;
        var ctx = {
            panel: panel,
            form: form,
            translateUrl: translateUrl,
            postHeaders: postHeaders,
            statusEl: statusEl
        };

        function next() {
            if (state.aborted) return Promise.resolve(null);
            if (index >= total) {
                return Promise.resolve({ done: done, failed: failed, total: total });
            }

            var job = jobs[index];
            index += 1;
            var label = job.label || job.input_name || job.key;
            var inputEl = findInputForJob(form, job);
            setStatus(statusEl, 'Đang dịch (' + index + '/' + total + '): ' + label + '...', 'loading');
            updateAiProgress(panel, done, total);

            return translateFieldByKey(ctx, inputEl, job.key, { silentStatus: true })
                .then(function () {
                    if (state.aborted) return;
                    done += 1;
                })
                .catch(function (err) {
                    failed += 1;
                    if (inputEl) setFieldAiState(inputEl, 'error');
                    console.warn('AI translate field failed:', job.key, err);
                })
                .then(function () {
                    updateAiProgress(panel, done + failed, total);
                    return next();
                });
        }

        return next();
    }

    function applySingleTranslatedField(form, data) {
        if (!data) return;
        var value = data.translated;
        if (value == null) return;

        if (data.kind === 'field' || data.kind === 'body') {
            setFieldValue(form, data.input_name, value);
            return;
        }

        if (data.kind === 'array_row' && data.array_name && data.id_alias) {
            var rowIndex = findRowIndexById(form, data.array_name, data.id_alias, data.row_id);
            if (rowIndex < 0) return;
            var fullName = data.array_name + '[' + rowIndex + '][' + data.input_name + ']';
            setFieldValue(form, fullName, value);
        }
    }

    function loadPromptTemplates(panel, preferredId) {
        var endpoint = window.TRANSLATION_AI_PROMPT_TEMPLATE_LIST_URL || '';
        if (!endpoint) return;
        var statusEl = panel.querySelector('#translation-ai-status');
        var select = panel.querySelector('#translation-ai-template');
        var url = endpoint + '?scope=translation';
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(parseJsonResponse)
            .then(function (json) {
                if (!json || !json.success) throw new Error(json && json.message ? json.message : 'Load template failed');
                var rows = Array.isArray(json.data) ? json.data : [];
                setPanelPromptRows(panel, rows);
                select.innerHTML = '<option value="">-- Không dùng template --</option>' + rows.map(function (row) {
                    return '<option value="' + row.id + '">' + escapeHtml(row.name) + (row.is_default ? ' (default)' : '') + '</option>';
                }).join('');
                if (preferredId) {
                    select.value = String(preferredId);
                }
                if (!select.value && rows.length > 0) {
                    var defRow = rows.find(function (r) { return r.is_default; });
                    select.value = String((defRow || rows[0]).id);
                }
                applySelectedTemplate(panel, getPanelPromptRows(panel));
                syncTemplateToolbar(panel);
            })
            .catch(function (e) {
                setStatus(statusEl, (e && e.message) ? e.message : 'Không tải được prompt templates', 'error');
            });
    }

    function getSelectedTemplateRow(panel) {
        var id = getSelectedTemplateId(panel);
        if (!id) return null;
        var rows = getPanelPromptRows(panel);
        return rows.find(function (x) { return Number(x.id) === id; }) || null;
    }

    function syncTemplateToolbar(panel) {
        var select = panel.querySelector('#translation-ai-template');
        var updateBtn = panel.querySelector('#translation-ai-template-update');
        var saveNewBtn = panel.querySelector('#translation-ai-template-save-new');
        var delBtn = panel.querySelector('#translation-ai-template-delete');
        var hintEl = panel.querySelector('#translation-ai-template-hint');
        var hasSelection = !!(select && select.value);
        if (saveNewBtn) {
            if (hasSelection) saveNewBtn.classList.add('translationAiPanel_actionBtn--hidden');
            else saveNewBtn.classList.remove('translationAiPanel_actionBtn--hidden');
        }
        if (updateBtn) {
            if (hasSelection) updateBtn.classList.remove('translationAiPanel_actionBtn--hidden');
            else updateBtn.classList.add('translationAiPanel_actionBtn--hidden');
        }
        if (delBtn) {
            if (hasSelection) delBtn.classList.remove('translationAiPanel_actionBtn--hidden');
            else delBtn.classList.add('translationAiPanel_actionBtn--hidden');
        }
        if (hintEl) {
            if (!hasSelection) {
                hintEl.textContent = 'Soạn prompt (dùng token), nhập tên rồi Lưu mới — template áp dụng cho mọi trang dịch.';
            } else {
                var row = getSelectedTemplateRow(panel);
                hintEl.textContent = row && row.is_default
                    ? 'Template mặc định toàn hệ thống. [target_language] / [locale] thay theo trang đang dịch.'
                    : 'Chỉnh prompt hoặc tên rồi Cập nhật; token [target_language], [source]... thay tự động mỗi lần dịch.';
            }
        }
    }

    function applySelectedTemplate(panel, cachedRows) {
        var select = panel.querySelector('#translation-ai-template');
        var promptEl = panel.querySelector('#translation-ai-template-text');
        var nameEl = panel.querySelector('#translation-ai-template-name');
        if (!select || !promptEl) return;
        var rows = cachedRows && cachedRows.length ? cachedRows : getPanelPromptRows(panel);
        var id = parseInt(select.value || '0', 10);
        if (!id) {
            promptEl.value = '';
            if (nameEl) nameEl.value = '';
            return;
        }
        var row = rows.find(function (x) { return Number(x.id) === id; });
        if (!row) return;
        var content = row.template_content;
        if (content == null || String(content).trim() === '') {
            content = (row.part_before || '') + '\n\n[source]\n\n' + (row.part_after || '');
        }
        promptEl.value = String(content || '');
        if (nameEl) nameEl.value = String(row.name || '').trim();
        var modelEl = panel.querySelector('#translation-ai-model');
        if (row.default_model && modelEl && Array.from(modelEl.options).some(function (o) { return o.value === row.default_model; })) {
            modelEl.value = row.default_model;
        }
    }

    function postPromptTemplateSave(panel, payload, successMessage) {
        var endpoint = window.TRANSLATION_AI_PROMPT_TEMPLATE_SAVE_URL || '';
        if (!endpoint) return Promise.reject(new Error('Thiếu URL lưu template.'));
        var form = document.getElementById('formAction') || document.querySelector('form');
        var tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        var statusEl = panel.querySelector('#translation-ai-status');
        if (!tokenEl || !tokenEl.value) {
            setStatus(statusEl, 'Thiếu CSRF token.', 'error');
            return Promise.reject(new Error('CSRF'));
        }
        return fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenEl.value
            },
            body: JSON.stringify(payload)
        })
            .then(parseJsonResponse)
            .then(function (json) {
                if (!json || !json.success || !json.data) throw new Error((json && json.message) ? json.message : 'Lưu template thất bại');
                setStatus(statusEl, successMessage, 'success');
                return json.data;
            })
            .catch(function (e) {
                if (e && e.message !== 'CSRF') {
                    setStatus(statusEl, (e && e.message) ? e.message : 'Lưu template thất bại.', 'error');
                }
                throw e;
            });
    }

    function updatePromptTemplate(panel) {
        var id = getSelectedTemplateId(panel);
        if (!id) {
            setStatus(panel.querySelector('#translation-ai-status'), 'Chọn template cần cập nhật.', 'error');
            return;
        }
        var name = ((panel.querySelector('#translation-ai-template-name') || {}).value || '').trim();
        if (!name) {
            setStatus(panel.querySelector('#translation-ai-status'), 'Nhập tên template trước khi cập nhật.', 'error');
            return;
        }
        var row = getSelectedTemplateRow(panel);
        var payload = {
            id: id,
            name: name,
            scope: 'translation',
            template_content: (panel.querySelector('#translation-ai-template-text') || {}).value || '',
            default_model: (panel.querySelector('#translation-ai-model') || {}).value || '',
            is_default: !!(row && row.is_default)
        };
        postPromptTemplateSave(panel, payload, 'Đã cập nhật prompt template.')
            .then(function (data) {
                loadPromptTemplates(panel, data.id);
            })
            .catch(function () { /* status đã set */ });
    }

    function saveNewPromptTemplate(panel) {
        var name = ((panel.querySelector('#translation-ai-template-name') || {}).value || '').trim();
        if (!name) {
            setStatus(panel.querySelector('#translation-ai-status'), 'Nhập tên cho template mới.', 'error');
            return;
        }
        var payload = {
            name: name,
            scope: 'translation',
            template_content: (panel.querySelector('#translation-ai-template-text') || {}).value || '',
            default_model: (panel.querySelector('#translation-ai-model') || {}).value || '',
            is_default: false
        };
        postPromptTemplateSave(panel, payload, 'Đã lưu prompt template mới.')
            .then(function (data) {
                var nameEl = panel.querySelector('#translation-ai-template-name');
                if (nameEl) nameEl.value = '';
                loadPromptTemplates(panel, data.id);
            })
            .catch(function () { });
    }

    function deletePromptTemplate(panel) {
        var id = getSelectedTemplateId(panel);
        if (!id) return;
        var row = getSelectedTemplateRow(panel);
        var label = row && row.name ? String(row.name).replace(/[\r\n"]/g, ' ') : '#' + id;
        if (!window.confirm('Xóa template "' + label + '"? Hành động này không thể hoàn tác từ giao diện này.')) {
            return;
        }
        var endpoint = window.TRANSLATION_AI_PROMPT_TEMPLATE_DELETE_URL || '';
        if (!endpoint) {
            setStatus(panel.querySelector('#translation-ai-status'), 'Thiếu URL xóa template.', 'error');
            return;
        }
        var form = document.getElementById('formAction') || document.querySelector('form');
        var tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        var statusEl = panel.querySelector('#translation-ai-status');
        if (!tokenEl || !tokenEl.value) {
            setStatus(statusEl, 'Thiếu CSRF token.', 'error');
            return;
        }
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenEl.value
            },
            body: JSON.stringify({ id: id })
        })
            .then(parseJsonResponse)
            .then(function (json) {
                if (!json || !json.success) {
                    throw new Error((json && json.message) ? json.message : 'Xóa thất bại');
                }
                setStatus(statusEl, 'Đã xóa template.', 'success');
                var nameEl = panel.querySelector('#translation-ai-template-name');
                if (nameEl) nameEl.value = '';
                loadPromptTemplates(panel, null);
            })
            .catch(function (e) {
                setStatus(statusEl, (e && e.message) ? e.message : 'Xóa template thất bại.', 'error');
            });
    }

    function getSelectedTemplateId(panel) {
        var select = panel.querySelector('#translation-ai-template');
        if (!select || !select.value) return null;
        var id = parseInt(select.value, 10);
        return Number.isFinite(id) && id > 0 ? id : null;
    }

    function applyAiDraftToForm(data, form) {
        Object.keys(data.fields || {}).forEach(function (name) {
            setFieldValue(form, name, data.fields[name]);
        });

        Object.keys(data.arrays || {}).forEach(function (arrayName) {
            var meta = data.arrays[arrayName] || {};
            var idAlias = meta.id_alias || '';
            (meta.rows || []).forEach(function (row) {
                var rowIndex = findRowIndexById(form, arrayName, idAlias, row.id);
                if (rowIndex < 0) return;
                Object.keys(row.fields || {}).forEach(function (inputName) {
                    var fullName = arrayName + '[' + rowIndex + '][' + inputName + ']';
                    setFieldValue(form, fullName, row.fields[inputName]);
                });
            });
        });

        if (typeof data.body_content_translation === 'string') {
            setFieldValue(form, 'body_content_translation', data.body_content_translation);
        }
    }

    function findRowIndexById(form, arrayName, idAlias, rowId) {
        if (!arrayName || !idAlias) return -1;
        var hiddenInputs = form.querySelectorAll('input[type="hidden"][name]');
        for (var i = 0; i < hiddenInputs.length; i++) {
            var name = hiddenInputs[i].getAttribute('name') || '';
            var match = name.match(new RegExp('^' + escapeRegExp(arrayName) + '\\[(\\d+)\\]\\[' + escapeRegExp(idAlias) + '\\]$'));
            if (!match) continue;
            if (String(hiddenInputs[i].value) === String(rowId)) return parseInt(match[1], 10);
        }
        return -1;
    }

    function setFieldValue(form, inputName, value) {
        var selector = '[name="' + cssEscape(inputName) + '"]';
        var el = form.querySelector(selector);
        if (!el) return;
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
            el.value = value == null ? '' : String(value);
            el.dataset.translated = '1';
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function setStatus(el, text, type) {
        if (!el) return;
        el.className = 'translationAiPanel_status ' + (type ? ('is-' + type) : '');
        el.textContent = text || '';
    }

    function escapeRegExp(str) {
        return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function cssEscape(str) {
        return String(str).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* ============================================================
     * Public API (cho các script khác có thể call)
     * ============================================================ */
    window.TranslationMode = {
        whitelist: WHITELIST,
        baseNameOf: baseNameOf,
        isTranslatableInput: function (name) {
            return WHITELIST.has(baseNameOf(name));
        },
        markInputs: markAndDisableInputs,
        translateField: translateSingleInput,
        refreshFieldButtons: injectPerFieldTranslateButtons,
    };
})();
