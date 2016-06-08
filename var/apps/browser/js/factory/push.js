
App.factory('Push', function($cordovaPush, $http, $rootScope, $translate, $window, Application, httpCache, Url, PUSH_EVENTS) {

    /*
     * PRIVATE
     */
    var __self = {
        push: null,
        device_token: null,
        register: function() {
            __self._init();

            if (__self.push) {
                __self.push.on('registration', function(data) {
                    console.log("device_token: " + data.registrationId);
                    __self.device_token = data.registrationId;
                    __self._registerDevice();
                });

                __self._onNotificationReceived();

                __self.push.on('error', function(e) {
                    console.log(e.message);
                });
            }
        },
        _init: function() {

            if(!$window.PushNotification) return;

            __self.push = PushNotification.init({
                android: {
                    senderID: "01234567890"
                },
                ios: {
                    clearBadge: "true",
                    alert: "true",
                    badge: "true",
                    sound: "true"
                },
                windows: {}
            });
        },
        _registerDevice: function() {
            if(ionic.Platform.isIOS()) {
                __self._registerForIos();
            } else if(ionic.Platform.isAndroid()) {
                __self._registerForAndroid();
            }
        },
        _registerForIos: function() {
            var url = "/push/iphone/registerdevice";

            cordova.getAppVersion.getVersionNumber().then(function(app_version) {
                var device_name = null;
                try {
                    device_name = device.platform;
                } catch(e) {
                    console.log(e.message);
                }

                var device_model = null;
                try {
                    device_model = device.model;
                } catch(e) {
                    console.log(e.message);
                }

                var device_version = null;
                try {
                    device_version = device.version;
                } catch(e) {
                    console.log(e.message);
                }

                var params = {
                    app_id: Application.app_id,
                    app_name: Application.app_name,
                    app_version: app_version,
                    device_uid: factory.device_uid,
                    device_token: __self.device_token,
                    device_name: device_name,
                    device_model: device_model,
                    device_version: device_version,
                    push_badge: "enabled",
                    push_alert: "enabled",
                    push_sound: "enabled"
                };

                $http({
                    method: 'GET',
                    url: Url.get(url, params),
                    cache: false,
                    responseType: 'json'
                });
            });
        },
        _registerForAndroid: function() {
            var url = "/push/android/registerdevice";
            var params = {
                app_id: Application.app_id,
                app_name: Application.app_name,
                device_uid: factory.device_uid,
                registration_id: btoa(__self.device_token)
            };

            $http({
                method: 'GET',
                url: Url.get(url, params),
                cache: false,
                responseType: 'json'
            });
        },
        _onNotificationReceived: function() {
            __self.push.on('notification', function(data) {
                $rootScope.$broadcast(PUSH_EVENTS.notificationReceived, data);

                __self.push.finish(function() {
                    console.log('success');
                }, function() {
                    console.log('error');
                });
            });
        }

    };

    /*
     * PUBLIC
     */
    var factory = {
        value_id: null,
        device_uid: null,
        pushs: 0,
        displayed_per_page: null
    };

    factory.register = function() {
        __self.register();
    };

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("push/mobile_list/findall", {value_id: this.value_id, device_uid: this.device_uid, offset:offset}),
            cache: false,
            responseType:'json'
        }).success(function(data) {

            httpCache.remove(Url.get("push/mobile/count", {device_uid: factory.device_uid}));

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

    factory.getLastMessages = function() {
        return $http({
            method: 'GET',
            url: Url.get("push/mobile/lastmessages", {device_uid: factory.device_uid}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.markInAppAsRead = function() {
        var device_type = ionic.Platform.isIOS() ? 1 : 2;

        return $http({
            method: 'GET',
            url: Url.get("push/mobile/readinapp", {device_uid: factory.device_uid, device_type: device_type}),
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
