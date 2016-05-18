
App.factory('Event', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;
    factory.displayed_per_page = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("event/mobile_list/findall", {value_id: this.value_id, offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
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
