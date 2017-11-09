
App.factory('Push', function($rootScope, $http, httpCache, Url, PUSH_EVENTS) {

    var factory = {};

    factory.value_id = null;
    factory.pushs = 0;
    factory.displayed_per_page = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("push/mobile_list/findall", {value_id: this.value_id, device_uid: Application.device_uid, offset:offset}),
            cache: false,
            responseType:'json'
        }).success(function(data) {

            httpCache.remove(Url.get("push/mobile/count", {device_uid: Application.device_uid}));

            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }

        });
    };

    factory.getPushs = function(device_uid) {

        $http({
            method: 'GET',
            url: Url.get("push/mobile/count", {device_uid: device_uid}),
            cache: true,
            responseType: 'json'
        }).success(function (data) {
            factory.pushs = data.count;
            $rootScope.$broadcast(PUSH_EVENTS.unreadPushs);
        });
    };

    factory.getLastPush = function(device_uid) {
        return $http({
            method: 'GET',
            url: Url.get("push/mobile/findlastpush", {device_uid: device_uid}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.getInAppMessages = function(device_uid) {
        return $http({
            method: 'GET',
            url: Url.get("push/mobile/inapp", {device_uid: device_uid}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.getLastMessages = function(device_uid) {
        return $http({
            method: 'GET',
            url: Url.get("push/mobile/lastmessages", {device_uid: device_uid}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.markInAppAsRead = function(device_uid, device_type) {
        return $http({
            method: 'GET',
            url: Url.get("push/mobile/readinapp", {device_uid: device_uid, device_type: device_type}),
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
