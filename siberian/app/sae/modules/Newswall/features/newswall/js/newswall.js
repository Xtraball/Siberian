/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('newswall-list', {
            url: BASE_PATH + '/comment/mobile_list/index/value_id/:value_id',
            templateUrl: function (param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layout_id) {
                    case 2:
                        layout_id = 'l2';
                        break;
                    case 3:
                        layout_id = 'l5';
                        break;
                    case 4:
                        layout_id = 'l6';
                        break;
                    default:
                        layout_id = 'l1';
                }
                return 'templates/html/' + layout_id + '/list.html';
            },
            controller: 'NewswallListController',
            cache: false,
            resolve: lazyLoadResolver('newswall')
        }).state('newswall-view', {
            url: BASE_PATH + '/comment/mobile_view/index/value_id/:value_id/comment_id/:comment_id',
            templateUrl: 'templates/html/l1/view.html',
            controller: 'NewswallViewController',
            cache: false,
            resolve: lazyLoadResolver('newswall')
        }).state('newswall-comment', {
            url: BASE_PATH + '/comment/mobile_comment/index/value_id/:value_id/comment_id/:comment_id',
            templateUrl: 'templates/html/l1/comment.html',
            controller: 'NewswallCommentController',
            cache: false,
            resolve: lazyLoadResolver('newswall')
        }).state('fanwall-gallery', {
            url: BASE_PATH + '/comment/mobile_gallery/index/value_id/:value_id',
            templateUrl: 'templates/fanwall/l1/gallery.html',
            controller: 'NewswallGalleryController',
            cache: false,
            resolve: lazyLoadResolver('newswall')
        }).state('fanwall-map', {
            url: BASE_PATH + '/comment/mobile_map/index/value_id/:value_id',
            templateUrl: 'templates/html/l1/maps.html',
            controller: 'NewswallMapController',
            cache: false,
            resolve: lazyLoadResolver('newswall')
        }).state('fanwall-edit', {
            url: BASE_PATH + '/comment/mobile_edit/value_id/:value_id',
            templateUrl: 'templates/fanwall/l1/edit.html',
            controller: 'NewswallEditController',
            cache: false,
            resolve: lazyLoadResolver('newswall')
        });
});
