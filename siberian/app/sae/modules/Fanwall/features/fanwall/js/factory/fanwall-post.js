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

    factory.loadSettings = function () {
        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        // Otherwise fallback on PWA!
        return $pwaRequest.get("fanwall/mobile_list/load-settings", {
            urlParams: {
                value_id: factory.value_id
            }
        });
    };

    factory.like = function (postId) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::FanwallPost.like] missing value_id");
        }

        return $pwaRequest.post("fanwall/mobile_list/like-post", angular.extend({
            urlParams: {
                value_id: this.value_id,
                postId: postId
            }
        }, factory.extendedOptions));
    };

    factory.unlike = function (postId) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::FanwallPost.like] missing value_id");
        }

        return $pwaRequest.post("fanwall/mobile_list/unlike-post", angular.extend({
            urlParams: {
                value_id: this.value_id,
                postId: postId
            }
        }, factory.extendedOptions));
    };


    return factory;
});