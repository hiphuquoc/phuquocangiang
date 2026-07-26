@extends('admin.layouts.main')
@section('content')
@php
    $config = $config ?? null;
    $items = $config?->items ?? collect();
@endphp

@push('styles-custom')
<link rel="stylesheet" href="{{ asset('css/admin/home-faq-admin.css') }}?v={{ @filemtime(public_path('css/admin/home-faq-admin.css')) ?: time() }}">
@include('admin.homeFaq.styles')
@endpush

<div class="pageAdminWithRightSidebar withRightSidebar hfaq-admin">
    <div class="pageAdminWithRightSidebar_header">
        Câu hỏi thường gặp — FAQ trang chủ
        <span class="badge bg-light-primary ms-1">{{ strtoupper($locale) }}</span>
        <span class="badge bg-light-info ms-1">{{ $islandName }}</span>
    </div>

    @if ($errors->any())
        <ul class="errorList">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @include('admin.template.messageAction')

    <form id="homeFaqForm" class="needs-validation invalid" action="{{ route('admin.homeFaq.update') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">

        <div class="pageAdminWithRightSidebar_main">
            <div class="pageAdminWithRightSidebar_main_content">
                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card hfq-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Tiêu đề section</h4>
                        </div>
                        <div class="card-body">
                            <p class="hfq-hint">Dùng <code>:name</code> trong mô tả để tự thay bằng tên đảo (<strong>{{ $islandName }}</strong>).</p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nhãn nhỏ (kicker)</label>
                                    <input type="text" class="form-control" name="kicker" value="{{ old('kicker', $config?->kicker ?? 'Hỏi đáp') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tiêu đề chính</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $config?->title ?? 'Câu hỏi thường gặp') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="2">{{ old('description', $config?->description ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="faqActive" @checked(old('is_active', $config?->is_active ?? true))>
                                        <label class="form-check-label" for="faqActive">Hiển thị section trên trang chủ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card hfq-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Khối hỗ trợ (sidebar)</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Tiêu đề</label>
                                    <input type="text" class="form-control" name="help_title" value="{{ old('help_title', $config?->help_title ?? 'Cần tư vấn thêm?') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nội dung (HTML)</label>
                                    <p class="hfq-hint mb-50">Ví dụ: <code>Gọi hotline &lt;a href="tel:19001234"&gt;1900 1234&lt;/a&gt; — hỗ trợ 7:00–22:00.</code></p>
                                    <textarea class="form-control" name="help_body" rows="3">{{ old('help_body', $config?->help_body ?? 'Gọi hotline <a href="tel:19001234">1900 1234</a> — hỗ trợ 7:00–22:00 hàng ngày.') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item width100">
                    <div class="card hfq-card">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-1">
                            <div>
                                <h4 class="card-title mb-0">Danh sách câu hỏi</h4>
                                <p class="hfq-hint mb-0 mt-25">Câu trả lời nhận HTML — có thể dùng <code>&lt;a&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;ul&gt;</code>…</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="hfqAddItemRow">
                                <i data-feather="plus"></i> Thêm câu hỏi
                            </button>
                        </div>
                        <div class="card-body">
                            @if($items->isNotEmpty())
                                <div class="hfq-item-grid" id="hfqExistingItems">
                                    @foreach($items as $item)
                                        <article class="hfq-item-card" data-faq-id="{{ $item->id }}">
                                            <input type="hidden" name="faqs_existing[]" value="{{ $item->id }}">
                                            <header class="hfq-item-card__head">
                                                <div class="hfq-item-card__head-left">
                                                    <span class="hfq-item-card__badge">#{{ $item->sort_order }}</span>
                                                </div>
                                                <div class="hfq-item-card__head-actions">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="faqs_open[]" value="{{ $item->id }}" id="faqOpen{{ $item->id }}" @checked($item->is_open_default)>
                                                        <label class="form-check-label" for="faqOpen{{ $item->id }}">Mở sẵn khi load trang</label>
                                                    </div>
                                                </div>
                                            </header>
                                            <div class="hfq-item-card__body">
                                                <div class="hfq-field-group">
                                                    <label class="form-label" for="faqQ{{ $item->id }}">Câu hỏi <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="faqQ{{ $item->id }}" name="faqs_question[{{ $item->id }}]" required value="{{ $item->question }}" placeholder="Nhập câu hỏi hiển thị trên trang chủ">
                                                </div>
                                                <div class="hfq-field-group hfq-field-group--answer">
                                                    <label class="form-label" for="faqA{{ $item->id }}">Câu trả lời <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="faqA{{ $item->id }}" name="faqs_answer[{{ $item->id }}]" rows="6" required placeholder="<p>Nội dung trả lời...</p>">{{ $item->answer_html }}</textarea>
                                                </div>
                                                <div class="hfq-field-group hfq-field-group--meta">
                                                    <div class="row g-2 align-items-end">
                                                        <div class="col-sm-3 col-md-2">
                                                            <label class="form-label" for="faqSort{{ $item->id }}">Thứ tự</label>
                                                            <input type="number" min="0" class="form-control" id="faqSort{{ $item->id }}" name="faqs_sort[{{ $item->id }}]" value="{{ $item->sort_order }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <footer class="hfq-item-card__footer">
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-faq="{{ $item->id }}">Xóa câu hỏi</button>
                                            </footer>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="hfq-empty" id="hfqEmptyState">
                                    <p>Chưa có câu hỏi nào. Bấm <strong>Thêm câu hỏi</strong> để tạo.</p>
                                </div>
                            @endif

                            <div id="hfqNewItems" class="hfq-item-grid hfq-item-grid--new"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pageAdminWithRightSidebar_submit">
            <button type="submit" class="btn btn-primary">Lưu FAQ</button>
        </div>
    </form>
</div>

@push('scripts-custom')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('homeFaqForm');
    const newWrap = document.getElementById('hfqNewItems');
    const emptyState = document.getElementById('hfqEmptyState');
    let newIndex = 0;

    document.getElementById('hfqAddItemRow')?.addEventListener('click', function () {
        if (emptyState) emptyState.style.display = 'none';
        const i = newIndex++;
        const html = `
            <article class="hfq-item-card hfq-item-card--new">
                <header class="hfq-item-card__head">
                    <div class="hfq-item-card__head-left">
                        <span class="hfq-item-card__badge">Mới</span>
                    </div>
                    <div class="hfq-item-card__head-actions">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="faqs_new_open[]" value="${i}" id="faqNewOpen${i}">
                            <label class="form-check-label" for="faqNewOpen${i}">Mở sẵn khi load trang</label>
                        </div>
                    </div>
                </header>
                <div class="hfq-item-card__body">
                    <div class="hfq-field-group">
                        <label class="form-label" for="faqNewQ${i}">Câu hỏi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="faqNewQ${i}" name="faqs_new_question[${i}]" required placeholder="Cần đặt vé trước bao lâu?">
                    </div>
                    <div class="hfq-field-group hfq-field-group--answer">
                        <label class="form-label" for="faqNewA${i}">Câu trả lời <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="faqNewA${i}" name="faqs_new_answer[${i}]" rows="6" required placeholder="<p>Nội dung trả lời...</p>"></textarea>
                    </div>
                </div>
                <footer class="hfq-item-card__footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary hfq-remove-new">Bỏ câu hỏi này</button>
                </footer>
            </article>`;
        newWrap.insertAdjacentHTML('beforeend', html);
        if (typeof feather !== 'undefined') feather.replace();
    });

    newWrap?.addEventListener('click', function (e) {
        if (e.target.closest('.hfq-remove-new')) {
            e.target.closest('.hfq-item-card')?.remove();
        }
    });

    document.querySelectorAll('[data-delete-faq]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Xóa câu hỏi này?')) return;
            const id = btn.getAttribute('data-delete-faq');
            const resp = await fetch('{{ route('admin.homeFaq.deleteItem') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ id }),
            });
            const data = await resp.json();
            if (data.success) {
                btn.closest('.hfq-item-card')?.remove();
            } else {
                alert(data.message || 'Không xóa được câu hỏi.');
            }
        });
    });

    form?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        const resp = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });
        const data = await resp.json();
        if (data.success) {
            window.location.href = data.redirect_url || window.location.href;
        } else {
            alert(data.message || 'Lưu thất bại.');
        }
    });
});
</script>
@endpush
@endsection
