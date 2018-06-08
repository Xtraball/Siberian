/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('sourcecode-view', {
            url: BASE_PATH + '/sourcecode/mobile_view/index/value_id/:value_id',
            controller: 'SourcecodeViewController',
            templateUrl: 'templates/sourcecode/l1/view.html',
            cache: false,
            resolve: lazyLoadResolver('source_code')
        });
});
