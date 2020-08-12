/*global
 angular
 */

/**
 * Country
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("Country", function($pwaRequest, $q) {

    var service = {
        lastError: null,
        fetched: false,
        countries: []
    };

    /**
     * Caching country list to avoid multiple useless requests!
     */
    service.findAll = function() {
        var defer = $q.defer();
        if (!service.fetched) {
            $pwaRequest
                .get("/application/mobile_country/findall")
                .then(function (countries) {
                    service.countries = countries;
                    service.fetched = true;

                    defer.resolve(service.countries);
                }, function (error) {
                    defer.reject(error);
                });
        } else {
            defer.resolve(service.countries);
        }
        return defer.promise;
    };

    return service;
});