<div class="callBookTour_button">
    <div class="callBookTour_button_item hotline">
        @if(!empty($item->staffs)&&$item->staffs->isNotEmpty())
            <div style="display:flex;line-height:1.7;align-items:center;">
                <div>
                    {{ t('callbook_consultant') }}
                </div>
                <div style="margin-left:0.5rem;">
                    @foreach($item->staffs as $staff)
                        <div><span>{{ $staff->infoStaff->phone }}</span> ({{ $staff->infoStaff->prefix_name }} {{ $staff->infoStaff->lastname }})</div>
                    @endforeach
                </div>
            </div>
        @else
            <div style="display:flex;line-height:1.7;align-items:center;">
                <div>
                    {{ t('callbook_consultant') }}
                </div>
                <div style="margin-left:0.75rem;">
                    <div><span>{{ config('company.hotline') }}</span> ({{ t('callbook_hotline_label') }})</div>
                </div>
            </div>
        @endif
    </div>
    @if(!empty($flagButton)&&$flagButton==true)
        @if(!empty($linkFull))
            <a href="{{ $linkFull }}">
                <h2 class="callBookTour_button_item bookonline">
                    {{ $button ?? t('book_now') }}
                </h2>
            </a>
        @else 
            <div>
                <h2 class="callBookTour_button_item bookonline">
                    {{ $button ?? t('book_now') }}
                </h2>
            </div>
        @endif
    @endif
</div>