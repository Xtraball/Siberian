App.factory('Topic', function($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;
    factory.device_uid = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("topic/mobile_list/findall", { value_id: this.value_id, device_uid: this.device_uid }),
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    factory.subscribe = function(topic_id, is_subscribed) {
        return $sbhttp({
            method: 'POST',
            data: { category_id: topic_id, device_uid: factory.device_uid, subscribe: is_subscribed },
            url: Url.get("topic/mobile_list/subscribe"),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
