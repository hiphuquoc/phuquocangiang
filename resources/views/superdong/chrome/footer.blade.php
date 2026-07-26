<!-- FOOTER -->
@php
  $nav = $islandNav ?? island_nav();
  $blogFooterLinks = array_values(array_filter(array_map(
    fn (array $link) => ['href' => $link['href'] ?? '#', 'label' => ltrim($link['label'] ?? '', '— ')],
    $islandBlogCategories ?? []
  ), fn (array $link) => ($link['label'] ?? '') !== ''));
  $footerNav = [
    'Dịch vụ' => [
      ['href' => $nav['ferry'] ?? '#ferry', 'label' => 'Vé tàu cao tốc'],
      ['href' => $nav['tours'] ?? '#tours', 'label' => 'Tour Côn Đảo'],
      ['href' => $nav['hotels'] ?? '#hotels', 'label' => 'Khách sạn'],
      ['href' => $nav['services'] ?? '#services', 'label' => 'Vé vui chơi'],
      ['href' => $nav['rental'] ?? '#rental', 'label' => 'Thuê xe'],
    ],
    'Hỗ trợ' => [
      ['href' => $nav['faq'] ?? '#faq', 'label' => 'Câu hỏi thường gặp'],
      ['href' => '#', 'label' => 'Chính sách đổi vé'],
      ['href' => '#', 'label' => 'Hướng dẫn thanh toán'],
      ['href' => '#', 'label' => 'Liên hệ'],
    ],
  ];
  if ($blogFooterLinks !== []) {
    $footerNav = array_merge(
      ['Dịch vụ' => $footerNav['Dịch vụ'], 'Blog' => $blogFooterLinks],
      ['Hỗ trợ' => $footerNav['Hỗ trợ']]
    );
  }
  $footerContacts = [
    [
      'label' => 'Hotline 24/7',
      'hint' => '1900 5454 87',
      'href' => 'tel:1900545487',
      'icon' => 'phone',
    ],
    [
      'label' => 'Email hỗ trợ',
      'hint' => 'support@superdong.dev',
      'href' => 'mailto:support@superdong.dev',
      'icon' => 'mail',
    ],
    [
      'label' => 'Văn phòng',
      'hint' => 'Lê Hồng Phong, Côn Đảo',
      'href' => null,
      'icon' => 'pin',
    ],
  ];
  $payments = [
    ['id' => 'visa', 'label' => 'Visa', 'word' => 'VISA'],
    ['id' => 'mastercard', 'label' => 'Mastercard'],
    ['id' => 'momo', 'label' => 'MoMo', 'word' => 'MoMo'],
    ['id' => 'vcb', 'label' => 'Vietcombank', 'word' => 'VCB'],
    ['id' => 'zalopay', 'label' => 'ZaloPay', 'word' => 'ZaloPay'],
  ];
  $socials = [
    ['id' => 'facebook', 'label' => 'Facebook', 'href' => '#'],
    ['id' => 'instagram', 'label' => 'Instagram', 'href' => '#'],
    ['id' => 'youtube', 'label' => 'YouTube', 'href' => '#'],
    ['id' => 'tiktok', 'label' => 'TikTok', 'href' => '#'],
  ];
  $legalLinks = [
    ['href' => '#', 'label' => 'Điều khoản'],
    ['href' => '#', 'label' => 'Bảo mật'],
    ['href' => '#', 'label' => 'Sitemap'],
  ];
@endphp

