/*global
    App
 */
angular.module("starter").factory("McommerceSalesDelivery", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.findStore = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesDelivery::findStore] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_sales_delivery/findstore", {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };
    
    factory.updateDeliveryInfos = function (form) {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesDelivery::updateDeliveryInfos] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_delivery/update", {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form
            }
        });
    };
    
    return factory;
});
