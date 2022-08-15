/**
 * Places
 *
 * @author Xtraball SAS
 * @version 4.18.5
 */
angular
.module('starter')
.factory('Places', function ($pwaRequest) {
    var factory = {
        value_id: null,
        collection: [],
        mapCollection: [],
        extendedOptions: {},
        settings: []
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
     *
     * @param collection
     */
    factory.setMapCollection = function (collection) {
        factory.mapCollection = collection;
    };

    /**
     * Pre-Fetch feature.
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    /**
     * @param filters
     * @param refresh
     */
    factory.findAll = function (filters, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.findAll] missing value_id');
        }

        filters.value_id = this.value_id;

        return $pwaRequest.get('places/mobile_list/findall', angular.extend({
            urlParams: filters,
            refresh: refresh
        }, factory.extendedOptions));
    };

    factory.findAllMaps = function (filters, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.findAll] missing value_id');
        }

        return $pwaRequest.post('places/mobile_list/find-all-maps', angular.extend({
            urlParams: {
                value_id: this.value_id
            },
            data: filters,
            refresh: refresh
        }, factory.extendedOptions));
    };

    /**
     *
     * @param placeId
     */
    factory.find = function (placeId) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.find] missing value_id');
        }

        return $pwaRequest.get('places/mobile_list/find-one', {
            urlParams: {
                value_id: this.value_id,
                place_id: placeId
            }
        });
    };

    /**
     *
     * @param placeId
     * @param note
     * @returns {*}
     */
    factory.createNote = function (placeId, note) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.createNote] missing value_id');
        }

        return $pwaRequest.post('places/mobile_view/save-note', angular.extend({
            urlParams: {
                value_id: this.value_id
            },
            data: {
                place_id: placeId,
                note: note
            }
        }, factory.extendedOptions));
    };

    /**
     *
     * @param placeId
     * @param noteId
     */
    factory.deleteNote = function (placeId, noteId) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.deleteNote] missing value_id');
        }

        return $pwaRequest.post('places/mobile_view/delete-note', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                place_id: placeId,
                note_id: noteId
            }
        });
    };

    factory.findNotes = function (placeId) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.findNotes] missing value_id');
        }

        return $pwaRequest.post('places/mobile_list/find-notes', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                place_id: placeId
            },
            refresh: true
        });
    };

    /**
     * Search for place payload inside cached collection
     *
     * @param placeId
     * @returns {*}
     */
    factory.getPlace = function (placeId) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Places.getPlace] missing value_id');
        }

        var merged = _.union(factory.collection, factory.mapCollection);

        var place = _.get(_.filter(merged, function (item) {
            return (item.id == placeId);
        })[0], 'embed_payload', false);

        if (!place) {
            // Well then fetch it!
            return factory.find(placeId);
        }

        return $pwaRequest.resolve(place);
    };

    return factory;
});
