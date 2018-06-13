angular.module('starter').config(function ($stateProvider, $urlRouterProvider) {
    $stateProvider
        .state('home', {
            url: BASE_PATH,
            templateUrl: 'templates/home/view.html',
            controller: 'HomeController',
            cache: false,
            resolve: lazyLoadResolver('homepage')
        });

    $urlRouterProvider.otherwise(BASE_PATH);
});
