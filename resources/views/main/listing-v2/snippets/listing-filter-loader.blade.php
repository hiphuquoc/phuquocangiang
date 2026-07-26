<div class="sd-listing-filter-loader" data-listing-filter-loader hidden aria-hidden="true">
  <div class="sd-listing-filter-loader__track" aria-hidden="true">
    <span></span><span></span><span></span>
  </div>
  <p class="sd-listing-filter-loader__empty" data-listing-filter-empty hidden>
    {!! t($emptyKey ?? 'tour_list_empty', ['name' => e($locationName ?? island_name()), 'brand' => e(config('main.name'))]) !!}
  </p>
</div>
