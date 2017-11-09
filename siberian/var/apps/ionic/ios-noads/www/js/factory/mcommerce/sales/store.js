/*global
    App
 */
angular.module("starter").factory("McommerceSalesStorechoice", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.find = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesStorechoice::find] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_sales_storechoice/find", {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.update = function(store_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesStorechoice::update] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_storechoice/update", {
            data: {
                store_id: store_id
            }
        });
    };
    
    return factory;
});
