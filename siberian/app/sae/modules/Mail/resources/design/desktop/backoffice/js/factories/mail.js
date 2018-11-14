/**
 *
 */
App.factory('Mail', function($http, Url) {

    let factory = {};

    factory.loadLogs = function() {
        return $http({
            method: 'GET',
            url: Url.get('mail/backoffice_log/load-logs'),
            cache: true,
            responseType: 'json'
        });
    };

    return factory;
});
