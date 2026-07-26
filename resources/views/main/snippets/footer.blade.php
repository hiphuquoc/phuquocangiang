<footer class="footer footer--redesign" role="contentinfo">
    <div class="container footer__container">
        <div class="footer__lead">
            <div class="footer__brandBlock">
                <div class="footer__brandHeadRow">
                    <p class="footer__eyebrow">{{ t('footer_eyebrow') }}</p>
                    <h2 class="footer__brandTitle">{{ config('company.name') }}</h2>
                </div>
                <p class="footer__brandText">{{ t('footer_brand_text') }}</p>
                <ul class="footer__chipRow" aria-label="{{ t('footer_chip_aria') }}">
                    <li><span class="footer__chip">{{ t('footer_chip_advisor') }}</span></li>
                    <li><span class="footer__chip">{{ t('footer_chip_curated') }}</span></li>
                    <li><span class="footer__chip">{{ t('footer_chip_support') }}</span></li>
                </ul>
                @php
                    $__s = config('company.social', []);
                    $__ext = static function ($url) {
                        $u = trim((string) $url);
                        return $u !== '' && $u !== '#' && preg_match('#^https?://#i', $u);
                    };
                @endphp
                <div class="footer__brandConnect">
                    <p id="footer-connect-heading" class="footer__connectLabel">{{ t('footer_connect') }}</p>
                    <nav class="footer__socialNav" aria-labelledby="footer-connect-heading">
                        <ul class="footer__social" role="list">
                            <li class="footer__socialItem">
                                <a
                                    href="{{ $__s['facebook'] ?? '#' }}"
                                    class="footer__socialLink footer__socialLink--facebook"
                                    @if($__ext($__s['facebook'] ?? '#')) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    <span class="footer__socialIcon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><path d="M14 8h3V4h-3c-2.76 0-5 2.24-5 5v3H6v4h3v4h4v-4h3l1-4h-4V9a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.65" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="footer__socialText">Facebook</span>
                                </a>
                            </li>
                            <li class="footer__socialItem">
                                <a
                                    href="{{ $__s['instagram'] ?? '#' }}"
                                    class="footer__socialLink footer__socialLink--instagram"
                                    @if($__ext($__s['instagram'] ?? '#')) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    <span class="footer__socialIcon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="1.65"/><circle cx="12" cy="12" r="3.35" stroke="currentColor" stroke-width="1.65"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>
                                    </span>
                                    <span class="footer__socialText">Instagram</span>
                                </a>
                            </li>
                            <li class="footer__socialItem">
                                <a
                                    href="{{ $__s['youtube'] ?? '#' }}"
                                    class="footer__socialLink footer__socialLink--youtube"
                                    @if($__ext($__s['youtube'] ?? '#')) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    <span class="footer__socialIcon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="12" rx="3" stroke="currentColor" stroke-width="1.65"/><path d="m10 9 5 3-5 3V9Z" fill="currentColor"/></svg>
                                    </span>
                                    <span class="footer__socialText">YouTube</span>
                                </a>
                            </li>
                            <li class="footer__socialItem">
                                <a
                                    href="{{ $__s['tiktok'] ?? '#' }}"
                                    class="footer__socialLink footer__socialLink--tiktok"
                                    @if($__ext($__s['tiktok'] ?? '#')) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    <span class="footer__socialIcon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                                    </span>
                                    <span class="footer__socialText">TikTok</span>
                                </a>
                            </li>
                            <li class="footer__socialItem">
                                <a href="mailto:{{ config('company.email') }}" class="footer__socialLink footer__socialLink--email">
                                    <span class="footer__socialIcon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 6.75A2.25 2.25 0 0 1 6.25 4.5h11.5A2.25 2.25 0 0 1 20 6.75v10.5A2.25 2.25 0 0 1 17.75 19.5H6.25A2.25 2.25 0 0 1 4 17.25V6.75Z" stroke="currentColor" stroke-width="1.65"/><path d="m5.25 7.88 6.35 4.76a1.25 1.25 0 0 0 1.5 0l6.35-4.76" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"/></svg>
                                    </span>
                                    <span class="footer__socialText">Email</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="footer__signup footer__signup--envelope">
                <div class="footer__signupInner">
                    <h3 class="footer__signupTitle">{{ t('footer_signup_title') }}</h3>
                    <p class="footer__signupDesc">{{ t('footer_signup_desc') }}</p>
                    <form id="registryEmailForm" action="#" method="get" class="footer__form" onsubmit="submitFormRegistryEmail('registryEmailForm'); return false;">
                        <label class="visuallyHidden" for="registry_email_input">{{ t('footer_email_label') }}</label>
                        <input id="registry_email_input" type="email" name="registry_email" autocomplete="email" placeholder="{{ t('footer_email_placeholder') }}" aria-label="{{ t('footer_email_label') }}" />
                        <button type="submit" class="footer__submit">{{ t('subscribe') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="footer__grid">
            <section class="footer__col" aria-labelledby="footer-contact-heading">
                <h3 id="footer-contact-heading" class="footer__colTitle">{{ t('footer_contact_info') }}</h3>
                <ul class="footer__contactList">
                    <li class="footer__contactItem">
                        <span class="footer__contactKey">{{ t('address') }}</span>
                        <span class="footer__contactVal">{{ config('company.address') }}</span>
                    </li>
                    <li class="footer__contactItem">
                        <span class="footer__contactKey">{{ t('hotline') }}</span>
                        <a class="footer__contactVal footer__contactLink" href="tel:{{ \App\Helpers\Charactor::removeSpecialCharacters(config('company.hotline')) }}" title="{{ t('footer_call_brand', ['brand' => config('company.sortname')]) }}">{{ config('company.hotline') }}</a>
                    </li>
                    <li class="footer__contactItem">
                        <span class="footer__contactKey">{{ t('email') }}</span>
                        <a class="footer__contactVal footer__contactLink" href="mailto:{{ config('company.email') }}" title="{{ t('footer_email_brand', ['brand' => config('company.sortname')]) }}">{{ config('company.email') }}</a>
                    </li>
                </ul>
            </section>

            <nav class="footer__col" aria-labelledby="footer-support-heading">
                <h3 id="footer-support-heading" class="footer__colTitle">{{ t('footer_support_center') }}</h3>
                <ul class="footer__linkList">
                    <li><a href="/lien-he-hitour" title="{{ t('menu_contact_company', ['brand' => config('company.sortname')]) }}">{{ t('footer_contact_support') }}</a></li>
                    <li><a href="#" title="{{ t('footer_payment_info') }}">{{ t('footer_payment_info') }}</a></li>
                    <li><a href="#" title="{{ t('footer_booking_policy') }}">{{ t('footer_booking_policy') }}</a></li>
                    <li><a href="#" title="{{ t('footer_privacy_policy') }}">{{ t('footer_privacy_policy') }}</a></li>
                </ul>
            </nav>

            <nav class="footer__col" aria-labelledby="footer-eco-heading">
                <h3 id="footer-eco-heading" class="footer__colTitle">{{ config('main.footer.eco_projects.title', 'Dự án Hitour') }}</h3>
                <ul class="footer__linkList">
                    @foreach(config('main.footer.eco_projects.links', []) as $ecoLink)
                        @if(!empty(trim($ecoLink['url'] ?? '')) && !empty(trim($ecoLink['label'] ?? '')))
                            <li>
                                <a
                                    href="{{ trim($ecoLink['url']) }}"
                                    title="{{ $ecoLink['title'] ?? $ecoLink['label'] }}"
                                    target="{{ $ecoLink['target'] ?? '_blank' }}"
                                    rel="{{ $ecoLink['rel'] ?? 'nofollow noopener' }}"
                                >{{ trim($ecoLink['label']) }}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>

        <div class="footer__meta">
            <p class="footer__legal">
                {{ t('footer_legal') }}
            </p>
            @php
                $__footerTrust = array_values(array_filter(config('main.footer.trust_badges', []), static function ($b) {
                    return !empty(trim($b['src'] ?? ''));
                }));
            @endphp
            @if(count($__footerTrust))
                <div class="footer__trustBadges" role="group" aria-label="{{ t('footer_trust_aria') }}">
                    @foreach($__footerTrust as $badge)
                        @php
                            $href = trim($badge['href'] ?? '');
                            $target = trim($badge['target'] ?? '_blank');
                            $rel = trim($badge['rel'] ?? 'noopener noreferrer');
                            $title = trim($badge['title'] ?? $badge['alt'] ?? '');
                        @endphp
                        @if($href !== '')
                            <a
                                class="footer__trustBadge footer__trustBadge--link"
                                href="{{ $href }}"
                                title="{{ $title }}"
                                target="{{ $target }}"
                                rel="{{ $rel }}"
                            >
                                <img
                                    src="{{ trim($badge['src']) }}"
                                    alt="{{ trim($badge['alt'] ?? '') }}"
                                    loading="lazy"
                                    decoding="async"
                                    @if(!empty($badge['width'])) width="{{ (int) $badge['width'] }}" @endif
                                    @if(!empty($badge['height'])) height="{{ (int) $badge['height'] }}" @endif
                                />
                            </a>
                        @else
                            <span class="footer__trustBadge">
                                <img
                                    src="{{ trim($badge['src']) }}"
                                    alt="{{ trim($badge['alt'] ?? '') }}"
                                    loading="lazy"
                                    decoding="async"
                                    @if(!empty($badge['width'])) width="{{ (int) $badge['width'] }}" @endif
                                    @if(!empty($badge['height'])) height="{{ (int) $badge['height'] }}" @endif
                                />
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</footer>
<div class="copyright">
    <div class="container copyright__inner">
        <span class="copyright__line">© {{ date('Y') }} <a href="{{ config('company.website') }}" title="{{ t('menu_home_title', ['brand' => config('company.sortname')]) }}">{{ config('company.sortname') }}</a></span>
        <span class="copyright__credit" aria-hidden="true">·</span>
        <span class="copyright__line">{{ t('footer_dev_credit') }}</span>
    </div>
</div>
