/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('wordpress-list', {
            url: BASE_PATH + '/wordpress/mobile_list/index/value_id/:value_id',
            templateUrl: function (param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layout_id) {
                    case 2:
                        layout_id = 'l5';
                        break;
                    case 3:
                        layout_id = 'l6';
                        break;
                    default: // 1
                        layout_id = 'l3';
                }
                return 'templates/html/' + layout_id + '/list.html';
            },
            controller: 'WordpressListController',
            cache: false,
            resolve: lazyLoadResolver('wordpress')
        }).state('wordpress-view', {
            url: BASE_PATH + '/wordpress/mobile_view/index/value_id/:value_id/post_id/:post_id/offset/:offset',
            templateUrl: 'templates/wordpress/l1/view.html',
            controller: 'WordpressViewController',
            cache: false,
            resolve: lazyLoadResolver('wordpress')
        });
});
