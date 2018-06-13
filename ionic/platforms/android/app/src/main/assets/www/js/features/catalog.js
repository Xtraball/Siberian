/* global
 App, angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('catalog-category-list', {
            url: BASE_PATH + '/catalog/mobile_category_list/index/value_id/:value_id',
            controller: 'CategoryListController',
            templateUrl: function (param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (HomepageLayoutProvider.getLayoutIdForValueId(param.value_id)) {
                    case 2:
                        layout_id = 'l5';
                        break;
                    case 3:
                        layout_id = 'l6';
                        break;
                    default:
                        layout_id = 'l3';
                }
                return 'templates/html/' + layout_id + '/list.html';
            },
            cache: false,
            resolve: lazyLoadResolver('catalog')
        })
        .state('catalog-product-view', {
            url: BASE_PATH + '/catalog/mobile_category_product_view/index/value_id/:value_id/product_id/:product_id',
            controller: 'CategoryProductViewController',
            templateUrl: 'templates/catalog/category/l1/product/view.html',
            cache: false,
            resolve: lazyLoadResolver('catalog')
        });
});
