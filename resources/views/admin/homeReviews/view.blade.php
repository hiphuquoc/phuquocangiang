@extends('admin.layouts.main')
@section('content')
@php
    $config = $config ?? null;
    $items = $config?->items ?? collect();
    $scoreStats = $config?->score_stats ?? [
        ['value' => '12K+', 'label' => 'đánh giá'],
        ['value' => '98%', 'label' => 'quay lại'],
        ['value' => '5★', 'label' => 'trung bình'],
    ];
    $partnersText = old('partners_text', implode("\n", (array) ($config?->partners ?? [])));
@endphp

@include('admin.homeReviews.styles')

<div class="pageAdminWithRightSidebar withRightSidebar hrv-admin">
    <div class="pageAdminWithRightSidebar_header">
        Khách hàng nói gì — đánh giá trang chủ
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

    <form id="homeReviewsForm" class="needs-validation invalid" action="{{ route('admin.homeReviews.update') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">

        <div class="pageAdminWithRightSidebar_main">
            <div class="pageAdminWithRightSidebar_main_content">
                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card hrv-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Tiêu đề section</h4>
                        </div>
                        <div class="card-body">
                            <p class="hrv-hint">Dùng <code>:name</code> trong mô tả để tự thay bằng tên đảo (<strong>{{ $islandName }}</strong>).</p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nhãn nhỏ (kicker)</label>
                                    <input type="text" class="form-control" name="kicker" value="{{ old('kicker', $config?->kicker ?? 'Khách hàng nói gì') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Tiêu đề chính</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $config?->title ?? 'Hành trình được tin chọn') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="2">{{ old('description', $config?->description ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="reviewsActive" @checked(old('is_active', $config?->is_active ?? true))>
                                        <label class="form-check-label" for="reviewsActive">Hiển thị section trên trang chủ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card hrv-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Điểm đánh giá</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label">Điểm tổng (0–5)</label>
                                    <input type="number" step="0.1" min="0" max="5" class="form-control" name="score_value" value="{{ old('score_value', $config?->score_value ?? 4.9) }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">3 chỉ số dưới vòng tròn</label>
                                    <div class="hrv-stat-grid">
                                        @for($i = 0; $i < 3; $i++)
                                            <div class="hrv-stat-row">
                                                <input type="text" class="form-control form-control-sm" name="score_stat_value[{{ $i }}]" value="{{ old('score_stat_value.'.$i, $scoreStats[$i]['value'] ?? '') }}" placeholder="Giá trị">
                                                <input type="text" class="form-control form-control-sm" name="score_stat_label[{{ $i }}]" value="{{ old('score_stat_label.'.$i, $scoreStats[$i]['label'] ?? '') }}" placeholder="Nhãn">
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item">
                    <div class="card hrv-card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title mb-0">Đối tác tin cậy</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nhãn rail</label>
                                    <input type="text" class="form-control" name="partners_label" value="{{ old('partners_label', $config?->partners_label ?? 'Đối tác tin cậy') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Danh sách đối tác (mỗi dòng một tên)</label>
                                    <textarea class="form-control" name="partners_text" rows="4" placeholder="Superdong&#10;Phú Quốc Express">{{ $partnersText }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pageAdminWithRightSidebar_main_content_item width100">
                    <div class="card hrv-card">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-1">
                            <div>
                                <h4 class="card-title mb-0">Bình luận khách hàng</h4>
                                <p class="hrv-hint mb-0 mt-25">Trang chủ hiển thị 3 bình luận / hàng (desktop), có nút chuyển trang để xem thêm.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="hrvAddReviewRow">
                                <i data-feather="plus"></i> Thêm bình luận
                            </button>
                        </div>
                        <div class="card-body">
                            @if($items->isNotEmpty())
                                <div class="hrv-review-grid" id="hrvExistingReviews">
                                    @foreach($items as $item)
                                        <article class="hrv-review-card" data-review-id="{{ $item->id }}">
                                            <input type="hidden" name="reviews_existing[]" value="{{ $item->id }}">
                                            <div class="hrv-review-card__head">
                                                <div class="hrv-review-card__avatar">
                                                    @if($item->avatarUrl())
                                                        <img src="{{ $item->avatarUrl() }}" alt="" loading="lazy">
                                                    @else
                                                        <span class="hrv-review-card__avatar--placeholder">Chưa có ảnh</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong>{{ $item->customer_name }}</strong>
                                                    <div class="text-muted small">#{{ $item->sort_order }}</div>
                                                </div>
                                            </div>
                                            <div class="hrv-review-card__fields">
                                                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm" name="reviews_quote[{{ $item->id }}]" rows="3" required>{{ $item->quote_text }}</textarea>
                                                <div class="row g-1 mt-50">
                                                    <div class="col-6">
                                                        <label class="form-label">Tên khách <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm" name="reviews_name[{{ $item->id }}]" required value="{{ $item->customer_name }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Địa điểm / meta</label>
                                                        <input type="text" class="form-control form-control-sm" name="reviews_meta[{{ $item->id }}]" value="{{ $item->customer_meta }}" placeholder="Hà Nội">
                                                    </div>
                                                </div>
                                                <div class="row g-1 mt-50">
                                                    <div class="col-4">
                                                        <label class="form-label">Tag</label>
                                                        <input type="text" class="form-control form-control-sm" name="reviews_tag[{{ $item->id }}]" value="{{ $item->tag }}" placeholder="Combo 3N2Đ">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label">Sao (1–5)</label>
                                                        <input type="number" min="1" max="5" class="form-control form-control-sm" name="reviews_rating[{{ $item->id }}]" value="{{ $item->rating }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label">Thứ tự</label>
                                                        <input type="number" min="0" class="form-control form-control-sm" name="reviews_sort[{{ $item->id }}]" value="{{ $item->sort_order }}">
                                                    </div>
                                                </div>
                                                <label class="form-label mt-50">Avatar URL (tuỳ chọn)</label>
                                                <input type="url" class="form-control form-control-sm" name="reviews_avatar_url[{{ $item->id }}]" value="{{ str_starts_with((string) $item->avatar_path, 'http') ? $item->avatar_path : '' }}" placeholder="https://...">
                                                <label class="form-label mt-50">Hoặc upload avatar mới</label>
                                                <input type="file" accept="image/*" name="reviews_avatar[{{ $item->id }}]" class="form-control form-control-sm">
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger hrv-review-card__delete" data-delete-review="{{ $item->id }}">Xóa</button>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="hrv-empty" id="hrvEmptyState">
                                    <p>Chưa có bình luận nào. Bấm <strong>Thêm bình luận</strong> để tạo.</p>
                                </div>
                            @endif

                            <div id="hrvNewReviews" class="hrv-review-grid hrv-review-grid--new mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pageAdminWithRightSidebar_submit">
            <button type="submit" class="btn btn-primary">Lưu đánh giá</button>
        </div>
    </form>
</div>

@push('scripts-custom')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('homeReviewsForm');
    const newWrap = document.getElementById('hrvNewReviews');
    const emptyState = document.getElementById('hrvEmptyState');
    let newIndex = 0;

    document.getElementById('hrvAddReviewRow')?.addEventListener('click', function () {
        if (emptyState) emptyState.style.display = 'none';
        const i = newIndex++;
        const html = `
            <article class="hrv-review-card hrv-review-card--new">
                <div class="hrv-review-card__fields">
                    <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" name="reviews_new_quote[${i}]" rows="3" required placeholder="Nội dung đánh giá"></textarea>
                    <div class="row g-1 mt-50">
                        <div class="col-6">
                            <label class="form-label">Tên khách <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="reviews_new_name[${i}]" required placeholder="Nguyễn Minh Anh">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Địa điểm / meta</label>
                            <input type="text" class="form-control form-control-sm" name="reviews_new_meta[${i}]" placeholder="Hà Nội">
                        </div>
                    </div>
                    <div class="row g-1 mt-50">
                        <div class="col-4">
                            <label class="form-label">Tag</label>
                            <input type="text" class="form-control form-control-sm" name="reviews_new_tag[${i}]" placeholder="Combo 3N2Đ">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Sao (1–5)</label>
                            <input type="number" min="1" max="5" class="form-control form-control-sm" name="reviews_new_rating[${i}]" value="5">
                        </div>
                    </div>
                    <label class="form-label mt-50">Avatar URL (tuỳ chọn)</label>
                    <input type="url" class="form-control form-control-sm" name="reviews_new_avatar_url[${i}]" placeholder="https://...">
                    <label class="form-label mt-50">Hoặc upload avatar</label>
                    <input type="file" accept="image/*" name="reviews_new_avatar[${i}]" class="form-control form-control-sm">
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary hrv-review-card__remove-new">Bỏ</button>
            </article>`;
        newWrap.insertAdjacentHTML('beforeend', html);
        if (typeof feather !== 'undefined') feather.replace();
    });

    newWrap?.addEventListener('click', function (e) {
        if (e.target.closest('.hrv-review-card__remove-new')) {
            e.target.closest('.hrv-review-card')?.remove();
        }
    });

    document.querySelectorAll('[data-delete-review]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!confirm('Xóa bình luận này?')) return;
            const id = btn.getAttribute('data-delete-review');
            const resp = await fetch('{{ route('admin.homeReviews.deleteItem') }}', {
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
                btn.closest('.hrv-review-card')?.remove();
            } else {
                alert(data.message || 'Không xóa được bình luận.');
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
