<!-- ISLAND GALLERY — full-width band -->
@php
  $gallerySection = $islandGallery ?? ['items' => [], 'active' => false];
  $galleryItems = $gallerySection['items'] ?? [];
  $galleryCount = count($galleryItems);
  $islandName = island_name();
@endphp

@if(!empty($gallerySection['active']) && $galleryCount > 0)
<section class="sd-gallery" id="gallery" aria-labelledby="sd-gallery-title">
  <div class="sd-gallery__scene" aria-hidden="true">
    <span class="sd-gallery__scene-mark">◇</span>
    <span class="sd-gallery__scene-leak sd-gallery__scene-leak--gold"></span>
    <span class="sd-gallery__scene-leak sd-gallery__scene-leak--sky"></span>
    <span class="sd-gallery__scene-frame"></span>
  </div>

  <div class="sd-gallery__intro">
    <span class="sd-gallery__label" data-reveal>{{ $gallerySection['eyebrow'] ?? 'Trải nghiệm đảo' }}</span>
    <h2 class="sd-gallery__title" id="sd-gallery-title" data-reveal>{{ $gallerySection['title'] ?? ($islandName . ' qua từng khoảnh khắc đẹp') }}</h2>
    @if(!empty($gallerySection['lead']))
      <p class="sd-gallery__lead" data-reveal>{{ $gallerySection['lead'] }}</p>
    @endif
    <div class="sd-gallery__meta" data-reveal>
      <span class="sd-gallery__count">
        <strong>{{ str_pad((string) $galleryCount, 2, '0', STR_PAD_LEFT) }}</strong> khoảnh khắc
      </span>
      <span class="sd-gallery__meta-divider" aria-hidden="true"></span>
      <span class="sd-gallery__meta-caption">{{ $gallerySection['meta_caption'] ?? ('Thư viện ảnh ' . $islandName) }}</span>
    </div>
  </div>

  <div class="sd-gallery__band">
    <div class="sd-gallery__grid" data-reveal role="list" aria-label="{{ $gallerySection['meta_caption'] ?? ('Thư viện ảnh ' . $islandName) }}">
      @foreach($galleryItems as $i => $item)
        @php $index = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); @endphp
        <article class="sd-gallery__tile" role="listitem">
          <a
            href="#"
            class="sd-gallery__tile-link"
            data-sd-gallery-lightbox
            data-src="{{ $item['lightbox'] ?? $item['image'] }}"
            data-title="{{ $item['title'] }}"
            data-tag="{{ $item['tag'] ?? '' }}"
            data-alt="{{ $item['alt'] }}"
            data-pos="{{ $item['pos'] ?? 'center center' }}"
            aria-label="Xem ảnh: {{ $item['alt'] }}"
          >
            <figure class="sd-gallery__frame">
              <img
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                data-lazy-src="{{ $item['image'] }}"
                alt="{{ $item['alt'] }}"
                width="800"
                height="600"
                decoding="async"
                loading="lazy"
                style="--img-pos: {{ $item['pos'] ?? 'center center' }};"
              >
              <figcaption class="sd-gallery__tile-cap">
                <span class="sd-gallery__tile-index" aria-hidden="true">{{ $index }}</span>
                <span class="sd-gallery__tile-rule" aria-hidden="true"></span>
                <span class="sd-gallery__tile-title">{{ $item['title'] }}</span>
              </figcaption>
            </figure>
          </a>
        </article>
      @endforeach
    </div>
  </div>
</section>

@include('superdong.ui.gallery-lightbox')
@endif
