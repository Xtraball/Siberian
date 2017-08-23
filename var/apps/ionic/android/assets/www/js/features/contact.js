/* global
 App, angular, lazyLoadResolver, BASE_PATH, IMAGE_URL
 */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('contact-view', {
            url: BASE_PATH + '/contact/mobile_view/index/value_id/:value_id',
            templateUrl: 'templates/contact/l1/view.html',
            controller: 'ContactViewController',
            cache: false,
            resolve: lazyLoadResolver('contact')
        }).state('contact-form', {
            url: BASE_PATH + '/contact/mobile_form/index/value_id/:value_id',
            templateUrl: 'templates/contact/l1/form.html',
            controller: 'ContactFormController',
            cache: false,
            resolve: lazyLoadResolver('contact')
        }).state('contact-map', {
            url: BASE_PATH + '/contact/mobile_map/index/value_id/:value_id',
            templateUrl: 'templates/html/l1/maps.html',
            controller: 'ContactMapController',
            cache: false,
            resolve: lazyLoadResolver('contact')
        });
});
