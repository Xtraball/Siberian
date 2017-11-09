
App.factory('AdvancedTools', function ($http, Url) {
    var factory = {};

    factory.loadData = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_tools/load'),
            cache: true,
            responseType: 'json'
        });
    };

    factory.runtest = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_tools/runtest'),
            cache: false,
            responseType: 'json'
        });
    };

    factory.restoreapps = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_tools/restoreapps'),
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
