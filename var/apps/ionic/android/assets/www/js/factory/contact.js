
App.factory('Contact', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("contact/mobile_view/find", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.post = function(form) {

        if(!this.value_id) return;

        var url = Url.get("/contact/mobile_form/post", {value_id: this.value_id});

        return $sbhttp.post(url, form);
    };

    return factory;
});
