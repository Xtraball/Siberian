/* global
    App, angular
 */

/**
 * Places
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Places', function ($pwaRequest, Cms) {
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
     * Pre-Fetch feature.
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function (position, offset, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.findAll] missing value_id');
        }

        var parameters = {
            value_id: this.value_id,
            offset: offset
        };

        if (angular.isObject(position)) {
            parameters.latitude = position.latitude;
            parameters.longitude = position.longitude;
        }

        return $pwaRequest.get('places/mobile_list/findall', angular.extend({
            urlParams: parameters,
            refresh: refresh
        }, factory.extendedOptions));
    };

    factory.findAllMaps = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.findAll] missing value_id');
        }

        var parameters = {
            value_id: this.value_id,
            maps: true
        };

        return $pwaRequest.get('places/mobile_list/findall', angular.extend({
            urlParams: parameters,
            refresh: refresh
        }, factory.extendedOptions));
    };


    factory.find = function (place_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.find] missing value_id');
        }

        return $pwaRequest.get('places/mobile_view/find', {
            urlParams: {
                value_id: this.value_id,
                place_id: place_id
            }
        });
    };

    /**
     * Search for place payload inside cached collection
     *
     * @param place_id
     * @returns {*}
     */
    factory.getPlace = function (place_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.getPlace] missing value_id');
        }

        var place = _.get(_.filter(factory.collection, function (item) {
            return (item.id == place_id);
        })[0], 'embed_payload', false);

        if (!place) {
            // Well then fetch it!
            return Cms.findAll(place_id);
        }

        return $pwaRequest.resolve(place);
    };

    factory.settings = function () {
        /* The url and agent must be non-null */
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.settings] missing value_id');
        }

        return $pwaRequest.resolve($pwaRequest.getPayloadForValueId(factory.value_id).settings);
    };

    return factory;
});
