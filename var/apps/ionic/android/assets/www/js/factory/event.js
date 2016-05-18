
App.factory('Event', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        var params = {
            value_id: this.value_id,
            offset: offset
        };

        //if(Boolean(last_id)) params.last_id = last_id;
        //if(Boolean(load_newer_posts)) params.load_newer_posts = load_newer_posts;

        return $http({
            method: 'GET',
            url: Url.get("event/mobile_list/findall", params),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findById = function(event_id) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("event/mobile_view/find", {value_id: this.value_id, event_id: event_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
