@if(!empty($facts))
  <ul class="sd-card__facts" role="list">
    @foreach($facts as $fact)
      @php
        if (!empty($fact['text_key'])) {
          $factLabel = t($fact['text_key'], $fact['text_params'] ?? []);
        } else {
          $factLabel = $fact['text'] ?? '';
        }
      @endphp
      <li>
        @switch($fact['icon'] ?? 'tag')
          @case('pin')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            @break
          @case('clock')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            @break
          @case('bed')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/></svg>
            @break
          @case('ship')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M13 4v8m0 0 3-3m-3 3-3-3"/></svg>
            @break
          @default
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        @endswitch
        <span class="maxLine_1">{{ $factLabel }}</span>
      </li>
    @endforeach
  </ul>
@endif
