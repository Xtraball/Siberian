
App.factory('Catalog', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("catalog/mobile_category_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function(product_id) {

        if(!this.value_id) return;

        var url = Url.get('catalog/mobile_category_product_view/find', {value_id: this.value_id, product_id: product_id});

        return $http({
            method: 'GET',
            url: url,
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
