/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('form-view', {
            url: BASE_PATH + '/form/mobile_view/index/value_id/:value_id',
            controller: 'FormViewController',
            templateUrl: 'templates/form/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('form')
        });
});
