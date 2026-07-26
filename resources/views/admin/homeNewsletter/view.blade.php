@extends('admin.layouts.main')
@section('content')
@php
    $config = $config ?? null;
    $year = (string) date('Y');
@endphp

<div class="pageAdminWithRightSidebar withRightSidebar">
    <div class="pageAdminWithRightSidebar_header">
        Newsletter — Thư đăng ký trang chủ
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

    <form id="homeNewsletterForm" class="needs-validation invalid" action="{{ route('admin.homeNewsletter.update') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">

        <div class="pageAdminWithRightSidebar_main">
            <div class="pageAdminWithRightSidebar_main_content">
                <div class="pageAdminWithRightSidebar_main_content_item width100">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Nội dung lá thư</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-1">Dùng <code>:name</code> trong mô tả để tự thay bằng tên đảo (<strong>{{ $islandName }}</strong>).</p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nhãn nhỏ (kicker)</label>
                                    <input type="text" class="form-control" name="kicker" value="{{ old('kicker', $config?->kicker ?? 'Thư từ Superdong') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tiêu đề chính</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $config?->title ?? 'Gửi bạn ưu đãi vé tàu & combo mới nhất') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mô tả ngắn</label>
                                    <textarea class="form-control" name="lead" rows="2">{{ old('lead', $config?->lead ?? '') }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tem — chữ (vd: SD)</label>
                                    <input type="text" class="form-control" name="stamp_text" value="{{ old('stamp_text', $config?->stamp_text ?? 'SD') }}" maxlength="16">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tem — năm</label>
                                    <input type="text" class="form-control" name="stamp_year" value="{{ old('stamp_year', $config?->stamp_year ?? $year) }}" maxlength="8">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="newsletterActive" @checked(old('is_active', $config?->is_active ?? true))>
                                        <label class="form-check-label" for="newsletterActive">Hiển thị section trên trang chủ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item width100">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Form đăng ký</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nhãn ô email</label>
                                    <input type="text" class="form-control" name="field_label" value="{{ old('field_label', $config?->field_label ?? 'Kính gửi') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Placeholder email</label>
                                    <input type="text" class="form-control" name="email_placeholder" value="{{ old('email_placeholder', $config?->email_placeholder ?? 'email@ban.com') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nút gửi</label>
                                    <input type="text" class="form-control" name="submit_text" value="{{ old('submit_text', $config?->submit_text ?? 'Gửi thư đăng ký') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Ghi chú quyền riêng tư</label>
                                    <textarea class="form-control" name="note" rows="2">{{ old('note', $config?->note ?? 'Bạn có thể hủy đăng ký bất cứ lúc nào. Chúng tôi tôn trọng quyền riêng tư của bạn.') }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Chữ ký cuối thư</label>
                                    <input type="text" class="form-control" name="sign_text" value="{{ old('sign_text', $config?->sign_text ?? 'Trân trọng!') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pageAdminWithRightSidebar_submit">
            <button type="submit" class="btn btn-primary">Lưu Newsletter</button>
        </div>
    </form>
</div>

@push('scripts-custom')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('homeNewsletterForm');
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
