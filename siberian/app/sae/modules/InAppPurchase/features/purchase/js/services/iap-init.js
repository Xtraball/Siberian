/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
angular
    .module('starter')
    .service('IAP_Init', function (IAP_Product, IAP_Store, SB) {
        var service = {

        };

        service.onStart = function () {
            if (SB.DEVICE.TYPE_BROWSER === DEVICE_TYPE) {
                console.info('IAP[Support]: InAppPurchase are not available on the WebApp/Browser.');
                return;
            }

            IAP_Store.init();

            // Android/iOS support!
            IAP_Product
                .all()
                .then(function (payload) {
                    IAP_Store.registerCollection(payload.collection);
                }, function (error) {
                    console.log('IAP[Error]: ', error);
                });
        };

        return service;
    });
