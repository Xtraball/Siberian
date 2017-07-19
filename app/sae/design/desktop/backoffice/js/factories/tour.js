
App.factory('Tour', function($http, Url) {

    var factory = {};

    factory.load = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/tour_settings/load"),
            cache: false,
            responseType:'json'
        });
    };

    factory.loginAs = function(email) {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/tour_settings/loginas", {"email": email}),
            cache: false,
            responseType:'json'
        });
    };

    factory.setStatus = function(status) {
        return $http({
            method: 'POST',
            data: {"status": status},
            url: Url.get("backoffice/tour_settings/setstatus"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
