/*global
 App, device, angular
 */

/**
 * Wordpress
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Wordpress", function($pwaRequest) {

    var factory = {
        value_id        : null,
        post_id         : null,
        collection      : null,
        extendedOptions : {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param page
     */
    factory.preFetch = function(page) {
        factory.findAll();
    };

    factory.findAll = function(offset) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::Wordpress.findAll] missing value_id");
        }

        return $pwaRequest.get("wordpress/mobile_list/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                offset      : offset
            }
        }, factory.extendedOptions));
    };

    return factory;
});
