/*global
 angular
 */

/**
 * Country
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("Country", function($pwaRequest) {

    var service = {};

    service.findAll = function() {
        return $pwaRequest.get("/application/mobile_country/findall");
    };

    return service;
});