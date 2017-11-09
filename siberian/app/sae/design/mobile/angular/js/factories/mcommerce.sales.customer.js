
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

    return factory;
});
