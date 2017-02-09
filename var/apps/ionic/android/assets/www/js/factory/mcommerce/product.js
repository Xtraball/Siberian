
App.factory('McommerceProduct', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function(product_id) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_product/find", {value_id: this.value_id, product_id: product_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
