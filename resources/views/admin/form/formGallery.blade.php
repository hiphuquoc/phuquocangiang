<!-- One Row -->
<div class="formBox_full_item">
    <span data-toggle="tooltip" data-placement="top" title="
        Đây là ảnh dùng làm slider hiển thị ở phần giới thiệu và phần ảnh đẹp của Tour
    ">
        <i class="explainInput" data-feather='alert-circle'></i>
        <label class="form-label" for="gallery">Ảnh Gallery <span style="font-weight:700;">750 * 460 px</span></label>
    </span>
    <input class="form-control" type="file" id="gallery" name="gallery[]" onChange="readURLs(this, 'galleryUpload');" multiple />
    <div class="invalid-feedback">{{ config('admin.massage_validate.not_empty') }}</div>
    <div id="galleryUpload" class="imageUpload">
        @if(!empty($item->files)&&$type==='edit')
            @foreach($item->files as $file)
                @if($file->file_type==='gallery'&&media_exists($file->getRawOriginal('file_path')))
                    <div id="gallery_{{ $file->id }}">
                        <img src="{{ $file->file_path }}" />
                        <i class="fa-solid fa-circle-xmark" onClick="removeGallery('{{ $file->id }}');"></i>
                        @php
                            $rawPath    = $file->getRawOriginal('file_path');
                            $mediaMeta  = media_info($rawPath);
                        @endphp
                        <div style="margin-top:0.25rem;color:#789;display:flex;justify-content:space-between;">
                            @if(!empty($mediaMeta))
                            <span>.{{ $mediaMeta['extension'] }}</span>
                            <span>{{ $mediaMeta['width'] }}*{{ $mediaMeta['height'] }} px</span>
                            <span>{{ $mediaMeta['size_kb'] }} MB</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>

@push('scripts-custom')
    <script type="text/javascript">

        function readURLs(input, idShow) {
            if(input.files){
                const data          = input.files;
                for(let i = 0; i<data.length; i++){
                    let file        = data[i];
                    if (!file.type.match('image')) continue;
                    var picReader   = new FileReader();
                    picReader.addEventListener("load", function (event) {
                        var picFile = event.target;
                        var div     = document.createElement("div");
                        div.innerHTML = '<img src="'+picFile.result+'" />';
                        $('#'+idShow).append(div);
                    });
                    picReader.readAsDataURL(file);
                }
            }
        }

        function removeGallery(id){
            $.ajax({
                url         : "{{ route('admin.gallery.removeGallery') }}",
                type        : "GET",
                dataType    : "html",
                data        : { id : id }
            }).done(function(data){
                if(data==true) $('#gallery_'+id).remove();
            });
        }

    </script>
@endpush