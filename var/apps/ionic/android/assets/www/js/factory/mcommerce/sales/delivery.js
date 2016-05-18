App.factory('McommerceSalesDelivery', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findStore = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_delivery/findstore", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };
    
    factory.updateDeliveryInfos = function (form) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_delivery/update", {value_id: this.value_id});
        
        var data = {form: form};

        return $http.post(url, data);
    };
    
    return factory;
});
