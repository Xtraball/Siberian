/* global
    App, angular
 */

/**
 * Event
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Event', function ($pwaRequest) {
    var factory = {
        value_id: null,
        collection: [],
        extendedOptions: {}
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
     * Custom Page
     *
     * @todo preload only the second page, next pages are preloaded only once furthermore, to reduce data usage.
     *
     * @param page
     */
    factory.preFetch = function () {
        factory.findAll(0);
    };

    factory.findAll = function (offset, refresh) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Event.findAll] missing value_id');
        }

        return $pwaRequest.get('event/mobile_list/findall',
            angular.extend({
                urlParams: {
                    value_id: this.value_id,
                    offset: offset
                },
                refresh: refresh,
                timeout: 30000
            }, factory.extendedOptions)
        );
    };

    factory.findById = function (event_id, refresh) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Event.findById] missing value_id');
        }

        return $pwaRequest.get('event/mobile_view/find', {
            urlParams: {
                value_id: this.value_id,
                event_id: event_id
            },
            refresh: refresh,
            timeout: 30000
        });
    };

    /**
     * Search for event payload inside cached collection
     *
     * @param event_id
     * @returns {*}
     */
    factory.getEvent = function (event_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Event.getEvent] missing value_id');
        }

        var event = _.get(_.filter(factory.collection, function (item) {
            return (item.id == event_id);
        })[0], 'embed_payload', false);

        if (!event) {
            /** Well then fetch it. */
            return factory.findById(event_id);
        }

        return $pwaRequest.resolve(event);
    };

    return factory;
});
