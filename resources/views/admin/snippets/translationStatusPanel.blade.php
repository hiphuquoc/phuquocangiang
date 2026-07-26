@php
    /**
     * Panel "Phiên bản dịch" — hiển thị trạng thái từng ngôn ngữ active của 1 entity (qua $seo)
     * và link tới trang dịch riêng (admin.translation.edit/{locale}/{seoId}).
     *
     * Required:
     *   - $seo  (App\Models\Seo)
     */
    use App\Models\Language;
    use App\Models\SeoTranslation;
    use App\Models\SeoContentTranslation;

    $defaultCode = config('language.default_code', 'vi');
    $languages   = Language::active();

    // Map language_id => row seo_translations
    $seoTransRows = SeoTranslation::where('seo_id', $seo->id)->get()->keyBy('language_id');
    $bodyRows     = SeoContentTranslation::where('seo_id', $seo->id)->get()->keyBy('language_id');
@endphp

<div class="formBox" style="margin-top:1rem;">
    <div class="formBox_full">
        <div class="formBox_full_item">
            <h5 class="mb-2" style="color:#5a5c69;">
                <i class="fa-regular fa-globe me-1"></i>
                Phiên bản dịch
                <small class="text-muted">— mỗi ngôn ngữ là 1 trang riêng đầy đủ nội dung</small>
            </h5>

            <div class="alert alert-info py-2 small mb-2">
                <i class="fa-regular fa-circle-info me-1"></i>
                Trang admin này chỉnh sửa <strong>nội dung mặc định ({{ strtoupper($defaultCode) }})</strong>
                và dữ liệu master (giá, ngày, ảnh, quan hệ). Để dịch sang ngôn ngữ khác, bấm <em>Dịch sang …</em> dưới đây — bạn sẽ được mở trang dịch riêng chứa đầy đủ <strong>SEO + nội dung + lịch trình + tùy chọn + FAQ</strong> cho ngôn ngữ đó.
            </div>

            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:32px;">#</th>
                        <th>Ngôn ngữ</th>
                        <th style="width:90px;">Mã</th>
                        <th>SEO</th>
                        <th>Body</th>
                        <th style="width:200px;text-align:right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($languages as $lng)
                        @php
                            $isDefault = $lng->code === $defaultCode;
                            $seoTr     = $seoTransRows[$lng->id] ?? null;
                            $bodyTr    = $bodyRows[$lng->id]    ?? null;

                            $statusSeo  = $seoTr ? ($seoTr->status ?: 'draft') : null;
                            $statusBody = $bodyTr ? ($bodyTr->status ?: 'draft') : null;

                            $iconSeo  = $seoTr  ? '<span class="badge bg-success">'.e($statusSeo).'</span>'  : '<span class="badge bg-secondary">trống</span>';
                            $iconBody = $bodyTr ? '<span class="badge bg-success">'.e($statusBody).'</span>' : '<span class="badge bg-secondary">trống</span>';
                        @endphp
                        <tr class="{{ $isDefault ? 'table-warning' : '' }}">
                            <td>
                                @if (!empty($lng->flag))
                                    <img src="{{ asset(ltrim($lng->flag, '/')) }}" alt="{{ $lng->code }}" style="width:20px;height:14px;object-fit:cover;">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $lng->name_native ?? $lng->name }}</strong>
                                @if ($isDefault) <small class="text-muted">(mặc định)</small> @endif
                            </td>
                            <td><code>{{ strtoupper($lng->code) }}</code></td>
                            <td>{!! $iconSeo !!}</td>
                            <td>{!! $iconBody !!}</td>
                            <td class="text-end">
                                @if ($isDefault)
                                    <span class="badge bg-light text-dark">Sửa ở trang này</span>
                                @else
                                    <a href="{{ route('admin.translation.edit', ['locale' => $lng->code, 'seoId' => $seo->id]) }}"
                                       class="btn btn-sm btn-{{ $seoTr ? 'outline-primary' : 'primary' }}" target="_blank">
                                        <i class="fa-regular fa-{{ $seoTr ? 'edit' : 'plus' }} me-1"></i>
                                        {{ $seoTr ? 'Chỉnh sửa' : 'Dịch sang ' . strtoupper($lng->code) }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
