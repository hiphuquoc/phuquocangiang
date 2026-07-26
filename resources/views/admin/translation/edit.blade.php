@extends('admin.layouts.main')
@section('content')
    @php
        $isLong = function($field) {
            return in_array($field, ['description','content','answer','seo_description','special_content','special_list','include','not_include','policy_child','menu','hotel','policy_cancel','note','content_sort'], true);
        };
        $isHtml = function($field) {
            return in_array($field, ['content','answer','description','seo_description','special_content','special_list','include','not_include','policy_child','menu','hotel','policy_cancel','note','content_sort'], true);
        };
        $backUrl = \App\Http\Controllers\AdminTranslationController::backUrlForType($entityType, $entity->id);
    @endphp

    <form id="formAction" class="needs-validation invalid" action="{{ route('admin.translation.save', ['locale' => $locale, 'seoId' => $seo->id]) }}" method="POST" novalidate>
        @csrf
        <div class="pageAdminWithRightSidebar withRightSidebar">
            <div class="pageAdminWithRightSidebar_header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa-regular fa-globe me-1"></i>
                    Chỉnh sửa bản dịch:
                    <span class="badge bg-primary">{{ $language->name_native ?? $language->name }} ({{ strtoupper($locale) }})</span>
                    <small class="text-muted ms-2">cho “{{ $entityTrans->{$entityFields[0] ?? null} ?? $entityDefault->{$entityFields[0] ?? null} ?? $entity->name ?? '#'.$entity->id }}”</small>
                </div>
                <div>
                    <a href="{{ $backUrl }}" class="btn btn-light btn-sm">
                        <i class="fa-regular fa-arrow-left me-1"></i> Quay lại trang gốc
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Language switcher --}}
            <div class="alert alert-info">
                <strong>Ngôn ngữ khả dụng:</strong>
                @foreach ($allLanguages as $lng)
                    @if ($lng->code === $defaultCode)
                        <span class="badge bg-secondary me-1" title="Ngôn ngữ mặc định — sửa ở trang admin gốc">
                            {{ strtoupper($lng->code) }} (default)
                        </span>
                    @elseif ($lng->code === $locale)
                        <span class="badge bg-primary me-1">{{ strtoupper($lng->code) }} (đang sửa)</span>
                    @else
                        <a class="badge bg-light text-dark text-decoration-none me-1"
                           href="{{ route('admin.translation.edit', ['locale' => $lng->code, 'seoId' => $seo->id]) }}">
                            {{ strtoupper($lng->code) }}
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="pageAdminWithRightSidebar_body">
                <div class="pageAdminWithRightSidebar_body_left">

                    {{-- ========== 1) ENTITY FIELDS (name, pick_up, transport, ...) ========== --}}
                    @if (!empty($entityFields))
                        <div class="formBox">
                            <div class="formBox_header"><h5>Thông tin cơ bản</h5></div>
                            <div class="formBox_full">
                                @foreach ($entityFields as $field)
                                    <div class="formBox_full_item">
                                        <label class="form-label" for="entity_{{ $field }}">
                                            <i class="fa-regular fa-language me-1 text-primary"></i>
                                            {{ ucfirst(str_replace('_',' ', $field)) }}
                                        </label>
                                        @php $defaultVal = $entityDefault->{$field} ?? $entity->{$field} ?? ''; @endphp
                                        @if ($isLong($field))
                                            <textarea id="entity_{{ $field }}" name="entity[{{ $field }}]" rows="4"
                                                      class="form-control" placeholder="Bản gốc: {{ \Illuminate\Support\Str::limit(strip_tags($defaultVal), 120) }}">{{ old('entity.'.$field, optional($entityTrans)->{$field}) }}</textarea>
                                        @else
                                            <input type="text" id="entity_{{ $field }}" name="entity[{{ $field }}]"
                                                   class="form-control"
                                                   placeholder="Bản gốc: {{ \Illuminate\Support\Str::limit(strip_tags((string)$defaultVal), 120) }}"
                                                   value="{{ old('entity.'.$field, optional($entityTrans)->{$field}) }}">
                                        @endif
                                        @if (!empty($defaultVal))
                                            <small class="text-muted d-block mt-1">
                                                <i class="fa-regular fa-quote-left me-1"></i><em>{{ \Illuminate\Support\Str::limit(strip_tags((string)$defaultVal), 200) }}</em>
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ========== 2) BODY CONTENT (seo_content_translations) ========== --}}
                    <div class="formBox">
                        <div class="formBox_header"><h5>Nội dung trang (Body)</h5></div>
                        <div class="formBox_full">
                            <div class="formBox_full_item">
                                <label class="form-label" for="body_content">Nội dung HTML / Blade của trang</label>
                                <textarea id="body_content" name="body_content" rows="20" class="form-control ckeditor"
                                          placeholder="Bản dịch nội dung body cho ngôn ngữ {{ strtoupper($locale) }}.">{{ old('body_content', optional($bodyTrans)->content) }}</textarea>
                                @if ($bodyDefault && !empty($bodyDefault->content))
                                    <details class="mt-2">
                                        <summary class="text-primary" style="cursor:pointer;">
                                            <i class="fa-regular fa-eye me-1"></i> Xem bản gốc ({{ strtoupper($defaultCode) }}) — {{ strlen($bodyDefault->content) }} ký tự
                                        </summary>
                                        <pre class="bg-light p-2 mt-2 small" style="max-height:300px;overflow:auto;">{{ \Illuminate\Support\Str::limit($bodyDefault->content, 5000) }}</pre>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ========== 3) RELATIONS (timetables, options, FAQs, content sections) ========== --}}
                    @foreach ($relations as $key => $relData)
                        @php $meta = $relData['_meta']; @endphp
                        <div class="formBox">
                            <div class="formBox_header">
                                <h5>
                                    <i class="fa-regular fa-list me-1"></i>
                                    {{ $meta['label'] ?? $key }}
                                    <small class="text-muted">({{ count($relData['rows']) }} mục)</small>
                                </h5>
                            </div>
                            <div class="formBox_full">
                                @if (empty($relData['rows']))
                                    <div class="alert alert-warning mb-0">
                                        Chưa có dữ liệu “{{ $meta['label'] ?? $key }}” ở bản gốc — không có gì để dịch.
                                    </div>
                                @else
                                    @foreach ($relData['rows'] as $idx => $row)
                                        <div class="formBox_full_item border rounded p-3 mb-2">
                                            <div class="d-flex justify-content-between mb-2">
                                                <strong>#{{ $row['id'] }}</strong>
                                                <small class="text-muted">{{ $key }}.{{ $row['id'] }}</small>
                                            </div>
                                            <input type="hidden" name="relations[{{ $key }}][{{ $idx }}][id]" value="{{ $row['id'] }}">
                                            @foreach ($relData['fields'] as $field)
                                                <div class="mb-2">
                                                    <label class="form-label small text-uppercase">
                                                        <i class="fa-regular fa-language me-1 text-primary"></i>{{ str_replace('_',' ', $field) }}
                                                    </label>
                                                    @if ($isLong($field))
                                                        <textarea name="relations[{{ $key }}][{{ $idx }}][fields][{{ $field }}]"
                                                                  rows="3" class="form-control"
                                                                  placeholder="Bản gốc: {{ \Illuminate\Support\Str::limit(strip_tags((string)($row['default'][$field] ?? '')), 120) }}">{{ old('relations.'.$key.'.'.$idx.'.fields.'.$field, $row['locale'][$field] ?? '') }}</textarea>
                                                    @else
                                                        <input type="text" name="relations[{{ $key }}][{{ $idx }}][fields][{{ $field }}]"
                                                               class="form-control"
                                                               placeholder="Bản gốc: {{ \Illuminate\Support\Str::limit(strip_tags((string)($row['default'][$field] ?? '')), 120) }}"
                                                               value="{{ old('relations.'.$key.'.'.$idx.'.fields.'.$field, $row['locale'][$field] ?? '') }}">
                                                    @endif
                                                    @if (!empty($row['default'][$field]))
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fa-regular fa-quote-left me-1"></i>
                                                            <em>{{ \Illuminate\Support\Str::limit(strip_tags((string)$row['default'][$field]), 250) }}</em>
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pageAdminWithRightSidebar_body_right">
                    {{-- ========== 4) SEO TRANSLATION ========== --}}
                    <div class="formBox">
                        <div class="formBox_header"><h5><i class="fa-regular fa-search me-1"></i> SEO</h5></div>
                        <div class="formBox_full">
                            @php
                                $seoFields = [
                                    'title'           => 'Tiêu đề trang',
                                    'description'     => 'Mô tả ngắn',
                                    'seo_title'       => 'SEO Title (Meta)',
                                    'seo_description' => 'SEO Description (Meta)',
                                    'slug'            => 'Slug (URL chỉ riêng locale này)',
                                    'link_canonical'  => 'Canonical URL',
                                ];
                            @endphp
                            @foreach ($seoFields as $field => $label)
                                <div class="formBox_full_item">
                                    <label class="form-label" for="seo_{{ $field }}">{{ $label }}</label>
                                    @php $defaultVal = optional($seoDefault)->{$field}; @endphp
                                    @if ($isLong($field))
                                        <textarea id="seo_{{ $field }}" name="seo[{{ $field }}]" rows="3"
                                                  class="form-control"
                                                  placeholder="Bản gốc: {{ \Illuminate\Support\Str::limit($defaultVal, 100) }}">{{ old('seo.'.$field, optional($seoTrans)->{$field}) }}</textarea>
                                    @else
                                        <input type="text" id="seo_{{ $field }}" name="seo[{{ $field }}]"
                                               class="form-control"
                                               placeholder="Bản gốc: {{ \Illuminate\Support\Str::limit($defaultVal, 80) }}"
                                               value="{{ old('seo.'.$field, optional($seoTrans)->{$field}) }}">
                                    @endif
                                    @if (!empty($defaultVal))
                                        <small class="text-muted d-block mt-1">{{ \Illuminate\Support\Str::limit($defaultVal, 120) }}</small>
                                    @endif
                                </div>
                            @endforeach
                            <div class="formBox_full_item">
                                <label class="form-label" for="seo_status">Trạng thái</label>
                                <select id="seo_status" name="seo[status]" class="form-select">
                                    <option value="draft" {{ optional($seoTrans)->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ optional($seoTrans)->status === 'published' ? 'selected' : '' }}>Published</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ========== 5) ACTIONS ========== --}}
                    <div class="formBox sticky-top" style="top:1rem;">
                        <div class="formBox_full">
                            <div class="formBox_full_item">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa-regular fa-floppy-disk me-1"></i> Lưu bản dịch {{ strtoupper($locale) }}
                                </button>
                            </div>
                            <div class="formBox_full_item">
                                <a href="{{ route('admin.translation.delete', ['locale' => $locale, 'seoId' => $seo->id]) }}"
                                   class="btn btn-outline-danger w-100"
                                   onclick="return confirm('Xoá toàn bộ bản dịch {{ strtoupper($locale) }} của entity này?');">
                                    <i class="fa-regular fa-trash me-1"></i> Xoá bản dịch {{ strtoupper($locale) }}
                                </a>
                            </div>
                            <div class="formBox_full_item">
                                <a href="{{ $backUrl }}" class="btn btn-light w-100">
                                    <i class="fa-regular fa-arrow-left me-1"></i> Trang gốc
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
