/* global
    App, angular
 */

/**
 * Form
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Form', function ($pwaRequest) {
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
     * Pre-Fetch feature.
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Form.findAll] missing value_id');
        }

        return $pwaRequest.get('form/mobile_view/find', angular.extend({
            urlParams: {
                value_id: this.value_id
            }
        }, factory.extendedOptions));
    };

    factory.post = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Form.post] missing value_id');
        }

        return $pwaRequest.post('form/mobile_view/post', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                'form': form
            },
            cache: false
        });
    };

    return factory;
});
