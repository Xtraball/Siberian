App.factory('Tip', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {
        return $sbhttp({
            method: 'GET',
            url: Url.get("tip/mobile_view/findall", {value_id: this.value_id}),
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    return factory;
});
