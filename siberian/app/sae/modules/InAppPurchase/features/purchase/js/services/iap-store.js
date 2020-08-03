/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
angular
    .module('starter')
    .service('IAP_Store', function (SB) {
        var service = {};

        service.init = function () {

        };

        // We register available products!
        service.registerCollection = function (collection) {
            if (store === undefined) {
                console.error('[IAP Store] store is not available.');
                return;
            }

            store.ready(function () {
                collection.forEach(function (product) {
                    store.register({
                        id: DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID ?
                            product.google_id : product.apple_id,
                        alias: product.alias,
                        type: store[product.type]
                    });
                });
            });
        };

        return service;
    });
