/*global
    App
 */
angular.module("starter").factory("McommerceProduct", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.find = function(product_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceProduct::find] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_product/find", {
            urlParams: {
                value_id: this.value_id,
                product_id: product_id
            }
        });
    };

    return factory;
});
