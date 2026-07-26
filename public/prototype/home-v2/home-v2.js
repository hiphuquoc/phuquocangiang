(() => {
  const header = document.querySelector('.sd-header');
  const toggle = document.querySelector('.sd-header__toggle');
  const mobileNav = document.querySelector('.sd-mobile-nav');

  const onScroll = () => {
    header?.classList.toggle('sd-header--scrolled', window.scrollY > 80);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  toggle?.addEventListener('click', () => {
    const open = mobileNav?.classList.toggle('is-open');
    toggle.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
  });

  mobileNav?.querySelector('.sd-mobile-nav__backdrop')?.addEventListener('click', () => {
    mobileNav.classList.remove('is-open');
    toggle?.classList.remove('is-open');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  });

  document.querySelectorAll('.sd-booking__tabs').forEach((tabs) => {
    const root = tabs.closest('.sd-booking');
    tabs.querySelectorAll('.sd-booking__tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        tabs.querySelectorAll('.sd-booking__tab').forEach((t) => t.classList.remove('is-active'));
        tab.classList.add('is-active');
        root?.querySelectorAll('.sd-booking__panel').forEach((panel) => {
          panel.classList.toggle('is-active', panel.dataset.panel === target);
        });
      });
    });
  });

  document.querySelectorAll('.sd-faq__question').forEach((btn) => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.sd-faq__item');
      const open = item?.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Scroll reveal — gắn cho các khối nội dung, có stagger trong cùng nhóm */
  const revealSelector = [
    '[data-reveal]',
    '.sd-section__head',
    '.sd-card',
    '.sd-ship-route__item',
    '.sd-combo-feature__main',
    '.sd-combo-feature__side > *',
    '.sd-bento__item',
    '.sd-route-guide__step',
    '.sd-split-banner__item',
    '.sd-blog-card',
    '.sd-reviews__item',
    '.sd-faq__item',
    '.sd-trust__item',
  ].join(',');

  const revealEls = Array.from(document.querySelectorAll(revealSelector));
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealEls.forEach((el) => el.classList.add('sd-reveal', 'is-in'));
  } else {
    revealEls.forEach((el) => {
      el.classList.add('sd-reveal');
      const siblings = Array.from(el.parentElement?.children || []).filter((c) => c.classList.contains('sd-reveal'));
      const idx = siblings.indexOf(el);
      if (idx > 0) el.style.transitionDelay = Math.min(idx, 6) * 70 + 'ms';
    });
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    revealEls.forEach((el) => io.observe(el));
  }

  /* Parallax nhẹ cho ảnh hero */
  const heroImg = document.querySelector('.sd-hero__media img');
  if (heroImg && !reduceMotion) {
    let ticking = false;
    const parallax = () => {
      const y = window.scrollY;
      if (y < window.innerHeight) {
        heroImg.style.transform = 'translateY(' + (-y * 0.12) + 'px) scale(1.02)';
      }
      ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (!ticking) { window.requestAnimationFrame(parallax); ticking = true; }
    }, { passive: true });
    parallax();
  }
})();
