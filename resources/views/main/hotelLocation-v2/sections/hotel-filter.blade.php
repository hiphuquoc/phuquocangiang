<div class="sd-listing-filter" data-hotel-filter aria-label="{{ t('filter_by') }}">
  <div class="sd-listing-filter__inner">
    <div class="sd-listing-filter__intro">
      <span class="sd-listing-filter__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
      </span>
      <div class="sd-listing-filter__copy">
        <span class="sd-listing-filter__label">{{ t('filter_by') }}</span>
        <span class="sd-listing-filter__hint" data-hotel-filter-count aria-live="polite"></span>
      </div>
    </div>

    <div class="sd-listing-filter__track" role="group" aria-label="{{ t('filter_by') }}">
      <button type="button" class="sd-listing-filter__pill is-active" data-hotel-filter-type="tat-ca-khach-san">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span>{{ t('filter_all') }}</span>
      </button>
      @foreach($filters ?? [] as $filter)
        <button type="button" class="sd-listing-filter__pill" data-hotel-filter-type="{{ $filter['slug'] }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
          <span>{{ $filter['label'] }}</span>
        </button>
      @endforeach
    </div>
  </div>
</div>
