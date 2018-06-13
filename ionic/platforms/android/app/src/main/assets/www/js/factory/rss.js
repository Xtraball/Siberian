/* global
 App, device, angular
 */

/**
 * Rss
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Rss', function ($pwaRequest) {
    var factory = {
        value_id: null,
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
     *
     * @param page
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Rss.findAll] missing value_id');
        }

        return $pwaRequest.get('rss/mobile_feed_list/findall', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.find = function (feed_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Rss.find] missing value_id');
        }

        return $pwaRequest.get('rss/mobile_feed_view/find', {
            urlParams: {
                value_id: this.value_id,
                feed_id: feed_id
            }
        });
    };

    /**
     * Search for feed payload inside cached collection
     *
     * @param feed_id
     * @returns {*}
     */
    factory.getFeed = function (feed_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Rss.getFeed] missing value_id');
        }

        var feed = _.get(_.filter(factory.collection, function (item) {
            return (item.id == feed_id);
        })[0], 'embed_payload', false);

        if (!feed) {
            return factory.find(feed_id);
        }
        return $pwaRequest.resolve(feed);
    };


    return factory;
});
