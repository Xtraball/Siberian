/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
angular
    .module('starter')
    .service('IAP_Init', function (IAP_Product, SB) {
        var service = {

        };

        service.onStart = function () {
            if (SB.DEVICE.TYPE_BROWSER === DEVICE_TYPE) {
                console.info('IAP[Support]: InAppPurchase are not available on the WebApp/Browser.');
                return;
            }
            // Android/iOS support!
            IAP_Product
                .all()
                .then(function (payload) {
                    payload.collection.forEach(function (product) {
                        console.log('IAP[Product]: ', product);
                    });
                }, function (error) {
                    console.log('IAP[Error]: ', error);
                });
        };

        return service;
    });
