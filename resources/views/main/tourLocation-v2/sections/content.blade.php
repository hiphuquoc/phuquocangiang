@if(!empty($content))
@include('main.listing-v2.sections.content-prose', [
  'content' => $content,
  'eyebrow' => t('tour_guide_intro_kicker'),
  'title' => t('tour_guide_intro_title', ['name' => $locationName ?? '']),
  'lede' => t('tour_guide_intro_lede'),
  'titleId' => 'tour-location-content-title',
  'tocSub' => t('tour_guide_intro_kicker'),
  'sectionClass' => 'sd-listing-content sd-tour-location-content',
])
@endif
