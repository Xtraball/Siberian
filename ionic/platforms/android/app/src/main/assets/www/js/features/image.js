/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('image-list', {
            url: BASE_PATH + '/media/mobile_gallery_image_list/index/value_id/:value_id',
            templateUrl: 'templates/media/image/l1/list.html',
            controller: 'ImageListController',
            cache: false,
            resolve: lazyLoadResolver('image')
        });
});
