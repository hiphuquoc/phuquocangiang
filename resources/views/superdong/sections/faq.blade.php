<!-- FAQ SECTION -->
@php
  $faqSection = $homeFaq ?? ['items' => [], 'active' => false];
  $faqs = $faqSection['items'] ?? [];
  $openIndex = (int) ($faqSection['open_index'] ?? 0);
@endphp

@if(!empty($faqSection['active']) && count($faqs) > 0)
<section class="sd-faq-section" id="faq" aria-labelledby="sd-faq-title">
  <div class="sd-section__inner">
    <div class="sd-faq-section__layout">
      <aside class="sd-faq-section__aside" data-reveal>
        <span class="sd-faq-section__kicker">{{ $faqSection['kicker'] ?? 'Hỏi đáp' }}</span>
        <h2 class="sd-faq-section__title" id="sd-faq-title">{{ $faqSection['title'] ?? 'Câu hỏi thường gặp' }}</h2>
        <p class="sd-faq-section__desc">{{ $faqSection['description'] ?? '' }}</p>

        @if(!empty($faqSection['help_title']) || !empty($faqSection['help_body']))
          <div class="sd-faq-section__help">
            <span class="sd-faq-section__help-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <div>
              @if(!empty($faqSection['help_title']))
                <strong>{{ $faqSection['help_title'] }}</strong>
              @endif
              @if(!empty($faqSection['help_body']))
                <div class="sd-faq-section__help-body">{!! $faqSection['help_body'] !!}</div>
              @endif
            </div>
          </div>
        @endif
      </aside>

      <div class="sd-faq-section__list sd-faq" role="list">
        @foreach($faqs as $i => $faq)
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
              id="sd-faq-q-{{ $i }}"
              aria-controls="sd-faq-a-{{ $i }}"
            >
              <span class="sd-faq__index" aria-hidden="true">{{ $index }}</span>
              <span class="sd-faq__label">{{ $faq['q'] }}</span>
              <span class="sd-faq__toggle" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
              </span>
            </button>
            <div
              class="sd-faq__answer"
              id="sd-faq-a-{{ $i }}"
              role="region"
              aria-labelledby="sd-faq-q-{{ $i }}"
            >
              <div>
                <div class="sd-faq__answer-body">{!! $faq['a'] !!}</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif
