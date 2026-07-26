{{--
    Tabs đa ngôn ngữ tự động cho admin form.

    Cách dùng tối thiểu (auto-detect mọi thứ từ $item):
        @include('admin.snippets.formMultilingualTabs')

    Cách dùng explicit (override khi cần):
        @include('admin.snippets.formMultilingualTabs', [
            'tabId'           => 'tour-i18n',                    // unique trên page
            'languages'       => \App\Models\Language::active(), // collection
            'translationsSeo' => $seoTranslations    ?? [],      // [code => SeoTranslation]
            'translationsEnt' => $entityTranslations ?? [],      // [code => *Translation]
            'fields'          => $translatableFields ?? [],      // ['name','description',...]
            'fieldLabels'     => ['name' => 'Tên hiển thị', ...],
            'longTextFields'  => ['description','content',...],
            'showSeoFields'   => true,
            'showEntityFields'=> true,
        ])

    Form sẽ submit về controller dưới dạng:
        translations[<code>][seo][title|description|seo_title|seo_description|slug|link_canonical|status]
        translations[<code>][entity][<field>]

    Auto-persist phía server: Seo::saved() event hook trong AppServiceProvider sẽ
    gọi EntityTranslationService::persistFromRequest() trên mọi admin POST có
    chứa trường `translations`. Không cần sửa controller.
--}}
@php
    use App\Models\Language;
    use App\Models\Seo;
    use App\Services\EntityTranslationService;

    /* ===== AUTO-DETECT từ $item nếu chưa được truyền vào ===== */
    $tabId            = $tabId            ?? 'multilingual-' . uniqid();
    $languages        = $languages        ?? Language::active();
    $defaultCode      = config('language.default_code', 'vi');

    // Resolve seo từ $item (entity model có ->seo) hoặc nếu $item chính là Seo
    $__seoModel    = null;
    $__entityModel = null;
    if (isset($item) && $item) {
        if ($item instanceof Seo) {
            $__seoModel = $item;
        } elseif (is_object($item) && isset($item->seo)) {
            $__seoModel    = $item->seo instanceof Seo ? $item->seo : null;
            $__entityModel = $item;
        }
    }

    // Auto-load translations nếu chưa có
    if (!isset($translationsSeo) || !isset($translationsEnt)) {
        $__loaded = EntityTranslationService::loadAllTranslations($__seoModel, $__entityModel);
        $translationsSeo = $translationsSeo ?? $__loaded['seo']    ?? [];
        $translationsEnt = $translationsEnt ?? $__loaded['entity'] ?? [];
    }

    // Auto-detect translatable fields từ entity model hoặc config('tablemysql')
    if (!isset($fields)) {
        $fields = [];
        if ($__entityModel && property_exists($__entityModel, 'translatableFields')
            && is_array($__entityModel->translatableFields)) {
            $fields = $__entityModel->translatableFields;
        } elseif ($__seoModel && !empty($__seoModel->type)) {
            $fields = config('tablemysql.' . $__seoModel->type . '.translatable', []);
        }
    }

    $fieldLabels      = $fieldLabels      ?? [
        'name'         => 'Tên hiển thị',
        'display_name' => 'Tên hiển thị (alt)',
        'description'  => 'Mô tả',
        'note'         => 'Ghi chú',
        'address'      => 'Địa chỉ',
        'pick_up'      => 'Điểm đón',
        'transport'    => 'Phương tiện',
        'content'      => 'Nội dung',
    ];
    $longTextFields   = $longTextFields   ?? [
        'description', 'content', 'note', 'special_content', 'special_list',
        'include', 'not_include', 'policy_child', 'policy_cancel', 'menu', 'hotel',
    ];
    $showSeoFields    = $showSeoFields    ?? true;
    $showEntityFields = $showEntityFields ?? !empty($fields);

    $__hasAnything    = $showSeoFields || $showEntityFields;
@endphp

@if($__hasAnything && $languages && count($languages) > 1)
<div class="formMultilingualTabs" id="{{ $tabId }}">
    <ul class="nav nav-tabs" role="tablist">
        @foreach($languages as $i => $lang)
            @php $isActive = $lang->code === $defaultCode; @endphp
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $isActive ? 'active' : '' }}"
                   id="{{ $tabId }}-tab-{{ $lang->code }}"
                   data-bs-toggle="tab" data-toggle="tab"
                   href="#{{ $tabId }}-{{ $lang->code }}"
                   role="tab"
                   aria-controls="{{ $tabId }}-{{ $lang->code }}"
                   aria-selected="{{ $isActive ? 'true' : 'false' }}">
                    @if(!empty($lang->flag))
                        <img src="{{ $lang->flag }}" alt="{{ $lang->name_native }}" style="width:18px;height:auto;vertical-align:middle;margin-right:6px;" onerror="this.style.display='none'" />
                    @endif
                    {{ $lang->name_native ?: $lang->name }}
                    @if($lang->is_default) <span class="badge bg-secondary" style="font-size:.7em;">default</span> @endif
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content pt-3 border border-top-0 p-3" style="background:#fafafa;border-radius:0 0 .25rem .25rem;">
        @foreach($languages as $lang)
            @php
                $code         = $lang->code;
                $rowSeo       = $translationsSeo[$code] ?? null;
                $rowEnt       = $translationsEnt[$code] ?? null;
                $statusEntity = optional($rowEnt)->status ?? ($lang->is_default ? 'published' : 'draft');
                $statusSeo    = optional($rowSeo)->status ?? ($lang->is_default ? 'published' : 'draft');
                $isDefault    = $code === $defaultCode;
            @endphp

            <div class="tab-pane fade {{ $isDefault ? 'show active' : '' }}"
                 id="{{ $tabId }}-{{ $code }}" role="tabpanel"
                 aria-labelledby="{{ $tabId }}-tab-{{ $code }}">

                @if($isDefault)
                    <div class="alert alert-info" style="font-size:.875rem;">
                        Tab <strong>{{ $lang->name_native }}</strong> là ngôn ngữ mặc định —
                        các trường SEO/Tên ở form chính sẽ được copy sang đây tự động khi lưu.
                        Chỉ cần điền các tab ngôn ngữ KHÁC.
                    </div>
                @endif

                @if($showSeoFields)
                    <fieldset class="mb-3">
                        <legend class="h6 text-uppercase">SEO ({{ $lang->name_native }})</legend>

                        <div class="form-group mb-2">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control"
                                   name="translations[{{ $code }}][seo][title]"
                                   value="{{ old('translations.' . $code . '.seo.title', optional($rowSeo)->title) }}"
                                   placeholder="{{ $isDefault ? 'Để trống = lấy theo trường Tiêu đề SEO ở form chính' : 'Tiêu đề ' . $lang->name_native }}" />
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="2"
                                      name="translations[{{ $code }}][seo][description]">{{ old('translations.' . $code . '.seo.description', optional($rowSeo)->description) }}</textarea>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-6 mb-2">
                                <label class="form-label">SEO Title (override &lt;title&gt;)</label>
                                <input type="text" class="form-control"
                                       name="translations[{{ $code }}][seo][seo_title]"
                                       value="{{ old('translations.' . $code . '.seo.seo_title', optional($rowSeo)->seo_title) }}" />
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <label class="form-label">Slug ({{ $lang->name_native }})</label>
                                <input type="text" class="form-control"
                                       name="translations[{{ $code }}][seo][slug]"
                                       value="{{ old('translations.' . $code . '.seo.slug', optional($rowSeo)->slug) }}"
                                       placeholder="{{ $isDefault ? 'Để trống = lấy slug ở form chính' : 'Để trống = giữ nguyên slug ' . $defaultCode }}" />
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label">SEO Description</label>
                            <textarea class="form-control" rows="2"
                                      name="translations[{{ $code }}][seo][seo_description]">{{ old('translations.' . $code . '.seo.seo_description', optional($rowSeo)->seo_description) }}</textarea>
                        </div>

                        <div class="form-row row">
                            <div class="form-group col-md-8 mb-2">
                                <label class="form-label">Link canonical (optional)</label>
                                <input type="text" class="form-control"
                                       name="translations[{{ $code }}][seo][link_canonical]"
                                       value="{{ old('translations.' . $code . '.seo.link_canonical', optional($rowSeo)->link_canonical) }}" />
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <label class="form-label">Trạng thái SEO</label>
                                <select class="form-control" name="translations[{{ $code }}][seo][status]">
                                    @foreach(['published' => 'Published', 'draft' => 'Draft', 'machine' => 'Machine-translated'] as $key => $lbl)
                                        <option value="{{ $key }}" {{ $statusSeo === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>
                @endif

                @if($showEntityFields && count($fields) > 0)
                    <fieldset class="mb-3">
                        <legend class="h6 text-uppercase">Nội dung ({{ $lang->name_native }})</legend>

                        @foreach($fields as $field)
                            @php
                                $label    = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                $isLong   = in_array($field, $longTextFields, true);
                                $current  = old('translations.' . $code . '.entity.' . $field, optional($rowEnt)->{$field});
                            @endphp
                            <div class="form-group mb-2">
                                <label class="form-label">{{ $label }}</label>
                                @if($isLong)
                                    <textarea class="form-control" rows="3"
                                              name="translations[{{ $code }}][entity][{{ $field }}]">{{ $current }}</textarea>
                                @else
                                    <input type="text" class="form-control"
                                           name="translations[{{ $code }}][entity][{{ $field }}]"
                                           value="{{ $current }}"
                                           placeholder="{{ $isDefault ? ('Để trống = copy từ form chính (' . $field . ')') : ($label . ' ' . $lang->name_native) }}" />
                                @endif
                            </div>
                        @endforeach

                        <div class="form-group mb-2">
                            <label class="form-label">Trạng thái nội dung</label>
                            <select class="form-control" name="translations[{{ $code }}][entity][status]">
                                @foreach(['published' => 'Published', 'draft' => 'Draft', 'machine' => 'Machine-translated'] as $key => $lbl)
                                    <option value="{{ $key }}" {{ $statusEntity === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
