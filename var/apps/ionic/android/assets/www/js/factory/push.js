App.factory('Push', function($cordovaGeolocation, $cordovaLocalNotification, $cordovaPush, $log, $sbhttp, $rootScope, $translate, $window, Application, httpCache, Url, PUSH_EVENTS) {

    /*
     * PRIVATE
     */
    var __self = {
        push: null,
        device_token: null,
        init_data: {
            android: {
                senderID: "01234567890",
                icon: "ic_icon",
                iconColor: "#0099C7"
            },
            ios: {
                clearBadge: "true",
                alert: "true",
                badge: "true",
                sound: "true"
            },
            windows: {}
        },
        register: function() {
            __self._init();

            if (__self.push) {
                __self.push.on('registration', function(data) {
                    $log.debug("device_token: " + data.registrationId);
                    __self.device_token = data.registrationId;
                    __self._registerDevice();
                });

                __self._onNotificationReceived();

                __self.push.on('error', function(e) {
                    $log.debug(e.message);
                });
            }
        },
        startBackgroundGeolocation: function() {
            //if plugin not available do not geolocate
            if(!window.BackgroundGeolocation) {
                return;
            }
            if(ionic.Platform.isIOS()) {
                $log.debug("-- iOS StartBackgroundLocation --");

                __self._startIosBackgroundGeolocation();
            } else if(ionic.Platform.isAndroid()) {
                $log.debug("-- ANDROID StartBackgroundLocation --");

                BackgroundGeoloc.startBackgroundLocation(function(result) {
                    // Android only
                    var proximity_alerts = JSON.parse(localStorage.getItem("proximity_alerts"));
                    if(proximity_alerts != null) {
                        angular.forEach(proximity_alerts, function(value, index) {
                            var alert = value;

                            var distance_in_km = __self._calculateDistance(result.latitude, result.longitude, alert.additionalData.latitude, alert.additionalData.longitude, "K");
                            if(distance_in_km <= alert.additionalData.radius) {
                                var current_date = new Date().getTime();
                                var push_date = new Date(alert.additionalData.send_until).getTime();

                                if(push_date == 0 || push_date >= current_date) {
                                    __self._sendLocalNotification(alert.additionalData.message_id, alert.title, alert.message);

                                    $rootScope.$broadcast(PUSH_EVENTS.notificationReceived, alert);
                                }

                                proximity_alerts.splice(index, 1);
                            }
                        });

                        localStorage.setItem("proximity_alerts", JSON.stringify(proximity_alerts));
                    } else {
                        BackgroundGeoloc.stopBackgroundLocation();
                    }
                }, function(err) {
                    $log.debug("error to startLocation: " + err);
                });
            }
        },
        _startIosBackgroundGeolocation: function() {
            //This callback will be executed every time a geolocation is recorded in the background.
            var callbackFn = function(location, taskId) {
                var coords = location.coords;
                var lat    = coords.latitude;
                var lng    = coords.longitude;
                $log.debug('- Location: ', JSON.stringify(location));

                // Must signal completion of your callbackFn.
                window.BackgroundGeolocation.finish(taskId);
            };

            // This callback will be executed if a location-error occurs.  Eg: this will be called if user disables location-services.
            var failureFn = function(errorCode) {
                $log.debug('- BackgroundGeoLocation error: ', errorCode);
            };

            window.BackgroundGeolocation.onGeofence(function(params, taskId) {
                try {
                    var location = params.location;
                    var identifier = params.identifier;
                    var message_id = identifier.replace("push","");
                    var action = params.action;

                    $log.debug('A geofence has been crossed: ', identifier);
                    $log.debug('ENTER or EXIT?: ', action);

                    // remove the geofence
                    window.BackgroundGeolocation.removeGeofence(identifier);

                    // remove the stored proximity alert
                    var proximity_alerts = JSON.parse(localStorage.getItem("proximity_alerts"));
                    if(proximity_alerts != null) {
                        angular.forEach(proximity_alerts, function(value, index) {
                            var alert = value;

                            if(message_id == alert.additionalData.message_id) {
                                var current_date = new Date().getTime();
                                var push_date = new Date(alert.additionalData.send_until).getTime();

                                if(push_date == 0 || push_date >= current_date) {
                                    alert.title = alert.additionalData.user_info.alert.body;
                                    alert.message = alert.title;

                                    __self._sendLocalNotification(alert.additionalData.message_id, alert.title, alert.message);

                                    $rootScope.$broadcast(PUSH_EVENTS.notificationReceived, alert);
                                }

                                proximity_alerts.splice(index, 1);
                            }
                        });

                        localStorage.setItem("proximity_alerts", JSON.stringify(proximity_alerts));
                    }
                } catch(e) {
                    $log.debug('An error occurred in my application code', e);
                }

                // The plugin runs your callback in a background-thread:
                // you MUST signal to the native plugin when your callback is finished so it can halt the thread.
                // IF YOU DON'T, iOS WILL KILL YOUR APP
                window.BackgroundGeolocation.finish(taskId);
            });

            // BackgroundGeoLocation is highly configurable.
            window.BackgroundGeolocation.configure({
                // Geolocation config
                desiredAccuracy: 0,
                distanceFilter: 10,
                stationaryRadius: 50,
                locationUpdateInterval: 1000,
                fastestLocationUpdateInterval: 5000,

                // Activity Recognition config
                activityType: 'AutomotiveNavigation',
                activityRecognitionInterval: 5000,
                stopTimeout: 5,

                // Disable aggressive GPS
                disableMotionActivityUpdates: true,

                // Block mode
                useSignificantChangesOnly: true,

                // Application config
                debug: false,
                stopOnTerminate: true,
                startOnBoot: true
            }, function(state) {
                $log.debug('BackgroundGeolocation ready: ', state);
                if (!state.enabled) {
                    window.BackgroundGeolocation.start();
                }
            });
        },
        _init: function() {

            if(!$window.PushNotification) {
                return;
            }

            /** senderID not set. */
            if(ionic.Platform.isAndroid() && (__self.init_data.android.senderID == "01234567890" || __self.init_data.android.senderID == "")) {
                __self.init_data.android.senderID = null;
            }

            if(ionic.Platform.isIOS() || ionic.Platform.isAndroid()) {
                __self.push = PushNotification.init(__self.init_data);
            }

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
                    $log.debug(e.message);
                }

                var device_model = null;
                try {
                    device_model = device.model;
                } catch(e) {
                    $log.debug(e.message);
                }

                var device_version = null;
                try {
                    device_version = device.version;
                } catch(e) {
                    $log.debug(e.message);
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

                $sbhttp({
                    method: 'POST',
                    url: Url.get(url),
                    data: params,
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

            $sbhttp({
                method: 'POST',
                url: Url.get(url),
                data: params,
                cache: false,
                responseType: 'json'
            });
        },
        _onNotificationReceived: function() {
            __self.push.on('notification', function(data) {
                if(data.additionalData.longitude && data.additionalData.latitude) {

                    var callbackCurrentPosition = function(result) {
                        var distance_in_km = __self._calculateDistance(result.latitude, result.longitude, data.additionalData.latitude, data.additionalData.longitude, "K");

                        if(distance_in_km <= data.additionalData.radius) {
                            if(ionic.Platform.isIOS()) {
                                data.title = data.additionalData.user_info.alert.body;
                                data.message = data.title;
                            }

                            __self._sendLocalNotification(data.additionalData.message_id, data.title, data.message);

                            $rootScope.$broadcast(PUSH_EVENTS.notificationReceived, data);
                        } else {
                            __self._addProximityAlert(data);
                        }
                    };

                    var callbackErrCurrentPosition = function(err) {
                        $log.debug(err.message);

                        __self._addProximityAlert(data);
                    };

                    if(ionic.Platform.isIOS()) {
                        window.BackgroundGeolocation.getCurrentPosition(function(location, taskId) {
                            location.latitude = location.coords.latitude;
                            location.longitude = location.coords.longitude;

                            callbackCurrentPosition(location);
                            window.BackgroundGeolocation.finish(taskId);
                        }, callbackErrCurrentPosition);
                    } else {
                        // Get the user current position when app on foreground
                        BackgroundGeoloc.getCurrentPosition(callbackCurrentPosition, callbackErrCurrentPosition);
                    }
                } else {
                    $rootScope.$broadcast(PUSH_EVENTS.notificationReceived, data);
                }

                __self.push.finish(function() {
                    // success
                }, function() {
                    // error
                });
            });
        },
        _sendLocalNotification: function(p_message_id, p_title, p_message) {
            $log.debug("-- Sending a Local Notification --");

            if(ionic.Platform.isIOS()) p_message = "";

            var params = {
                id: p_message_id,
                title: p_title,
                text: p_message
            };

            if(ionic.Platform.isAndroid()) params.icon = "res://icon.png";

            // Send Local Notification
            $cordovaLocalNotification.schedule(params);

            factory.markAsDisplayed(p_message_id);
        },
        _addProximityAlert: function(data) {
            $log.debug("-- Adding a proximity alert --");

            var proximity_alerts = localStorage.getItem("proximity_alerts");
            var json_proximity_alerts = JSON.parse(proximity_alerts);

            if(json_proximity_alerts == null) {
                json_proximity_alerts = [];
                json_proximity_alerts.push(data);
                localStorage.setItem("proximity_alerts", JSON.stringify(json_proximity_alerts));
            } else {
                var index = proximity_alerts.indexOf(JSON.stringify(data));
                if(index == -1) {
                    json_proximity_alerts.push(data);
                    localStorage.setItem("proximity_alerts", JSON.stringify(json_proximity_alerts));
                }
            }

            if(ionic.Platform.isIOS()) {
                $log.debug("-- iOS --");

                window.BackgroundGeolocation.addGeofence({
                    identifier: "push" + data.additionalData.message_id,
                    radius: parseInt(data.additionalData.radius * 1000),
                    latitude: data.additionalData.latitude,
                    longitude: data.additionalData.longitude,
                    notifyOnEntry: true
                }, function() {
                    $log.debug("Successfully added geofence");
                }, function(error) {
                    $log.debug("Failed to add geofence", error);
                });
            } else {
                $log.debug("-- ANDROID --");

                __self.startBackgroundGeolocation();
            }
        },
        _calculateDistance: function(lat1, lon1, lat2, lon2, unit) {
            var radlat1 = Math.PI * lat1/180;
            var radlat2 = Math.PI * lat2/180;
            var theta = lon1-lon2;
            var radtheta = Math.PI * theta/180;
            var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
            dist = Math.acos(dist);
            dist = dist * 180/Math.PI;
            dist = dist * 60 * 1.1515;
            if (unit=="K") { dist = dist * 1.609344 }
            if (unit=="N") { dist = dist * 0.8684 }
            return dist;
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

    Object.defineProperty($rootScope, "device_uid", {
        get: function() {
            return factory.device_uid;
        }
    }); // symbolic link to bypass dependency injection for Application service

    factory.setSenderID = function(senderID) {
        __self.init_data.android.senderID = senderID;
    };

    factory.setIconColor = function(iconColor) {
        __self.init_data.android.iconColor = iconColor;
    };

    factory.register = function() {
        __self.register();
    };

    factory.startBackgroundGeolocation = function() {
        __self.startBackgroundGeolocation();
    };

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("push/mobile_list/findall", {value_id: this.value_id, device_uid: this.device_uid, offset:offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {

            httpCache.remove(Url.get("push/mobile/count", {device_uid: factory.device_uid}));

            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }

        });
    };

    factory.getPushs = function(device_uid) {

        $sbhttp({
            method: 'GET',
            url: Url.get("push/mobile/count", {device_uid: device_uid}),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        }).success(function (data) {
            factory.pushs = data.count;
            $rootScope.$broadcast(PUSH_EVENTS.unreadPushs);
        });
    };

    factory.getInAppMessages = function(device_uid) {
        return $sbhttp({
            method: 'GET',
            url: Url.get("push/mobile/inapp", {device_uid: device_uid}),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.getLastMessages = function() {
        return $sbhttp({
            method: 'GET',
            url: Url.get("push/mobile/lastmessages", {device_uid: factory.device_uid}),
            cache: false,
            responseType: 'json',
            timeout: 10000
        });
    };

    factory.markInAppAsRead = function() {
        var device_type = ionic.Platform.isIOS() ? 1 : 2;

        return $sbhttp({
            method: 'GET',
            url: Url.get("push/mobile/readinapp", {device_uid: factory.device_uid, device_type: device_type}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.markAsDisplayed = function(message_id) {
        var device_type = ionic.Platform.isIOS() ? "iphone" : "android";

        return $sbhttp({
            method: 'GET',
            url: Url.get("push/" + device_type + "/markdisplayed", {device_uid: factory.device_uid, message_id: message_id}),
            cache: false,
            responseType: 'json'
        });
    };

    return factory;
});
