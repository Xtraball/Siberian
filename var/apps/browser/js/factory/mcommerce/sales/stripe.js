
App.factory('McommerceStripe', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_stripe/find"),
            cache: false,
            responseType:'json'
        });
    };

    factory.process = function(token) {

        if(!this.value_id) return;

        return $http({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_stripe/process"),
            data: {value_id: this.value_id, token: token},
            cache: false,
            responseType:'json'
        });
    };


    return factory;
});
