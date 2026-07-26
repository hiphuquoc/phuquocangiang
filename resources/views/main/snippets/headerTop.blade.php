<div class="headerTop">
   <div class="container">
      <div class="headerTop_item">
         <div class="headerTop_item_hotline">
            <i class="fa-solid fa-phone"></i>hotline<span>{{ config('company.hotline') }}</span>
         </div>
      </div>
      <div class="headerTop_item">
         <div class="headerTop_item_list">
            {{-- <a href="#" class="headerTop_item_list_item">
               Tư vấn khách hàng
            </a> --}}
            {{-- <a href="/admin" title="Đăng nhập" class="headerTop_item_list_item">
               Đăng nhập
            </a> --}}
            <div id="js_checkLoginAndSetShow_button" class="headerTop_item_list_item js_toggleModalLogin"><div class="loginBox" onclick="toggleModalCustomerLoginForm('modalLoginFormCustomerBox');">
               <img src="/storage/images/svg/sign-in-alt.svg" alt="{{ t('login') }}" title="{{ t('login') }}" />
               <div class="maxLine_1">{{ t('login') }}</div>
               </div>
            </div>
         </div>
         {{-- ============================================================
              REGION SWITCHER (LANGUAGE + CURRENCY trong 1 dropdown)
              Markup + JS được đóng gói trong partial regionSwitcher để
              dùng chung giữa headerTop (desktop) và headerMain (mobile).
              Mỗi nơi nhận 1 variant khác nhau (desktop / mobile).
              ============================================================ --}}
         <div class="headerTop_item_region">
            @include('main.snippets.regionSwitcher', ['variant' => 'desktop'])
         </div>
      </div>
   </div>
</div>
