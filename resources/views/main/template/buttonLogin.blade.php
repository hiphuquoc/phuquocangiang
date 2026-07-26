@if(!empty($user))
    <div class="loginBox" onClick="toggleModalCustomerLoginForm('modalLoginFormCustomerBox');">
        <div class="loginBox_iconAvatar">
            <img src="{{ Storage::url('images/svg/icon-user.svg') }}" alt="{{ t('account_info') }}" title="{{ t('account_info') }}" />
        </div>
        <div class="maxLine_1">{{ $user->name ?? t('account') }}</div>
        <div class="loginBox_list">
            <a href="{{ route('admin.logout') }}" class="loginBox_list_item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <div class="maxLine_1">{{ t('logout') }}</div>
            </a>
        </div>
        <div class="loginBox_background">

        </div>
    </div>
@else 
    <div class="loginBox" onClick="toggleModalCustomerLoginForm('modalLoginFormCustomerBox');">
        <img src="{{ Storage::url('images/svg/sign-in-alt.svg') }}" alt="{{ t('login_brand', ['brand' => config('main.company_name')]) }}" title="{{ t('login_brand', ['brand' => config('main.company_name')]) }}" />
        <div class="maxLine_1">{{ t('login') }}</div>
    </div>
@endif