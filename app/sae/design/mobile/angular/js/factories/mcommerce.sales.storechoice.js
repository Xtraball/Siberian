App.factory('McommerceSalesStorechoice', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_storechoice/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.update = function(store_id) {

        if(!this.value_id) return;

        return $http({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_storechoice/update"),
            data: {store_id: store_id},
            cache: false,
            responseType:'json'
        });
    };
    
    return factory;
});
