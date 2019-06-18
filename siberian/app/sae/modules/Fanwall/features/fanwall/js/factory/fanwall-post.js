/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular.module("starter").factory("FanwallPost", function ($pwaRequest) {
    var factory = {
        value_id: null,
        displayed_per_page: 0,
        extendedOptions: {},
        collection: []
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     */
    factory.preFetch = function () {
    };

    factory.findAll = function (offset, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::FanwallPost.findAll] missing value_id");
        }

        return $pwaRequest.get("fanwall/mobile_list/find-all", angular.extend({
            urlParams: {
                value_id: this.value_id,
                offset: offset
            },
            refresh: refresh
        }, factory.extendedOptions));
    };


    return factory;
});