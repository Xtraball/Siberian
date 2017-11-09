
App.factory('Booking', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findStores = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("booking/mobile_view/find", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.post = function(form) {

        if(!this.value_id) return;

        var url = Url.get("booking/mobile_view/post", {value_id: this.value_id});

        return $http.post(url, form);
    };

    return factory;
});
