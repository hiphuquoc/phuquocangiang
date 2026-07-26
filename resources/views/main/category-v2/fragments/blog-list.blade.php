@php
  $items = $items ?? [];
  $showFeatured = $showFeatured ?? true;
  $featured = $showFeatured ? ($items[0] ?? null) : null;
  $rows = $featured !== null ? array_slice($items, 1) : $items;
@endphp

@if($featured !== null || !empty($rows))
  <div class="sd-blog-list">
    @if($featured !== null)
      @include('superdong.ui.cards.blog-row', array_merge($featured, ['featured' => true]))
    @endif

    @if(!empty($rows))
      <div class="sd-blog-list__stack" role="list">
        @foreach($rows as $card)
          <div role="listitem">
            @include('superdong.ui.cards.blog-row', $card)
          </div>
        @endforeach
      </div>
    @endif
  </div>
@else
  <div class="sd-blog-empty">
    <div class="sd-blog-empty__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24"><path fill="currentColor" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
    </div>
    <p>{!! t('category_no_blog') !!}</p>
  </div>
@endif
