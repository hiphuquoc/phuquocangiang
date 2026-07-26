<!-- FERRY SECTION -->
@php
  $ferrySection = $island['ferry'] ?? ['head' => [], 'routes' => []];
  $ferryHead = $ferrySection['head'] ?? [];
  $shipRoutes = $ferrySection['routes'] ?? [];
@endphp
@if(!empty($shipRoutes))
<section class="sd-section sd-section--alt" id="ferry">
  <div class="sd-section__inner">
    @include('superdong.ui.section-head', [
      'eyebrow' => $ferryHead['eyebrow'] ?? 'Superdong Speed Ferry',
      'title' => $ferryHead['title'] ?? '',
      'desc' => $ferryHead['desc'] ?? '',
      'linkHref' => $ferryHead['linkHref'] ?? '#ferry',
      'linkLabel' => $ferryHead['linkLabel'] ?? 'Xem tất cả tuyến →',
    ])

    @include('superdong.sections.listing.ferry-routes', [
      'routes' => $shipRoutes,
    ])
  </div>
</section>
@endif
