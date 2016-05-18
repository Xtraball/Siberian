
App.factory('Backoffice', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/index/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.find = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/index/find"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
