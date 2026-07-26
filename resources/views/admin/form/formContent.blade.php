@php
  $contentValue = old('content');
  if ($contentValue === null || $contentValue === '') {
      $passed = $content ?? null;
      $contentValue = ($passed === false || $passed === null) ? '' : $passed;
  }
  if ($contentValue === '' && !empty($item?->seo)) {
      $contentValue = seo_content_for_admin($item->seo);
  }
@endphp
<div class="formBox">
    <div class="formBox_full">

        <!-- One Column -->
        <textarea class="form-control" id="content" name="content" rows="20">{{ $contentValue }}</textarea>

    </div>
</div>

@push('scripts-custom')
    {{-- @include('admin.script.tiny', ['id' => 'content']) --}}
@endpush