<footer class="sd-footer">
  @include('superdong.assets.footer-icon-defs')

  <div class="sd-footer__fx" aria-hidden="true">
    <span class="sd-footer__orb sd-footer__orb--sky"></span>
    <span class="sd-footer__orb sd-footer__orb--sun"></span>
    <span class="sd-footer__mesh"></span>
    <svg class="sd-footer__wave" viewBox="0 0 1440 48" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0 24C240 48 480 0 720 24s480 24 720 0 480-24 720 0V48H0V24z" fill="currentColor"/>
    </svg>
  </div>

  <div class="sd-footer__shell">
    <div class="sd-footer__inner">
      <div class="sd-footer__mast">
        <a class="sd-footer__logo" href="{{ $nav['home'] ?? route('main.home') }}">
          <span class="sd-header__logo-mark" aria-hidden="true">SD</span>
          <span class="sd-footer__logo-text">
            <strong>Superdong</strong>
            <span>Côn Đảo Travel</span>
          </span>
        </a>

        <ul class="sd-footer__reach" aria-label="Thông tin liên hệ">
          @foreach($footerContacts as $contact)
            <li>
              @if($contact['href'])
                <a href="{{ $contact['href'] }}" class="sd-footer__reach-item sd-footer__reach-item--{{ $contact['icon'] }}">
              @else
                <div class="sd-footer__reach-item sd-footer__reach-item--{{ $contact['icon'] }} sd-footer__reach-item--static">
              @endif
                <span class="sd-footer__reach-icon" aria-hidden="true">
                  @if($contact['icon'] === 'phone')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.5 19.5 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                  @elseif($contact['icon'] === 'mail')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
                  @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  @endif
                </span>
                <span class="sd-footer__reach-text">
                  <span class="sd-footer__reach-label">{{ $contact['label'] }}</span>
                  <span class="sd-footer__reach-value">{{ $contact['hint'] }}</span>
                </span>
              @if($contact['href'])
                </a>
              @else
                </div>
              @endif
            </li>
          @endforeach
        </ul>
      </div>

      <div class="sd-footer__grid">
        <div class="sd-footer__brand">
          <p class="sd-footer__tagline">Nền tảng du lịch Côn Đảo trọn gói — vé tàu cao tốc, tour, khách sạn và trải nghiệm biển đảo trên một hệ sinh thái tin cậy.</p>

          <div class="sd-footer__certs">
            <span>GPĐKKD 1702204052</span>
            <span>Bà Rịa – Vũng Tàu</span>
          </div>

          <div class="sd-footer__social" aria-label="Mạng xã hội">
            @foreach($socials as $social)
              <a
                href="{{ $social['href'] }}"
                class="sd-footer__social-tile sd-footer__social-tile--{{ $social['id'] }}"
                aria-label="{{ $social['label'] }}"
                target="_blank"
                rel="noopener noreferrer"
              >
                @include('superdong.assets.footer-icons', ['icon' => $social['id']])
              </a>
            @endforeach
          </div>
        </div>

        @foreach($footerNav as $title => $links)
          <nav class="sd-footer__col" aria-label="{{ $title }}">
            <h4 class="sd-footer__col-title">{{ $title }}</h4>
            <ul class="sd-footer__links">
              @foreach($links as $link)
                <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
              @endforeach
            </ul>
          </nav>
        @endforeach

        <div class="sd-footer__col sd-footer__col--contact">
          <h4 class="sd-footer__col-title">Liên hệ</h4>
          <ul class="sd-footer__contact">
            <li>
              <span class="sd-footer__contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              </span>
              <span class="sd-footer__contact-body">
                <strong>Giờ hỗ trợ</strong>
                7:00 – 21:00, Thứ 2 – Chủ nhật
              </span>
            </li>
            <li>
              <span class="sd-footer__contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </span>
              <span class="sd-footer__contact-body">
                <a href="#">Xem văn phòng trên bản đồ</a>
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="sd-footer__legal">
    <div class="sd-footer__legal-inner">
      <p class="sd-footer__copy">© {{ date('Y') }} Superdong. Bảo lưu mọi quyền.</p>

      <nav class="sd-footer__legal-nav" aria-label="Liên kết pháp lý">
        @foreach($legalLinks as $link)
          <a href="{{ $link['href'] }}">{{ $link['label'] }}</a>
        @endforeach
      </nav>

      <ul class="sd-footer__payments" role="list" aria-label="Phương thức thanh toán">
        @foreach($payments as $pay)
          <li>
            <span class="sd-footer__pay sd-footer__pay--{{ $pay['id'] }}" role="img" aria-label="{{ $pay['label'] }}">
              @if($pay['id'] === 'mastercard')
                <span class="sd-footer__pay-mark" aria-hidden="true">
                  @include('superdong.assets.footer-icons', ['icon' => 'mastercard'])
                </span>
              @else
                <span class="sd-footer__pay-word" aria-hidden="true">{{ $pay['word'] }}</span>
              @endif
            </span>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</footer>
