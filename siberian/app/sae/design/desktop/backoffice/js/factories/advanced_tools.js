
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

    factory.runtest = function (params) {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_tools/runtest'),
            params: params || {},
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

    factory.migrateSessions = function () {
        return $http({
            method: 'GET',
            url: Url.get('backoffice/advanced_tools/migratetoredis'),
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
