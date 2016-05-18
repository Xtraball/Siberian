
App.factory('Tip', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {
        return $http({
            method: 'GET',
            url: Url.get("tip/mobile_view/findall", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
