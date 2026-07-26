<!-- THUÊ XE MÁY & Ô TÔ -->
@php
  $rentalHref = island_nav()['rental'] ?? route('main.home') . '#rental';
  $rentalOptions = [
    [
      'type' => 'moto',
      'eyebrow' => 'Xe máy',
      'title' => 'Honda Vision / Lead',
      'desc' => 'Linh hoạt khám phá Côn Sơn · giao xe tận khách sạn',
      'image' => 'https://static.vinwonders.com/production/gia-thue-xe-may-o-phu-quoc.jpg',
      'specs' => ['150–175cc', '2 mũ bảo hiểm', 'Bình xăng đầy'],
      'price' => '120.000',
      'unit' => '/ ngày',
      'href' => $rentalHref,
    ],
    [
      'type' => 'car',
      'eyebrow' => 'Ô tô',
      'title' => 'Toyota Innova 7 chỗ',
      'desc' => 'Gia đình & nhóm bạn · tài xế hoặc tự lái',
      'image' => 'https://static.vinwonders.com/2022/06/thue-xe-tu-lai-phu-quoc-thumb-1.jpg',
      'specs' => ['7 chỗ · máy lạnh', 'Lái phụ / tự lái', 'Đón sân bay / cảng'],
      'price' => '850.000',
      'unit' => '/ ngày',
      'href' => $rentalHref,
    ],
  ];
@endphp
<section class="sd-section sd-rental" id="rental" aria-labelledby="sd-rental-title">
  <div class="sd-section__inner">
    <header class="sd-rental__head" data-reveal>
      <span class="sd-rental__kicker">Di chuyển trên đảo</span>
      <h2 class="sd-rental__title" id="sd-rental-title">Thuê xe máy &amp; ô tô Côn Đảo</h2>
      <p class="sd-rental__desc">Nhận xe nhanh tại trung tâm Côn Sơn — giấy tờ thủ tục nhanh gọn, bảng giá công khai - phù hợp, hỗ trợ bản đồ - tư vấn lộ trình miễn phí.</p>
    </header>

    <div class="sd-rental__body">
    <div class="sd-rental__deck" data-rental-deck>
      @foreach($rentalOptions as $idx => $option)
        <article
          class="sd-rental-pass sd-rental-pass--{{ $option['type'] }}"
          style="--rp-delay: {{ $idx * 90 }}ms"
          data-rental-pass
        >
          <div class="sd-rental-pass__stub">
            <span class="sd-rental-pass__stub-icon" aria-hidden="true">
              @if($option['type'] === 'moto')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="5.5" cy="17.5" r="2.5"/><circle cx="18.5" cy="17.5" r="2.5"/><path d="M8 17h8"/><path d="M5.5 15 8 9h5l2 3h4l1.5 3"/></svg>
              @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17h10"/><path d="M5 11l1-3h12l1 3"/><path d="M6 11v6"/><path d="M18 11v6"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
              @endif
            </span>
            <span class="sd-rental-pass__stub-label">{{ $option['eyebrow'] }}</span>
          </div>

          <div class="sd-rental-pass__body">
            <div class="sd-rental-pass__media">
              <img src="{{ $option['image'] }}" alt="{{ $option['title'] }}" loading="lazy" decoding="async">
              <span class="sd-rental-pass__eyebrow">{{ $option['eyebrow'] }}</span>
            </div>
            <div class="sd-rental-pass__content">
              <h3 class="sd-rental-pass__name">{{ $option['title'] }}</h3>
              <p class="sd-rental-pass__text">{{ $option['desc'] }}</p>
              <ul class="sd-rental-pass__specs" role="list">
                @foreach($option['specs'] as $spec)
                  <li>{{ $spec }}</li>
                @endforeach
              </ul>
              <div class="sd-rental-pass__foot">
                <div class="sd-rental-pass__price">
                  <span>Từ</span>
                  <strong>{{ $option['price'] }}<small>₫</small></strong>
                  <em>{{ $option['unit'] }}</em>
                </div>
                <a href="{{ $option['href'] }}" class="sd-rental-pass__cta">Đặt xe</a>
              </div>
            </div>
          </div>
        </article>
      @endforeach
    </div>

    <ul class="sd-rental__perks" role="list">
      <li>
        <span class="sd-rental__perk-check" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <span class="sd-rental__perk-text">Giao xe tận khách sạn / cảng</span>
      </li>
      <li>
        <span class="sd-rental__perk-check" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <span class="sd-rental__perk-text">Hỗ trợ 24/7 khi gặp sự cố</span>
      </li>
      <li>
        <span class="sd-rental__perk-check" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <span class="sd-rental__perk-text">Bản đồ &amp; gợi ý lộ trình miễn phí</span>
      </li>
    </ul>
    </div>
  </div>
</section>
