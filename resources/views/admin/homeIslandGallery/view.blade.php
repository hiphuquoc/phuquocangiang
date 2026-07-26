@extends('admin.layouts.main')
@section('content')
@php
    $config = $config ?? null;
    $photos = $config?->photos ?? collect();
@endphp

@include('admin.homeIslandGallery.styles')

<div class="pageAdminWithRightSidebar withRightSidebar hig-admin">
    <div class="pageAdminWithRightSidebar_header">
        Gallery Trải nghiệm đảo — ảnh đẹp trang chủ
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

    <form id="homeIslandGalleryForm" class="needs-validation invalid" action="{{ route('admin.homeIslandGallery.update') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">

        <div class="pageAdminWithRightSidebar_main">
            <div class="pageAdminWithRightSidebar_main_content">
                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card hig-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Tiêu đề section</h4>
                        </div>
                        <div class="card-body">
                            <p class="hig-hint">Dùng <code>:name</code> trong tiêu đề / caption để tự thay bằng tên đảo (<strong>{{ $islandName }}</strong>).</p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nhãn nhỏ (eyebrow)</label>
                                    <input type="text" class="form-control" name="eyebrow" value="{{ old('eyebrow', $config?->eyebrow ?? 'Trải nghiệm đảo') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tiêu đề chính</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $config?->title ?? ':name qua từng khoảnh khắc đẹp') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mô tả ngắn</label>
                                    <textarea class="form-control" name="lead" rows="2">{{ old('lead', $config?->lead ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Caption meta (dưới số lượng ảnh)</label>
                                    <input type="text" class="form-control" name="meta_caption" value="{{ old('meta_caption', $config?->meta_caption ?? 'Thư viện ảnh :name') }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="galleryActive" @checked(old('is_active', $config?->is_active ?? true))>
                                        <label class="form-check-label" for="galleryActive">Hiển thị section trên trang chủ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item width100">
                    <div class="card hig-card">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-1">
                            <div>
                                <h4 class="card-title mb-0">Thư viện ảnh</h4>
                                <p class="hig-hint mb-0 mt-25">Upload GCS — 3 bản gốc / -small / -medium. <strong>Alt bắt buộc</strong> (SEO &amp; accessibility).</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="higAddPhotoRow">
                                <i data-feather="plus"></i> Thêm ảnh
                            </button>
                        </div>
                        <div class="card-body">
                            @if($photos->isNotEmpty())
                                <div class="hig-photo-grid" id="higExistingPhotos">
                                    @foreach($photos as $photo)
                                        <article class="hig-photo-card" data-photo-id="{{ $photo->id }}">
                                            <input type="hidden" name="photos_existing[]" value="{{ $photo->id }}">
                                            <div class="hig-photo-card__preview">
                                                <img src="{{ $photo->thumbUrl() }}" alt="" loading="lazy">
                                                <span class="hig-photo-card__badge">#{{ $photo->sort_order }}</span>
                                            </div>
                                            <div class="hig-photo-card__fields">
                                                <label class="form-label">Alt <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" name="photos_alt[{{ $photo->id }}]" required value="{{ $photo->alt_text }}" placeholder="Mô tả ảnh cho SEO">
                                                <label class="form-label mt-50">Tiêu đề hiển thị (tuỳ chọn)</label>
                                                <input type="text" class="form-control form-control-sm" name="photos_title[{{ $photo->id }}]" value="{{ $photo->title }}" placeholder="Để trống = lấy từ alt">
                                                <div class="row g-1 mt-50">
                                                    <div class="col-6">
                                                        <label class="form-label">Tag</label>
                                                        <input type="text" class="form-control form-control-sm" name="photos_tag[{{ $photo->id }}]" value="{{ $photo->tag }}" placeholder="Biển đảo">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Thứ tự</label>
                                                        <input type="number" class="form-control form-control-sm" name="photos_sort[{{ $photo->id }}]" value="{{ $photo->sort_order }}" min="0">
                                                    </div>
                                                </div>
                                                <label class="form-label mt-50">Vị trí crop (CSS object-position)</label>
                                                <input type="text" class="form-control form-control-sm" name="photos_pos[{{ $photo->id }}]" value="{{ $photo->object_position }}" placeholder="center center">
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger hig-photo-card__delete" data-delete-photo="{{ $photo->id }}">Xóa</button>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="hig-empty" id="higEmptyState">
                                    <p>Chưa có ảnh nào. Bấm <strong>Thêm ảnh</strong> để upload.</p>
                                </div>
                            @endif

                            <div id="higNewPhotos" class="hig-photo-grid hig-photo-grid--new mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pageAdminWithRightSidebar_submit">
            <button type="submit" class="btn btn-primary">Lưu gallery</button>
        </div>
    </form>
</div>

@push('scripts-custom')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('homeIslandGalleryForm');
    const newWrap = document.getElementById('higNewPhotos');
    const emptyState = document.getElementById('higEmptyState');
    let newIndex = 0;

    document.getElementById('higAddPhotoRow')?.addEventListener('click', function () {
        if (emptyState) emptyState.style.display = 'none';
        const i = newIndex++;
        const html = `
            <article class="hig-photo-card hig-photo-card--new">
                <div class="hig-photo-card__preview hig-photo-card__preview--placeholder">
                    <span>Chọn ảnh</span>
                </div>
                <div class="hig-photo-card__fields">
                    <label class="form-label">File ảnh</label>
                    <input type="file" accept="image/*" name="photos_new[]" class="form-control form-control-sm mb-50" required>
                    <label class="form-label">Alt <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="photos_new_alt[${i}]" required placeholder="Mô tả ảnh">
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary hig-photo-card__remove-new">Bỏ</button>
            </article>`;
        newWrap.insertAdjacentHTML('beforeend', html);
        if (typeof feather !== 'undefined') feather.replace();
    });

    newWrap?.addEventListener('click', function (e) {
        if (e.target.closest('.hig-photo-card__remove-new')) {
            e.target.closest('.hig-photo-card')?.remove();
        }
    });

    document.querySelectorAll('[data-delete-photo]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Xóa ảnh này khỏi gallery?')) return;
            const id = btn.getAttribute('data-delete-photo');
            const resp = await fetch('{{ route('admin.homeIslandGallery.deletePhoto') }}', {
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
                btn.closest('.hig-photo-card')?.remove();
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
