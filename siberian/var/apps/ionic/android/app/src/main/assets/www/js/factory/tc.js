/*global
 App, device, angular
 */

/**
 * Tc
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Tc", function($pwaRequest) {

    var factory = {
        id              : null,
        extendedOptions : {}
    };

    /**
     *
     * @param id
     */
    factory.setId = function(id) {
        factory.id = id;
    };

    factory.find = function(tc_id, refresh) {

        if(!factory.id || !tc_id) {
            $pwaRequest.reject("[Factory::Tc.find] missing factory.id or tc_id");
        }

        return $pwaRequest.get("application/mobile_tc_view/find", {
            urlParams: {
                tc_id: tc_id || factory.id
            },
            refresh: refresh
        });
    };

    return factory;
});
