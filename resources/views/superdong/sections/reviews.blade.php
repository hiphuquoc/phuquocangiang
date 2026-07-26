<!-- REVIEWS — Voice postcards -->
@php
  $reviewsSection = $homeReviews ?? ['items' => [], 'active' => false];
  $reviews = $reviewsSection['items'] ?? [];
  $partners = $reviewsSection['partners'] ?? [];
  $scoreValue = $reviewsSection['score_value'] ?? 4.9;
  $scoreOffset = $reviewsSection['score_dashoffset'] ?? 32;
  $scoreStats = $reviewsSection['score_stats'] ?? [];
  $tilts = [-0.4, 0.35, -0.2];
@endphp

@if(!empty($reviewsSection['active']) && count($reviews) > 0)
<section class="sd-voices" id="reviews" aria-labelledby="sd-reviews-title">
  <div class="sd-voices__bg" aria-hidden="true"></div>

  <div class="sd-section__inner sd-voices__inner">
    <div class="sd-voices__mast">
      <header class="sd-voices__head" data-reveal>
        <span class="sd-voices__kicker">{{ $reviewsSection['kicker'] ?? 'Khách hàng nói gì' }}</span>
        <h2 class="sd-voices__title" id="sd-reviews-title">{{ $reviewsSection['title'] ?? 'Hành trình được tin chọn' }}</h2>
        <p class="sd-voices__desc">{{ $reviewsSection['description'] ?? '' }}</p>
      </header>

      <div class="sd-voices__score" data-reveal aria-label="{{ number_format($scoreValue, 1) }} trên 5 sao">
        <div class="sd-voices__score-ring">
          <svg viewBox="0 0 120 120" aria-hidden="true">
            <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="6" opacity="0.14"/>
            <circle cx="60" cy="60" r="52" fill="none" stroke="url(#sdVoiceScoreGrad)" stroke-width="6" stroke-linecap="round" stroke-dasharray="327" stroke-dashoffset="{{ $scoreOffset }}"/>
            <defs>
              <linearGradient id="sdVoiceScoreGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#0ea5e9"/>
                <stop offset="100%" stop-color="#0284c7"/>
              </linearGradient>
            </defs>
          </svg>
          <span class="sd-voices__score-value">{{ number_format($scoreValue, 1) }}</span>
          <span class="sd-voices__score-stars" aria-hidden="true">★★★★★</span>
        </div>
        @if(!empty($scoreStats))
          <ul class="sd-voices__score-meta" role="list">
            @foreach($scoreStats as $stat)
              <li><strong>{{ $stat['value'] ?? '' }}</strong> {{ $stat['label'] ?? '' }}</li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>

    @if(!empty($reviews))
      <div class="sd-voices__carousel" data-sd-voices-pager>
        <div class="sd-voices__grid" data-sd-voices-grid>
          @foreach($reviews as $idx => $review)
            @php
              $rating = max(1, min(5, (int) ($review['rating'] ?? 5)));
              $tilt = $tilts[$idx % 3];
            @endphp
            <article class="sd-voice-card" style="--vc-delay: {{ ($idx % 3) * 90 }}ms; --vc-tilt: {{ $tilt }}deg;" data-sd-voice-card>
              <div class="sd-voice-card__top">
                @if(!empty($review['tag']))
                  <span class="sd-voice-card__tag">{{ $review['tag'] }}</span>
                @endif
                <div class="sd-voice-card__stars" aria-label="{{ $rating }} sao">
                  @for($s = 0; $s < $rating; $s++)
                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.5l2.47 5.01 5.53.8-4 3.9.94 5.5L10 14.3l-4.94 2.6.94-5.5-4-3.9 5.53-.8L10 1.5z"/></svg>
                  @endfor
                </div>
              </div>

              <blockquote class="sd-voice-card__quote">
                <p>"{{ $review['text'] }}"</p>
              </blockquote>

              <footer class="sd-voice-card__foot">
                <span class="sd-voice-card__avatar">
                  <img src="{{ $review['avatar'] }}" alt="" width="44" height="44" loading="lazy" decoding="async">
                </span>
                <div class="sd-voice-card__who">
                  <strong>{{ $review['name'] }}</strong>
                  @if(!empty($review['meta']))
                    <span>{{ $review['meta'] }}</span>
                  @endif
                </div>
              </footer>
            </article>
          @endforeach
        </div>

        <nav class="sd-voices__pager" aria-label="Xem thêm đánh giá">
          <button type="button" class="sd-voices__pager-btn" data-sd-voices-prev aria-label="Xem đánh giá trước">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Trước</span>
          </button>
          <span class="sd-voices__pager-status" data-sd-voices-counter aria-live="polite"></span>
          <button type="button" class="sd-voices__pager-btn" data-sd-voices-next aria-label="Xem thêm đánh giá">
            <span>Tiếp</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </nav>
      </div>
    @endif

    @if(!empty($partners))
      <div class="sd-voices__rail" data-reveal>
        <span class="sd-voices__rail-label">{{ $reviewsSection['partners_label'] ?? 'Đối tác tin cậy' }}</span>
        <ul class="sd-voices__partners" role="list">
          @foreach($partners as $partner)
            <li><span>{{ $partner }}</span></li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
</section>
@endif
