
App.factory('Advanced', function($http, Url) {

    var factory = {};

    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_module/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/advanced_module/findall"),
            cache: false,
            responseType:'json'
        });

    };

    factory.moduleAction = function(module, action) {

        return $http({
            method: 'POST',
            url: Url.get("backoffice/advanced_module/execute"),
            data: {module: module, action: action},
            cache: false,
            responseType:'json'
        });

    };

    return factory;
});
