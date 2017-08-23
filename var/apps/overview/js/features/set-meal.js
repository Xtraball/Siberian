/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('set-meal-list', {
            url: BASE_PATH+'/catalog/mobile_setmeal_list/index/value_id/:value_id',
            controller: 'SetMealListController',
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
            resolve: lazyLoadResolver('catalog')
        }).state('set-meal-view', {
            url: BASE_PATH+'/catalog/mobile_setmeal_view/index/value_id/:value_id/set_meal_id/:set_meal_id',
            controller: 'SetMealViewController',
            templateUrl: 'templates/catalog/setmeal/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('catalog')
        });
});
