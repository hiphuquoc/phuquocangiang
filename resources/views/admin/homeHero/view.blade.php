@extends('admin.layouts.main')
@section('content')
@php
    $config = $config ?? null;
    $routes = old('routes', $config?->routeSlots?->map(fn ($slot) => [
        'ship_location_id' => $slot->ship_location_id,
    ])->values()->all() ?? [[], []]);
    while (count($routes) < 2) { $routes[] = []; }
@endphp

<div class="pageAdminWithRightSidebar withRightSidebar">
    <div class="pageAdminWithRightSidebar_header">
        Hero trang chủ — nội dung &amp; ảnh nền
        <span class="badge bg-light-primary ms-1">{{ strtoupper($locale) }}</span>
    </div>

    @if ($errors->any())
        <ul class="errorList">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @include('admin.template.messageAction')

    <form id="homeHeroForm" class="needs-validation invalid" action="{{ route('admin.homeHero.update') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">

        <div class="pageAdminWithRightSidebar_main">
            <div class="pageAdminWithRightSidebar_main_content">
                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title">Nội dung Hero</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-1">
                                    <label class="form-label">Tiêu đề chính</label>
                                    <input type="text" class="form-control" name="title" required value="{{ old('title', $config?->title ?? 'Khám phá Côn Đảo') }}">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <label class="form-label">Tiêu đề highlight (gradient)</label>
                                    <input type="text" class="form-control" name="title_accent" value="{{ old('title_accent', $config?->title_accent ?? 'đặt trọn hành trình') }}">
                                </div>
                                <div class="col-12 mb-1">
                                    <label class="form-label">Mô tả ngắn</label>
                                    <textarea class="form-control" name="tagline" rows="3">{{ old('tagline', $config?->tagline ?? '') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="heroActive" @checked(old('is_active', $config?->is_active ?? true))>
                                        <label class="form-check-label" for="heroActive">Kích hoạt Hero cho locale này</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Ảnh nền slider (Google Cloud)</h4>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addBackgroundInput">+ Thêm ảnh</button>
                        </div>
                        <div class="card-body">
                            @if(!empty($config) && $config->backgrounds->isNotEmpty())
                                <div class="row g-2 mb-2" id="existingBackgrounds">
                                    @foreach($config->backgrounds as $bg)
                                        <div class="col-md-4">
                                            <div class="border rounded p-1">
                                                <input type="hidden" name="backgrounds_existing[]" value="{{ $bg->id }}">
                                                <img src="{{ $bg->mediaUrl() }}" alt="" class="img-fluid rounded mb-1" style="max-height:140px;object-fit:cover;width:100%;">
                                                <input type="text" class="form-control form-control-sm mb-1" name="backgrounds_alt[{{ $bg->id }}]" placeholder="Alt text" value="{{ $bg->alt_text }}">
                                                <input type="number" class="form-control form-control-sm mb-1" name="backgrounds_sort[{{ $bg->id }}]" placeholder="Thứ tự" value="{{ $bg->sort_order }}">
                                                <button type="button" class="btn btn-sm btn-outline-danger d-block mt-1 w-100" data-delete-bg="{{ $bg->id }}">Xóa</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div id="newBackgroundInputs"></div>
                            <p class="text-muted small mb-0">Ảnh upload lên GCS bucket cấu hình sẵn. Khuyến nghị 1920×1080+, JPG/WebP.</p>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item width100">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title">Thẻ tuyến tàu (Ship Location)</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Chọn Ship Location — hệ thống tự load tối đa 2 chuyến có <strong>điểm xuất phát khác nhau</strong> (giá thấp nhất mỗi điểm). Có thể chọn cùng location ở cả 2 ô hoặc chỉ điền ô đầu.</p>
                            <div class="row g-3">
                                @foreach([0, 1] as $i)
                                    @php $slot = $routes[$i] ?? []; @endphp
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 h-100">
                                            <h6 class="mb-1">Tuyến #{{ $i + 1 }}</h6>
                                            <div class="mb-0">
                                                <label class="form-label">Ship Location</label>
                                                <select class="form-select" name="routes[{{ $i }}][ship_location_id]">
                                                    <option value="">— Chọn điểm đến —</option>
                                                    @foreach($shipLocations as $loc)
                                                        <option value="{{ $loc->id }}" @selected((int)($slot['ship_location_id'] ?? 0) === (int)$loc->id)>
                                                            {{ $loc->display_name ?: $loc->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card">
                        <div class="card-header border-bottom"><h4 class="card-title">Hai nút hành động</h4></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="btn_primary_enabled" value="1" id="btnPrimary" @checked(old('btn_primary_enabled', $config?->btn_primary_enabled ?? true))>
                                        <label class="form-check-label" for="btnPrimary">Bật nút chính</label>
                                    </div>
                                    <input type="text" class="form-control mb-1" name="btn_primary_label" placeholder="Nhãn nút" value="{{ old('btn_primary_label', $config?->btn_primary_label ?? 'Đặt vé tàu') }}">
                                    <input type="text" class="form-control" name="btn_primary_url" placeholder="URL (#booking)" value="{{ old('btn_primary_url', $config?->btn_primary_url ?? '#booking') }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="btn_secondary_enabled" value="1" id="btnSecondary" @checked(old('btn_secondary_enabled', $config?->btn_secondary_enabled ?? true))>
                                        <label class="form-check-label" for="btnSecondary">Bật nút phụ</label>
                                    </div>
                                    <input type="text" class="form-control mb-1" name="btn_secondary_label" placeholder="Nhãn nút" value="{{ old('btn_secondary_label', $config?->btn_secondary_label ?? '1900 545 487') }}">
                                    <input type="text" class="form-control" name="btn_secondary_url" placeholder="URL (tel:...)" value="{{ old('btn_secondary_url', $config?->btn_secondary_url ?? 'tel:1900545487') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pageAdminWithRightSidebar_submit">
            <button type="submit" class="btn btn-primary">Lưu Hero</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('homeHeroForm');
    const newBgWrap = document.getElementById('newBackgroundInputs');
    let newBgIndex = 0;

    document.getElementById('addBackgroundInput')?.addEventListener('click', function () {
        const i = newBgIndex++;
        const html = `<div class="border rounded p-2 mb-2"><input type="file" accept="image/*" name="backgrounds_new[]" class="form-control mb-1"><input type="text" class="form-control form-control-sm" name="backgrounds_new_alt[${i}]" placeholder="Alt text"></div>`;
        newBgWrap.insertAdjacentHTML('beforeend', html);
    });

    document.querySelectorAll('[data-delete-bg]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Xóa ảnh nền này?')) return;
            const id = btn.getAttribute('data-delete-bg');
            const resp = await fetch('{{ route('admin.homeHero.deleteBackground') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await resp.json();
            if (data.success) {
                btn.closest('.col-md-4')?.remove();
            } else {
                alert(data.message || 'Không xóa được ảnh.');
            }
        });
    });

    form?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        const resp = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const data = await resp.json();
        if (data.success) {
            window.location.href = data.redirect_url || window.location.href;
            return;
        }
        alert(data.message || 'Lưu thất bại.');
    });
});
</script>
@endsection
