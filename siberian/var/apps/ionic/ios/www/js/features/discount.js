/* global
 App, angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('discount-list', {
            url: BASE_PATH + '/promotion/mobile_list/index/value_id/:value_id',
            controller: 'DiscountListController',
            cache: false,
            resolve: lazyLoadResolver('discount'),
            templateUrl: function (param) {
                var layoutId = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                console.log('getting layoutId: ', layoutId);
                switch (layoutId) {
                    case 2:
                        return 'templates/html/l2/list.html';
                    case 3:
                        return 'templates/html/l5/list.html';
                    case 4:
                        return 'templates/html/l6/list.html';
                    case 5:
                        return 'templates/discount/l5/list.html';
                    default: // & case 1
                        return 'templates/discount/l1/list.html';
                }
            }
        }).state('discount-view', {
            url: BASE_PATH + '/promotion/mobile_view/index/value_id/:value_id/promotion_id/:promotion_id',
            controller: 'DiscountViewController',
            templateUrl: 'templates/discount/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('discount')
        });
});
