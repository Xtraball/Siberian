/**
 * Discount
 *
 * @author Xtraball SAS
 * @version 4.17.0
 */
angular
    .module('starter')
    .factory("Discount", function ($pwaRequest) {
        var factory = {
            valueId: null,
            extendedOptions: {}
        };

        /**
         *
         * @param valueId
         */
        factory.setValueId = function (valueId) {
            factory.valueId = valueId;
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
         * @param refresh
         */
        factory.findAll = function (refresh) {
            if (!this.valueId) {
                return $pwaRequest.reject('[Factory::Discount.findAll] missing valueId');
            }

            return $pwaRequest.get('discount/mobile_list/findall', angular.extend({
                urlParams: {
                    valueId: this.valueId
                },
                refresh: refresh
            }, factory.extendedOptions));
        };

        /**
         *
         * @param promotion_id
         * @param refresh
         * @returns {*}
         */
        factory.find = function (promotion_id, refresh) {
            if (!this.valueId) {
                return $pwaRequest.reject('[Factory::Discount.find] missing valueId');
            }

            return $pwaRequest.get('discount/mobile_view/find', {
                urlParams: {
                    valueId: this.valueId,
                    promotion_id: promotion_id
                },
                refresh: refresh
            });
        };

        /**
         *
         * @param promotion_id
         * @returns {*}
         */
        factory.use = function (promotion_id) {
            if (!this.valueId) {
                return $pwaRequest.reject('[Factory::Discount.use] missing valueId');
            }

            return $pwaRequest.post('discount/mobile_list/use', {
                data: {
                    valueId: this.valueId,
                    promotion_id: promotion_id
                },
                cache: false
            });
        };

        /**
         *
         * @param qrcode
         * @returns {*}
         */
        factory.unlockByQRCode = function (qrcode) {
            if (!this.valueId) {
                return $pwaRequest.reject('[Factory::Discount.unlockByQRCode] missing valueId');
            }

            return $pwaRequest.post('discount/mobile_list/unlockByQRCode', {
                data: {
                    valueId: this.valueId,
                    qrcode: qrcode
                },
                cache: false
            });
        };

        return factory;
    });
