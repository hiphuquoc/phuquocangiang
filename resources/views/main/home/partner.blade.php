@if(!empty($list)&&$list->isNotEmpty())
    @php
        $partnerVariant = $variant ?? 'partners';
        $partnerHeadingId = 'home-partners-heading-' . $partnerVariant;
        $partnerLabel = $label ?? null;
    @endphp
    <section class="homePartners homePartners--{{ $partnerVariant }}" aria-labelledby="{{ $partnerHeadingId }}">
        <header class="homePartners_head">
            <div class="homePartners_headRow">
                @if(!empty($partnerLabel))
                    <p class="homePartners_label">{{ $partnerLabel }}</p>
                @endif
                <h2 id="{{ $partnerHeadingId }}" class="homePartners_title">{{ $title ?? '' }}</h2>
            </div>
            @if(!empty($description))
                <p class="homePartners_desc">{{ $description }}</p>
            @endif
        </header>
        <ul class="homePartners_grid" role="list">
            @foreach($list as $partnerItem)
                <li class="homePartners_cell">
                    <a
                        class="homePartners_link"
                        href="/{{ $partnerItem->seo->slug_full ?? '' }}"
                        title="{{ $partnerItem->name ?? $partnerItem->seo->title ?? $partnerItem->seo->seo_title ?? '' }}"
                    >
                        <span class="homePartners_logoWrap">
                            <img
                                src="{{ config('main.svg.loading_main') }}"
                                data-src="{{ $partnerItem->seo->image_small ?? $partnerItem->seo->image ?? config('admin.images.default_750x460') }}"
                                alt="{{ $partnerItem->name ?? $partnerItem->seo->title ?? $partnerItem->seo->seo_title ?? '' }}"
                                decoding="async"
                                width="160"
                                height="80"
                            />
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
