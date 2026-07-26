<!-- One Row -->
<div class="formBox_full_item">
    <span data-toggle="tooltip" data-placement="top" title="
        Đây là Ảnh đại diện dùng làm Ảnh đại diện trên website, Ảnh đại diện ngoài Google, Ảnh đại diện khi Share link
    ">
        <i class="explainInput" data-feather='alert-circle'></i>
        <label class="form-label" for="avatar">Ảnh đại diện <span style="font-weight:700;">500 * 500 px</span></label>
    </span>
    <input class="form-control" type="file" id="avatar" name="avatar" onchange="readURL(this, 'imageUpload');">
    <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    <div class="imageUpload">
        @if(!empty($item->avatar)&&media_exists($item->avatar)&&$type!='copy')
            @php
                $rawPath    = $item->avatar;
                $mediaMeta  = media_info($rawPath);
                $imageUrl   = $mediaMeta['url'] ?? media_url($rawPath);
            @endphp
            <img id="imageUpload" src="{{ $imageUrl }}" />
            @if(!empty($mediaMeta))
            <div style="margin-top:0.25rem;color:#789;display:flex;justify-content:space-between;">
                <span>.{{ $mediaMeta['extension'] }}</span>
                <span>{{ $mediaMeta['width'] }}*{{ $mediaMeta['height'] }} px</span>
                <span>{{ $mediaMeta['size_kb'] }} MB</span>
            </div>
            @endif
        @else
            <img id="imageUpload" src="{{ config('admin.images.default_660x660') }}" />
        @endif
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">

        function readURL(input, idShow) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#'+idShow).attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

    </script>
@endpush
