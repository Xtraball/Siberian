/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('socialgaming-view', {
            url: BASE_PATH + '/socialgaming/mobile_view/index/value_id/:value_id',
            controller: 'SocialgamingViewController',
            templateUrl: 'templates/socialgaming/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('social_gaming')
        });
});
