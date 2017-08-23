/* global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('loyaltycard-view', {
            url: BASE_PATH + '/loyaltycard/mobile_view/index/value_id/:value_id',
            cache: false,
            controller: 'LoyaltyViewController',
            templateUrl: 'templates/loyalty-card/l1/view.html',
            resolve: lazyLoadResolver('loyalty_card')
        });
});
