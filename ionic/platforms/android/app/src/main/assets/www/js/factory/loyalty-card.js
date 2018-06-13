/* global
 App, angular
 */

/**
 * LoyaltyCard
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('LoyaltyCard', function ($pwaRequest) {
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

    factory.findAll = function (refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::LoyaltyCard.findAll] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            return $pwaRequest.resolve(payload);
        }

        // Otherwise fallback on PWA!
        return $pwaRequest.get('loyaltycard/mobile_view/findall',
            angular.extend({
                urlParams: {
                    value_id: this.value_id
                },
                refresh: refresh
            }, factory.extendedOptions)
        );
    };

    factory.validate = function (pad) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::LoyaltyCard.validate] missing value_id');
        }

        return $pwaRequest.post('loyaltycard/mobile_view/validate', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                customer_card_id: pad.card.id,
                number_of_points: pad.number_of_points,
                password: pad.password,
                mode_qrcode: pad.mode_qrcode
            },
            cache: false
        });
    };

    return factory;
});
