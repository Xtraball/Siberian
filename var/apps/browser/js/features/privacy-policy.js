/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('privacy-policy', {
            url: BASE_PATH + '/cms/privacy_policy/index/value_id/:value_id',
            controller: 'PrivacyPolicyController',
            templateUrl: 'templates/cms/privacypolicy/l1/privacy-policy.html',
            cache: false,
            resolve: lazyLoadResolver('privacy_policy')
        });
});
