
App.factory('McommerceStripe', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function(cust_id) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_stripe/find"),
            data: {customer_id: cust_id},
            cache: false,
            responseType:'json'
        });
    };

    factory.process = function(data) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_stripe/process"),
            data: {
                value_id: this.value_id,
                token: data["token"],
                customer_id: data["customer_id"],
                use_stored_card: data["use_stored_card"],
                save_card: data["save_card"],
                notes: sessionStorage.getItem('mcommerce-notes') || ""
            },
            cache: false,
            responseType:'json'
        });
    };

    factory.getCard = function(customer_id) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_stripe/getcard"),
            data: {"value_id": this.value_id, "customer_id": customer_id},
            cache: false,
            responseType:'json'
        });
    };

    factory.removeCard = function(customer_id) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_stripe/removecard"),
            data: {"value_id": this.value_id, "customer_id": customer_id},
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
