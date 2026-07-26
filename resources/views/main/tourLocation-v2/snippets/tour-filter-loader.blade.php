<div class="sd-tour-filter-loader sd-product-grid sd-product-grid--4 sd-product-grid--skeleton" aria-hidden="true" hidden>
  @for($i = 0; $i < 3; $i++)
    <article class="sd-card sd-card--deal sd-card--skeleton">
      <div class="sd-card__shell">
        <div class="sd-card__hero sd-card__hero--skeleton"></div>
      </div>
    </article>
  @endfor
</div>
<div class="sd-tour-filter-empty" hidden role="status">
  {{ t('no_matching_results') }}
</div>
