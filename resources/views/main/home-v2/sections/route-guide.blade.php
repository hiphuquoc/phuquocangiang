<!-- ROUTE GUIDE -->
<section class="sd-section">
  <div class="sd-section__inner">
    <div class="sd-route-guide">
      <div class="sd-route-guide__steps">
        @include('superdong.ui.section-head', [
          'eyebrow' => 'Di chuyển',
          'title' => 'Cách đến Côn Đảo',
          'desc' => 'Hai tuyến tàu cao tốc chính — Superdong kết nối bạn với hòn đảo linh thiêng và xinh đẹp.',
          'compact' => true,
        ])
        <div class="sd-route-guide__step">
          <span class="sd-route-guide__step-num">1</span>
          <div>
            <h4>Tuyến Trần Đề (Sóc Trăng) → Côn Đảo</h4>
            <p>~2h15 · Tuyến di chuyển nhanh nhất từ đất liền · Nhiều chuyến/ngày theo khung giờ linh hoạt</p>
          </div>
        </div>
        <div class="sd-route-guide__step">
          <span class="sd-route-guide__step-num">2</span>
          <div>
            <h4>Tuyến Vũng Tàu → Côn Đảo</h4>
            <p>~3h40 · Khởi hành từ cảng Cầu Đá · Phù hợp cho du khách từ TP.HCM và các tỉnh miền Nam</p>
          </div>
        </div>
        <div class="sd-route-guide__step">
          <span class="sd-route-guide__step-num">3</span>
          <div>
            <h4>Đặt vé trước 3–7 ngày</h4>
            <p>Đặt sớm để giữ chỗ, đặc biệt dịp đi lễ cuối tuần và mùa biển êm từ tháng 3 đến tháng 9</p>
          </div>
        </div>
      </div>
      <div class="sd-route-guide__map" aria-hidden="true">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="400" height="300" fill="#f0f9ff"/>
          <path d="M60 165 Q130 105 210 130 T350 100" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="6 4" opacity="0.6"/>
          <circle cx="92" cy="180" r="12" fill="#0284c7"/><text x="92" y="215" text-anchor="middle" fill="#075985" font-size="11" font-weight="700">Trần Đề</text>
          <circle cx="188" cy="80" r="10" fill="#0284c7"/><text x="188" y="65" text-anchor="middle" fill="#075985" font-size="10" font-weight="600">Vũng Tàu</text>
          <circle cx="300" cy="160" r="16" fill="#f59e0b"/><text x="300" y="200" text-anchor="middle" fill="#075985" font-size="12" font-weight="800">Côn Đảo</text>
          <path d="M92 180 L300 160" stroke="#0284c7" stroke-width="2" marker-end="url(#arrow)"/>
          <path d="M188 80 L300 160" stroke="#0284c7" stroke-width="2" marker-end="url(#arrow)"/>
          <defs><marker id="arrow" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto"><path d="M0,0 L0,6 L6,3" fill="#0284c7"/></marker></defs>
        </svg>
      </div>
    </div>
  </div>
</section>
