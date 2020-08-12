/**
 * Rss
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Rss", function ($pwaRequest) {
    var factory = {
        value_id: null,
        feed_id: null,
        extendedOptions: {},
        collection: []
    };

    /**
     * @param valueId
     */
    factory.setValueId = function (valueId) {
        factory.value_id = valueId;
    };

    /**
     * @param feedId
     */
    factory.setFeedId = function (feedId) {
        factory.feed_id = feedId;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.getFeeds = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Rss.getFeeds] missing value_id");
        }

        return $pwaRequest.get("rss/mobile_rss/feeds", angular.extend({
            urlParams: {
                value_id: this.value_id,
                refresh: refresh
            },
            cache: false
        }, factory.extendedOptions));
    };

    factory.getGroupedFeeds = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Rss.getGroupedFeeds] missing value_id");
        }

        return $pwaRequest.get("rss/mobile_rss/grouped-feeds", angular.extend({
            urlParams: {
                value_id: this.value_id,
                refresh: refresh
            },
            cache: false
        }, factory.extendedOptions));
    };

    factory.getSingleFeed = function (feedId, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject("[Factory::Rss.getSingleFeed] missing value_id");
        }

        return $pwaRequest.get("rss/mobile_rss/single-feed", {
            urlParams: {
                value_id: this.value_id,
                refresh: refresh,
                feedId: feedId
            },
            cache: false
        });
    };

    /**
     * Search for feed payload inside cached collection
     *
     * @param itemId
     * @returns {*}
     */
    factory.findItem = function (itemId) {
        var item = _.filter(factory.collection, function (item) {
            return (item.id == itemId);
        })[0];

        return $pwaRequest.resolve(item);
    };

    return factory;
});
