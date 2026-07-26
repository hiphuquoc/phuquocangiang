<div class="sd-listing-filter" data-service-filter aria-label="{{ t('filter_by') }}">
  <div class="sd-listing-filter__inner">
    <div class="sd-listing-filter__intro">
      <span class="sd-listing-filter__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
      </span>
      <div class="sd-listing-filter__copy">
        <span class="sd-listing-filter__label">{{ t('filter_by') }}</span>
        <span class="sd-listing-filter__hint" data-service-filter-count aria-live="polite"></span>
      </div>
    </div>

    <div class="sd-listing-filter__track" role="group" aria-label="{{ t('filter_by') }}">
      <button type="button" class="sd-listing-filter__pill is-active" data-service-filter-type="tat-ca-ve">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span>{{ t('filter_all') }}</span>
      </button>
      <button type="button" class="sd-listing-filter__pill" data-service-filter-type="ve-giam-gia">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        <span>{{ t('filter_on_sale') }}</span>
      </button>
      <button type="button" class="sd-listing-filter__pill" data-service-filter-type="ve-danh-gia-cao">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <span>{{ t('filter_top_rated') }}</span>
      </button>
    </div>
  </div>
</div>
