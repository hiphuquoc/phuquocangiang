@php
  $gallery = $gallery ?? [];
@endphp

<div class="sd-gallery-lightbox sd-hotel-gallery-lightbox" id="hotel-gallery-lightbox" hidden aria-hidden="true">
  <button type="button" class="sd-gallery-lightbox__close" data-hotel-gallery-close aria-label="Đóng">&times;</button>
  <button type="button" class="sd-gallery-lightbox__nav sd-gallery-lightbox__nav--prev" data-hotel-gallery-prev aria-label="Ảnh trước">&#8249;</button>
  <button type="button" class="sd-gallery-lightbox__nav sd-gallery-lightbox__nav--next" data-hotel-gallery-next aria-label="Ảnh sau">&#8250;</button>
  <div class="sd-gallery-lightbox__viewport">
    <figure class="sd-gallery-lightbox__frame">
      <img src="" alt="{{ $hotelName ?? '' }}" data-hotel-gallery-image width="1280" height="720">
    </figure>
    <p class="sd-gallery-lightbox__cap" data-hotel-gallery-caption></p>
  </div>
</div>

<script type="application/json" id="hotel-gallery-data">@json($gallery)</script>
