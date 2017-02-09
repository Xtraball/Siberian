App.factory('Booking', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findStores = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("booking/mobile_view/find", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.post = function(form) {

        if(!this.value_id) return;

        var url = Url.get("booking/mobile_view/post", {value_id: this.value_id});

        var data = {};
        for (var prop in form) {
            data[prop] = form[prop];
        }
        if (data.date) {
            data.date = new Date(data.date).toLocaleString();
        }

        return $sbhttp.post(url, data);
    };

    return factory;
});
