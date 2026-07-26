@extends('superdong.layout.app')

@section('content')
@include('superdong.chrome.topbar')
@include('superdong.sections.hero.shell')
@include('superdong.sections.trust')

<main id="main">
  @include('main.home-v2.sections.quick')
  @include('main.home-v2.sections.ferry')
  @include('main.home-v2.sections.tours')
  @include('main.home-v2.sections.hotels')
  @include('main.home-v2.sections.services')
  @include('superdong.sections.travel-guide')
  @include('superdong.sections.vehicle-rental')
  @include('superdong.sections.gallery')
  @include('superdong.sections.reviews')
  @include('superdong.sections.faq')
  @include('superdong.sections.cta')
</main>

@include('superdong.chrome.footer')
@include('superdong.chrome.float')
@endsection
