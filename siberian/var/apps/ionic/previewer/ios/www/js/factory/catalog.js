/* global
    App, angular
 */

/**
 * Catalog
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Catalog', function ($pwaRequest) {
    var factory = {
        value_id: null,
        last_category: null,
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
     * @param category
     */
    factory.setLastCategory = function (category) {
        factory.last_category = category;
    };

    /**
     */
    factory.getLastCategory = function () {
        return factory.last_category;
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

    factory.findAll = function (refresh) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Catalog.findAll] missing value_id');
        }

        return $pwaRequest.get('catalog/mobile_category_list/findall',
            angular.extend({
                urlParams: {
                    value_id: this.value_id
                },
                refresh: refresh
            }, factory.extendedOptions)
        );
    };

    factory.find = function (product_id) {
        if (!this.value_id) {
            $pwaRequest.reject('[Factory::Catalog.find] missing value_id');
        }

        return $pwaRequest.get('catalog/mobile_category_product_view/find', {
            urlParams: {
                value_id: this.value_id,
                product_id: product_id
            }
        });
    };

    /**
     * Search for product payload inside cached collection
     *
     * @param product_id
     * @returns {*}
     */
    factory.getProduct = function (product_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Catalog.getProduct] missing value_id');
        }

        var product = _.get(_.filter(factory.collection, function (product) {
            return (product.id == product_id);
        })[0], 'embed_payload', false);

        if (!product) {
            /** Well then fetch it. */
            return factory.find(product_id);
        }

        return $pwaRequest.resolve(product);
    };

    return factory;
});
