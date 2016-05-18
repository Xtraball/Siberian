
App.factory('ApiKey', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("api/backoffice_key_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("api/backoffice_key_list/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.save = function(api_keys) {

        return $http({
            method: 'POST',
            data: api_keys,
            url: Url.get("api/backoffice_key_list/save"),
            cache: false,
            responseType:'json'
        });

    }

    return factory;
});
