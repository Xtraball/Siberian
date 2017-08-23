/* global
    App, angular, _
 */

/**
 * Folder
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Folder', function ($pwaRequest) {
    var factory = {
        value_id: null,
        folder_id: null,
        category_id: null,
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
     * @param category_id
     */
    factory.setCategoryId = function (category_id) {
        factory.category_id = category_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.findAll = function (value_id, category_id, options) {
        var localValueId = (value_id === undefined) ? this.value_id : value_id;
        var localCategoryId = (category_id === undefined) ? this.category_id : category_id;

        if (!localValueId) {
            return $pwaRequest.reject('[Factory::Facebook.findAll] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if ((payload !== false) && (localCategoryId === null)) {
            return $pwaRequest.resolve(payload);
        } else if ((localCategoryId !== null) &&
            (_.find(factory.collection, { category_id: localCategoryId }) !== undefined)) {
            return _.find(factory.collection, { category_id: localCategoryId });
        }

        // Otherwise fallback on PWA!
        return $pwaRequest.get('folder/mobile_list/findallv2', angular.extend({
            urlParams: {
                value_id: localValueId,
                category_id: localCategoryId
            }
        }, factory.extendedOptions, options));
    };

    return factory;
});
