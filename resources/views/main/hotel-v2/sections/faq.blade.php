@php
  $faq = $faq ?? ['active' => false, 'items' => []];
  $faqs = $faq['items'] ?? [];
  $openIndex = (int) ($faq['open_index'] ?? 0);
@endphp

@if(!empty($faq['active']) && count($faqs) > 0)
<section class="sd-faq-section sd-hotel-faq" id="faq" aria-labelledby="sd-hotel-faq-title">
  <div class="sd-section__inner">
    <div class="sd-faq-section__layout">
      <aside class="sd-faq-section__aside" data-reveal>
        <span class="sd-faq-section__kicker">{{ $faq['kicker'] ?? t('kicker_support') }}</span>
        <h2 class="sd-faq-section__title" id="sd-hotel-faq-title">{{ $faq['title'] ?? t('tour_faq_aria') }}</h2>
        @if(!empty($faq['description']))
          <p class="sd-faq-section__desc">{{ $faq['description'] }}</p>
        @endif
      </aside>

      <div class="sd-faq-section__list sd-faq" role="list">
        @foreach($faqs as $i => $item)
          @php
            $index = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $isOpen = $i === $openIndex;
          @endphp
          <div
            @class(['sd-faq__item', 'is-open' => $isOpen])
            role="listitem"
            style="--fq-delay: {{ $i * 60 }}ms"
          >
            <button
              type="button"
              class="sd-faq__question"
              aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
              id="sd-hotel-faq-q-{{ $i }}"
              aria-controls="sd-hotel-faq-a-{{ $i }}"
            >
              <span class="sd-faq__index" aria-hidden="true">{{ $index }}</span>
              <span class="sd-faq__label">{{ $item['q'] }}</span>
              <span class="sd-faq__toggle" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </span>
            </button>
            <div
              class="sd-faq__answer"
              id="sd-hotel-faq-a-{{ $i }}"
              role="region"
              aria-labelledby="sd-hotel-faq-q-{{ $i }}"
            >
              <div>
                <div class="sd-faq__answer-body">{!! $item['a'] !!}</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif
