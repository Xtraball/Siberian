App.factory('Maps', function($rootScope, $q, $http, Url, GoogleMapService, Application) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("maps/mobile_view/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.calculateRoute = function(origin, destination, params) {
        var deferred = $q.defer();
        if(origin) {
            GoogleMapService.calculateRoute(origin, destination, params).then(function(route) {
                deferred.resolve(route);
            }, function(err) {
                deferred.reject(err);
            });
        } else {
            Application.getLocation(function(position) {
                GoogleMapService.calculateRoute(position, destination, params).then(function(route) {
                    deferred.resolve(route);
                }, function(err) {
                    deferred.reject(err);
                });
            }, function(err) {
                deferred.reject("gps_disabled");
            });
        }
        return deferred.promise;
    };

    return factory;
});
