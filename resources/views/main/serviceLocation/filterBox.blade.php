<div class="hide-767">
    <div class="filterBox">
        <div class="filterBox_label">
            {{ t('filter_by') }}:
        </div>
        <div class="filterBox_filter">
            <div class="filterBox_filter_item active" onClick="filterServiceTicket(this, 'tat-ca-ve');">
                <div>{{ t('filter_all') }}</div>
            </div>
            <div class="filterBox_filter_item" onClick="filterServiceTicket(this, 've-giam-gia');">
                <h3>{{ t('filter_on_sale') }}</h3>
            </div>
            <div class="filterBox_filter_item" onClick="filterServiceTicket(this, 've-danh-gia-cao');">
                <h3>{{ t('filter_top_rated') }}</h3>
            </div>
        </div>
        <div class="filterBox_view">
            <div class="filterBox_view_item" onClick="filterServiceTicketView(this, 'list');">
                <i class="fa-solid fa-table-list"></i>
            </div>
            <div class="filterBox_view_item active" onClick="filterServiceTicketView(this, 'grid');">
                <i class="fa-solid fa-table-cells"></i>
            </div>
        </div>
    </div>
</div>
@push('scripts-custom')
    <script type="text/javascript">
        (function () {
            var catalogId = 'js_serviceFilter_parent';
            var hiddenId = 'js_serviceFilter_hidden';

            function getCatalog() {
                return document.getElementById(catalogId);
            }

            function getLoader() {
                if (typeof jQuery === 'undefined') return jQuery();
                return jQuery('[data-page-fragment][data-fragment-section="services"]').first().prev('.loadingGridBox--filter');
            }

            window.filterServiceTicket = function (elementButton, type) {
                if (typeof jQuery === 'undefined') return;
                jQuery(elementButton).parent().children().removeClass('active');
                jQuery(elementButton).addClass('active');

                var $loader = getLoader();
                $loader.css('display', 'flex');
                $loader.siblings('.loadingGridBox_note').css('display', 'none');

                var $parent = jQuery('#' + catalogId);
                var $hidden = jQuery('#' + hiddenId);
                $parent.children().css('display', 'none');

                var data = [];
                var dataHidden = [];
                var $scope = jQuery('[data-page-fragment][data-fragment-section="services"] .pageFragment_content [data-filter-ticket]');

                if (type === 'tat-ca-ve') {
                    $scope.each(function () { data.push(jQuery(this)); });
                } else {
                    $scope.each(function () {
                        var valueFilter = jQuery(this).data('filter-ticket');
                        if ((valueFilter || '').indexOf(type) !== -1) {
                            data.push(jQuery(this));
                        } else {
                            dataHidden.push(jQuery(this));
                        }
                    });
                }

                setTimeout(function () {
                    $loader.css('display', 'none');
                    if (data.length === 0) {
                        $loader.siblings('.loadingGridBox_note').css('display', 'block');
                    } else {
                        $parent.html('');
                        for (var i = 0; i < data.length; ++i) {
                            $parent.append(data[i].attr('style', '').clone());
                        }
                        $hidden.html('');
                        for (var j = 0; j < dataHidden.length; ++j) {
                            $hidden.append(dataHidden[j].clone());
                        }
                    }
                    if (typeof window.lazyLoadImages === 'function') {
                        window.lazyLoadImages($parent);
                    }
                }, 600);
            };

            window.filterServiceTicketView = function (elementButton, type) {
                if (typeof jQuery === 'undefined') return;
                jQuery(elementButton).parent().children().removeClass('active');
                jQuery(elementButton).addClass('active');
                var $catalog = jQuery('#' + catalogId);
                var $loader = getLoader();
                if (type === 'list') {
                    $catalog.removeClass('tourGrid');
                    $loader.addClass('loadingListBox');
                } else if (type === 'grid') {
                    $catalog.addClass('tourGrid');
                    $loader.removeClass('loadingListBox');
                }
            };

            document.addEventListener('DOMContentLoaded', function () {
                var catalog = getCatalog();
                if (catalog) catalog.classList.add('tourGrid');
            });
        })();
    </script>
@endpush
