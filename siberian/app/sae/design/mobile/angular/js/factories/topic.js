App.factory('Topic', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;
    factory.device_uid = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("topic/mobile_list/findall", {value_id: this.value_id, device_uid:this.device_uid}),
            cache: false,
            responseType:'json'
        });
    };

    factory.subscribe = function(topic_id, device_uid, subscribe) {
        return $http({
            method: 'POST',
            data: {category_id: topic_id, device_uid: device_uid, subscribe: subscribe},
            url: Url.get("topic/mobile_list/subscribe"),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
