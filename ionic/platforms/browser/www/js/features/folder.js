/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('folder-category-list', {
            url: BASE_PATH + '/folder/mobile_list/index/value_id/:value_id',
            controller: 'FolderListController',
            cache: false,
            templateUrl: function (param) {
                var layoutId = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layoutId) {
                    case 2:
                        layoutId = 'l2';
                        break;
                    case 3:
                        layoutId = 'l3';
                        break;
                    case 4:
                        layoutId = 'l4';
                        break;
                    default:
                        layoutId = 'l1';
                }
                return 'templates/folder/' + layoutId + '/list.html';
            },
            resolve: lazyLoadResolver('folder')
        }).state('folder-subcategory-list', {
            url: BASE_PATH + '/folder/mobile_list/index/value_id/:value_id/category_id/:category_id',
            controller: 'FolderListController',
            cache: false,
            templateUrl: function (param) {
                var layoutId = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layoutId) {
                    case 2:
                        layoutId = 'l2';
                        break;
                    case 3:
                        layoutId = 'l3';
                        break;
                    case 4:
                        layoutId = 'l4';
                        break;
                    default:
                        layoutId = 'l1';
                }
                return 'templates/folder/' + layoutId + '/list.html';
            },
            resolve: lazyLoadResolver('folder')
        });
});
