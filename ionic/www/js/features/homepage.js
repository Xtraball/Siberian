angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('home', {
            url: BASE_PATH,
            templateUrl: 'templates/home/view.html',
            controller: 'HomeController',
            cache: false,
            resolve: lazyLoadResolver('homepage')
        });
});
