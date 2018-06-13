/* global
    App, angular
 */

/**
 * Discount
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Discount', function ($pwaRequest) {
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

    /**
     * @todo move the Tc.find elsewhere.
     * @param refresh
     */
    factory.findAll = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Discount.findAll] missing value_id');
        }

        return $pwaRequest.get('promotion/mobile_list/findall', angular.extend({
                urlParams: {
                    value_id: this.value_id
                },
                refresh: refresh
            }, factory.extendedOptions));
    };

    factory.find = function (promotion_id, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Discount.find] missing value_id');
        }

        return $pwaRequest.get('promotion/mobile_view/find', {
            urlParams: {
                value_id: this.value_id,
                promotion_id: promotion_id
            },
            refresh: refresh
        });
    };

    factory.use = function (promotion_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Discount.use] missing value_id');
        }

        return $pwaRequest.post('promotion/mobile_list/use', {
            data: {
                value_id: this.value_id,
                promotion_id: promotion_id
            },
            cache: false
        });
    };

    factory.unlockByQRCode = function (qrcode) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Discount.unlockByQRCode] missing value_id');
        }

        return $pwaRequest.post('promotion/mobile_list/unlockByQRCode', {
            data: {
                value_id: this.value_id,
                qrcode: qrcode
            },
            cache: false
        });
    };

    return factory;
});
