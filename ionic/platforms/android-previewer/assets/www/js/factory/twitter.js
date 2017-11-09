/*global
 App, angular, device
 */

/**
 * Twitter
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Twitter", function($pwaRequest) {

    var factory = {
        value_id        : null,
        last_id         : null,
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
        factory.loadData();
    };

    factory.loadData = function () {

        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Twitter.loadData] missing value_id");
        }

        var data = {
            value_id: this.value_id
        };

        if (this.last_id) {
            data.last_id = this.last_id;
        }

        /** @todo Limit cache for twitter */
        return $pwaRequest.get("twitter/mobile_twitter/list", angular.extend({
            urlParams: data,
            withCredentials: false
        }, factory.extendedOptions));
    };

    factory.getInfo = function () {

        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Twitter.getInfo] missing value_id");
        }

        return $pwaRequest.get("twitter/mobile_twitter/info", {
            urlParams: {
                value_id: this.value_id
            },
            withCredentials: false
        });
    };

    return factory;
});