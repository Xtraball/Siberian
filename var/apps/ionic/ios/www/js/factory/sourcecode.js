
App.factory('Sourcecode', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("sourcecode/mobile_view/find", {value_id: this.value_id}),
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    return factory;
});
