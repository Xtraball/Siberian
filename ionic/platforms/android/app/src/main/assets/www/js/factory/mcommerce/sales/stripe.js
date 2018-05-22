/*global
    App
 */
angular.module("starter").factory("McommerceStripe", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.find = function(cust_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::find] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/find", {
            data: {
                customer_id: cust_id
            }
        });
    };

    /**
     * @todo remove sessionStorage.getItem('mcommerce-notes') and use the pwa-cache registry
     *
     * @param data
     */
    factory.process = function(data) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::process] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/process", {
            data: {
                value_id        : this.value_id,
                token           : data["token"],
                customer_id     : data["customer_id"],
                use_stored_card : data["use_stored_card"],
                save_card       : data["save_card"],
                notes           : sessionStorage.getItem('mcommerce-notes') || ""
            }
        });
    };

    factory.getCard = function(customer_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::getCard] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/getcard", {
            data: {
                "value_id": this.value_id,
                "customer_id": customer_id
            }
        });
    };

    factory.removeCard = function(customer_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::removeCard] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/removecard", {
            data: {
                "value_id": this.value_id,
                "customer_id": customer_id
            }
        });
    };

    return factory;
});
