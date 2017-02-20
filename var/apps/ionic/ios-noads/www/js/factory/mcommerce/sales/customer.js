
App.factory('McommerceSalesCustomer', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.updateCustomerInfos = function (form) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_customer/update", {value_id: this.value_id});
        
        var data = {form: form};
        
        data.option_value_id = this.value_id;

        return $sbhttp.post(url, data);
    };

    factory.find = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_customer/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.hasGuestMode = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_customer/hasguestmode", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.getOrderHistory = function(offset) {
        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_customer/getorders", {value_id: this.value_id, offset: offset}),
            cache: false,
            responseType:'json'
        });
    };

    factory.getOrderDetails = function(order_id) {
        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_customer/getorderdetails", {value_id: this.value_id, order_id: order_id}),
            cache: false,
            responseType:'json'
        });
    };


    return factory;
});
