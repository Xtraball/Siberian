
App.factory('Sourcecode', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("sourcecode/mobile_view/find", {value_id: this.value_id}),
            cache: false,
            responseType:'json'
        });
    };
    
    return factory;
});
