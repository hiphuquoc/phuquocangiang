<!-- NEWSLETTER — lá thư -->
@php
  $letter = $homeNewsletter ?? ['active' => false];
@endphp

@if(!empty($letter['active']))
<section class="sd-letter" id="newsletter" aria-labelledby="sd-letter-title">
  <div class="sd-section__inner sd-letter__inner">
    <div class="sd-letter__stage" data-reveal>
      <div class="sd-letter__envelope" aria-hidden="true">
        <div class="sd-letter__env-back"></div>
        <div class="sd-letter__env-flap"></div>
        <div class="sd-letter__env-front"></div>
      </div>

      <article class="sd-letter__paper">
        <div class="sd-letter__stamp" aria-hidden="true">
          <span class="sd-letter__stamp-inner">
            <strong>{{ $letter['stamp_text'] ?? 'SD' }}</strong>
            <em>{{ $letter['stamp_year'] ?? date('Y') }}</em>
          </span>
        </div>

        <div class="sd-section-head__eyebrow-wrapper sd-letter__kicker-wrapper">
          <span class="sd-section-head__accent-dot" aria-hidden="true"></span>
          <span class="sd-section-head__eyebrow">{{ $letter['kicker'] ?? 'Thư từ Superdong' }}</span>
        </div>
        <h2 class="sd-section-head__title sd-letter__title" id="sd-letter-title">{!! $letter['title'] ?? '' !!}</h2>
        <p class="sd-section-head__desc sd-letter__lead">{{ $letter['lead'] ?? '' }}</p>

        <form class="sd-letter__form" action="#" method="post" onsubmit="return false;">
          <label class="sd-letter__field">
            <span class="sd-letter__field-label">{{ $letter['field_label'] ?? 'Kính gửi' }}</span>
            <input type="email" name="email" placeholder="{{ $letter['email_placeholder'] ?? 'email@ban.com' }}" autocomplete="email" required aria-label="Email của bạn">
          </label>
          <button type="submit" class="sd-letter__submit">
            <span>{{ $letter['submit_text'] ?? 'Gửi thư đăng ký' }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          </button>
        </form>

        @if(!empty($letter['note']))
          <p class="sd-letter__note">{{ $letter['note'] }}</p>
        @endif
        @if(!empty($letter['sign_text']))
          <p class="sd-letter__sign">{{ $letter['sign_text'] }}</p>
        @endif
      </article>
    </div>
  </div>
</section>
@endif
