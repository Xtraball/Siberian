App.factory('Radio', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function(product_id) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("radio/mobile_radio/find", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
