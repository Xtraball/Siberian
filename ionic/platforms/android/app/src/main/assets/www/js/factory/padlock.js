/* global
 App, angular
 */

/**
 * Padlock
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Padlock', function ($pwaRequest) {
    var factory = {
        value_id: null,
        events: {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    factory.onStatusChange = function (id, urls) {
        factory.events[id] = urls;
    };

    factory.flushData = function () {
        for (var i in factory.events) {
            if (angular.isArray(factory.events[i])) {
                var data = factory.events[i];
                for (var j = 0; j < data.length; j++) {
                    // Trigger a cache refresh!
                    $pwaRequest.cache(data[j]);
                }
            }
        }
    };

    factory.findUnlockTypes = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Padlock.findUnlockTypes] missing value_id');
        }

        return $pwaRequest.get('padlock/mobile_view/findunlocktypes', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    factory.find = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Padlock.find] missing value_id');
        }

        return $pwaRequest.get('padlock/mobile_view/find', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    factory.unlockByQRCode = function (qrcode) {
        if (!qrcode) {
            return $pwaRequest.reject('[Factory::Padlock.unlockByQRCode] missing value_id');
        }

        return $pwaRequest.post('padlock/mobile_view/unlockByQRCode', {
            data: {
                qrcode: qrcode
            },
            cache: false
        });
    };

    return factory;
});
