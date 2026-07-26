@if(!empty($breadcrumb))
   <!-- === START:: Breadcrumb === -->
   <div class="breadcrumbBox">
      <div class="container maxLine_1">
         <a href="{{ home_url() }}" title="{{ t('breadcrumb_home') }}" class="breadcrumbBox_home">{{ t('breadcrumb_home') }}</a>
         @for($i=0;$i<count($breadcrumb);++$i)
            @if($i!=(count($breadcrumb)-1))
               <a href="{{ seo_url($breadcrumb[$i]) }}" title="{{ $breadcrumb[$i]->title }}">{{ $breadcrumb[$i]->title ?? null }}</a>
            @else
               <span>{{ $breadcrumb[$i]->title }}</span>
            @endif
         @endfor
      </div>
   </div>
   <!-- === END:: Breadcrumb === -->
@endif