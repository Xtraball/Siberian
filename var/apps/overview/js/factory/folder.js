/*global
    App, angular, _
 */

/**
 * Folder
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Folder", function($pwaRequest) {

    var factory = {
        value_id    : null,
        folder_id   : null,
        category_id : null,
        collection  : [],
        extendedOptions: {}
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
     * @param category_id
     */
    factory.setCategoryId = function(category_id) {
        factory.category_id = category_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    factory.findAll = function(value_id, category_id, options) {

        value_id = (value_id === undefined) ? this.value_id : value_id;
        category_id = (category_id === undefined) ? this.category_id : category_id;

        if(!value_id) {
            return $pwaRequest.reject("[Factory::Facebook.findAll] missing value_id");
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if((payload !== false) && (category_id === null)) {

            return $pwaRequest.resolve(payload);

        } else if((category_id !== null) && (_.find(factory.collection, {category_id: category_id}) !== undefined)) {

            return _.find(factory.collection, {category_id: category_id});

        } else {

            /** Otherwise fallback on PWA */
            return $pwaRequest.get("folder/mobile_list/findallv2", angular.extend({
                urlParams: {
                    value_id    : value_id,
                    category_id : category_id
                }
            }, factory.extendedOptions, options));

        }

    };

    return factory;
});
