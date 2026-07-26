<div class="sd-listing-filter sd-tour-filter" data-tour-filter aria-label="{{ t('filter_by') }}">
  <div class="sd-tour-filter__inner">
    <div class="sd-tour-filter__intro">
      <span class="sd-tour-filter__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
      </span>
      <div class="sd-tour-filter__copy">
        <span class="sd-tour-filter__label">{{ t('filter_by') }}</span>
        <span class="sd-tour-filter__hint" data-tour-filter-count aria-live="polite"></span>
      </div>
    </div>

    <div class="sd-tour-filter__track" role="group" aria-label="{{ t('filter_by') }}">
      <button type="button" class="sd-tour-filter__pill is-active" data-tour-filter-type="tat-ca-tour">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span>{{ t('filter_all') }}</span>
      </button>
      <button type="button" class="sd-tour-filter__pill" data-tour-filter-type="tour-trong-ngay">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        <span>{{ t('filter_day_tour') }}</span>
      </button>
      <button type="button" class="sd-tour-filter__pill" data-tour-filter-type="tour-nhieu-ngay">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <span>{{ t('filter_multi_day_tour') }}</span>
      </button>
    </div>
  </div>
</div>
