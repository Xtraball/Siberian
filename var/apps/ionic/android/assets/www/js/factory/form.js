
App.factory('Form', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("form/mobile_view/find", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.post = function (form) {

        if (!this.value_id) return;

        var url = Url.get("form/mobile_view/post", {value_id: this.value_id});
        var data = {form: form};

        return $http.post(url, data);
    };

    return factory;
});
