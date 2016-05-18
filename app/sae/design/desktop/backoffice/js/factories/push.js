
App.factory('Push', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("push/backoffice_certificate/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("push/backoffice_certificate/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.save = function(keys) {

        return $http({
            method: 'POST',
            data: keys,
            url: Url.get("push/backoffice_certificate/save"),
            responseType:'json'
        });
    };

    return factory;
});
