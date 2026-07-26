@php
  $items = $items ?? [];
@endphp

@if(!empty($items))
  <div class="sd-blog-grid">
    @foreach($items as $card)
      @include('superdong.ui.cards.blog', $card)
    @endforeach
  </div>
@else
  <div class="sd-listing-empty">
    {!! t('category_no_blog') !!}
  </div>
@endif
