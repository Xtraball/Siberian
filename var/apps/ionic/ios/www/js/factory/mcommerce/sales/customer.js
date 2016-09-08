
App.factory('McommerceSalesCustomer', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.updateCustomerInfos = function (form) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_customer/update", {value_id: this.value_id});
        
        var data = {form: form};
        
        data.option_value_id = this.value_id;

        return $http.post(url, data);
    };

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_customer/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
