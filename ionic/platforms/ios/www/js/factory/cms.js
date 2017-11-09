/* global
    App, angular
 */

/**
 * Cms
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Cms', function ($pwaRequest) {
    var factory = {
        value_id: null,
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
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function (page_id, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Cms.findAll] missing value_id');
        }

        return $pwaRequest.get('cms/mobile_page_view/findall',
            angular.extend({
                urlParams: {
                    value_id: this.value_id,
                    page_id: page_id
                },
                refresh: refresh
            }, factory.extendedOptions)
        );
    };

    factory.find = function (page_id, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Cms.find] missing value_id');
        }

        return $pwaRequest.get('cms/mobile_page_view/find', {
            urlParams: {
                value_id: this.value_id,
                page_id: page_id
            },
            refresh: refresh
        });
    };

    factory.findBlock = function (block_id, page_id, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Cms.findBlock] missing value_id');
        }

        return $pwaRequest.get('cms/mobile_page_view/findblock', {
            urlParams: {
                block_id: block_id,
                page_id: page_id,
                value_id: this.value_id
            },
            refresh: refresh
        });
    };

    return factory;
});
