<!-- TOURS SECTION -->
@include('superdong.sections.tours.grid', [
  'toursSection' => $island['tours'] ?? ['head' => [], 'items' => []],
])
