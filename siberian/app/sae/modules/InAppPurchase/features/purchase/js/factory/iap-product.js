/**
 * InAppPurchase
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
angular
    .module('starter')
    .factory('IAP_Product', function ($pwaRequest) {

        var factory = {};

        factory.all = function () {
            return $pwaRequest.get('inapppurchase/mobile_product/all');
        };

        return factory;
    });
