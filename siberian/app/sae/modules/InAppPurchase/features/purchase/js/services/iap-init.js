/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
angular
    .module('starter')
    .service('IAP_Init', function (IAP_Product) {
        var service = {

        };

        service.onStart = function () {
            IAP_Product
                .all()
                .then(function (payload) {
                    payload.collection.each(function (product) {
                        console.log('IAP[Product]: ', product);
                    });
                }, function (error) {
                    console.log('IAP[Error]: ', error);
                });
        };

        return service;
    });
