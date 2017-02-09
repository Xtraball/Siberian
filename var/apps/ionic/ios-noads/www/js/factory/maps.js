App.factory('Maps', function($rootScope, $q, $sbhttp, Url, GoogleMaps/*, Application*/) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("maps/mobile_view/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
