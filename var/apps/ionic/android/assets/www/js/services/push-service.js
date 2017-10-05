/*global
    App, DOMAIN, angular, btoa, device, cordova, calculateDistance
 */

/**
 * PushService
 *
 * @author Xtraball SAS
 */
angular.module('starter').service('PushService', function ($cordovaLocalNotification, $location, $log, $q, $rootScope,
                                                           $translate, $window, $session, Application, Dialog,
                                                           LinkService, Pages, Push, SB) {
    var service = {
        push: null,
        settings: {
            android: {
                senderID: '01234567890',
                icon: 'ic_icon',
                iconColor: '#0099C7'
            },
            ios: {
                clearBadge: true,
                alert: true,
                badge: true,
                sound: true
            },
            windows: {}
        }
    };

    /**
     * Configure Push Service
     *
     * @param senderID
     * @param iconColor
     */
    service.configure = function (senderID, iconColor) {
        // senderID error proof for Android!
        if ((Push.device_type === SB.DEVICE.TYPE_ANDROID) &&
            (senderID === '01234567890' || senderID ==='')) {
            $log.debug('Invalid senderId: ' + senderID);
            service.settings.android.senderID = null;
        } else {
            service.settings.android.senderID = senderID;
        }

        // Validating push color!
        if (!(/^#[0-9A-F]{6}$/i).test(iconColor)) {
            $log.debug('Invalid iconColor: ' + iconColor);
        } else {
            service.settings.android.iconColor = iconColor;
        }
    };

    /**
     * If available, initialize push
     */
    service.init = function () {
        if (!$window.PushNotification) {
            return;
        }

        service.push = $window.PushNotification.init(service.settings);
    };

    /**
     * Handle registration, and various push events
     */
    service.register = function () {
        service.init();

        if (service.push && $rootScope.isNativeApp) {
            service.push.on('registration', function (data) {
                $log.debug('device_token: ' + data.registrationId);

                Push.device_token = data.registrationId;
                service.registerDevice();
            });

            service.onNotificationReceived();
            service.push.on('error', function (error) {
                $log.debug(error.message);
            });

            service.updateUnreadCount();

            Application.loaded.then(function () {
                // When Application is loaded, and push registered, look for missed push!
                service.fetchMessagesOnStart();

                // Register for push events!
                $rootScope.$on(SB.EVENTS.PUSH.notificationReceived, function (event, data) {
                    // Refresh to prevent the need for pullToRefresh!
                    var pushFeature = _.filter(Pages.getActivePages(), function (page) {
                        return (page.code === 'push_notification');
                    });
                    if (pushFeature.length >= 1) {
                        Push.setValueId(pushFeature[0].value_id);
                        Push.findAll(0, true);
                    }

                    service.displayNotification(data);
                });
            });
        } else {
            $log.debug('Unable to initialize push service.');
        }
    };

    service.registerDevice = function () {
        switch (Push.device_type) {
            case SB.DEVICE.TYPE_ANDROID:
                service.registerAndroid();
                break;

            case SB.DEVICE.TYPE_IOS:
                service.registerIos();
                break;
        }
    };

    service.registerAndroid = function () {
        var params = {
            app_id: Application.app_id,
            app_name: Application.app_name,
            registration_id: btoa(Push.device_token)
        };
        Push.registerAndroidDevice(params);
    };

    service.registerIos = function () {
        cordova.getAppVersion.getVersionNumber()
            .then(function (appVersion) {
                var deviceName = null;
                try {
                    deviceName = device.platform;
                } catch (e) {
                    $log.debug(e.message);
                }

                var deviceModel = null;
                try {
                    deviceModel = device.model;
                } catch (e) {
                    $log.debug(e.message);
                }

                var deviceVersion = null;
                try {
                    deviceVersion = device.version;
                } catch (e) {
                    $log.debug(e.message);
                }

                var params = {
                    app_id: Application.app_id,
                    app_name: Application.app_name,
                    app_version: appVersion,
                    device_token: Push.device_token,
                    device_name: deviceName,
                    device_model: deviceModel,
                    device_version: deviceVersion,
                    push_badge: 'enabled',
                    push_alert: 'enabled',
                    push_sound: 'enabled'
                };

                Push.registerIosDevice(params);
            });
    };

    service.onNotificationReceived = function () {
        service.push.on('notification', function (data) {
            if (data.additionalData.longitude && data.additionalData.latitude) {
                var callbackCurrentPosition = function (result) {
                    var distance_in_km = calculateDistance(
                        result.latitude,
                        result.longitude,
                        data.additionalData.latitude,
                        data.additionalData.longitude,
                        'K'
                    );

                    if (distance_in_km <= data.additionalData.radius) {
                        if (Push.device_type === SB.DEVICE.TYPE_IOS) {
                            data.title = data.additionalData.user_info.alert.body;
                            data.message = data.title;
                        }

                        service.sendLocalNotification(data.additionalData.message_id, data.title, data.message);

                        $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, data);
                    } else {
                        service.addProximityAlert(data);
                    }
                };

                var callbackErrCurrentPosition = function (err) {
                    $log.debug(err.message);

                    service.addProximityAlert(data);
                };

                if (Push.device_type === SB.DEVICE.TYPE_IOS) {
                    $window.BackgroundGeolocation
                        .getCurrentPosition(function (location, taskId) {
                            location.latitude = location.coords.latitude;
                            location.longitude = location.coords.longitude;

                            callbackCurrentPosition(location);
                            $window.BackgroundGeolocation.finish(taskId);
                        }, callbackErrCurrentPosition);
                } else {
                    // Get the user current position when app on foreground!
                    $window.BackgroundGeoloc.getCurrentPosition(callbackCurrentPosition, callbackErrCurrentPosition);
                }
            } else {
                $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, data);
            }

            service.push.finish(function () {
                $log.debug('push finish success');
                // success!
            }, function () {
                $log.debug('push finish error');
                // error!
            });
        });
    };

    service.startBackgroundGeolocation = function () {
        if (!$window.BackgroundGeolocation) {
            $log.debug('unable to find BackgroundGeolocation plugin.');
            return;
        }

        switch (Push.device_type) {
            case SB.DEVICE.TYPE_IOS:

                $log.debug('-- iOS StartBackgroundLocation --');
                service.startIosBackgroundGeolocation();

                break;

            case SB.DEVICE.TYPE_ANDROID:

                $log.debug('-- ANDROID StartBackgroundLocation --');

                $window.BackgroundGeoloc.startBackgroundLocation(function (result) {
                    // Android only!
                    var proximity_alerts = JSON.parse(localStorage.getItem('proximity_alerts'));
                    if (proximity_alerts !== null) {
                        angular.forEach(proximity_alerts, function (value, index) {
                            var alert = value;

                            var distance_in_km = calculateDistance(result.latitude,
                                result.longitude, alert.additionalData.latitude, alert.additionalData.longitude, 'K');
                            if (distance_in_km <= alert.additionalData.radius) {
                                var current_date = Date.now();
                                var push_date = new Date(alert.additionalData.send_until).getTime();

                                if (!push_date || (push_date >= current_date)) {
                                    service.sendLocalNotification(alert.additionalData.message_id, alert.title, alert.message);

                                    $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, alert);
                                }

                                proximity_alerts.splice(index, 1);
                            }
                        });

                        localStorage.setItem('proximity_alerts', JSON.stringify(proximity_alerts));
                    } else {
                        $window.BackgroundGeoloc.stopBackgroundLocation();
                    }
                }, function (err) {
                    $log.debug('error to startLocation: ' + err);
                });

                break;
        }
    };

    service.startIosBackgroundGeolocation = function () {
        // This callback will be executed every time a geolocation is recorded in the background!
        var callbackFn = function (location, taskId) {
            var coords = location.coords;
            var lat = coords.latitude;
            var lng = coords.longitude;
            $log.debug('- Location: ', JSON.stringify(location));

            // Must signal completion of your callbackFn.
            $window.BackgroundGeolocation.finish(taskId);
        };

        // This callback will be executed if a location-error occurs.  Eg: this will be called if user disables location-services.
        var failureFn = function (errorCode) {
            $log.debug('- BackgroundGeoLocation error: ', errorCode);
        };

        $window.BackgroundGeolocation.onGeofence(function (params, taskId) {
            try {
                // var location  = params.location;
                var identifier = params.identifier;
                var message_id = identifier.replace('push', '');
                var action = params.action;

                $log.debug('A geofence has been crossed: ', identifier);
                $log.debug('ENTER or EXIT ?: ', action);

                // Remove the geofence
                $window.BackgroundGeolocation.removeGeofence(identifier);

                // Remove the stored proximity alert
                var proximity_alerts = JSON.parse(localStorage.getItem('proximity_alerts'));
                if (proximity_alerts !== null) {
                    angular.forEach(proximity_alerts, function (value, index) {
                        var alert = value;

                        if (message_id === alert.additionalData.message_id) {
                            var current_date = Date.now();
                            var push_date = new Date(alert.additionalData.send_until).getTime();

                            if (!push_date || (push_date >= current_date)) {
                                alert.title = alert.additionalData.user_info.alert.body;
                                alert.message = alert.title;

                                service.sendLocalNotification(alert.additionalData.message_id, alert.title, alert.message);

                                $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, alert);
                            }

                            proximity_alerts.splice(index, 1);
                        }
                    });

                    localStorage.setItem('proximity_alerts', JSON.stringify(proximity_alerts));
                }
            } catch (e) {
                $log.debug('An error occurred in my application code', e);
            }

            $window.BackgroundGeolocation.finish(taskId);
        });

        // BackgroundGeoLocation is highly configurable!
        $window.BackgroundGeolocation.configure({
            // Geolocation config!
            desiredAccuracy: 0,
            distanceFilter: 10,
            stationaryRadius: 50,
            locationUpdateInterval: 1000,
            fastestLocationUpdateInterval: 5000,

            // Activity Recognition config!
            activityType: 'AutomotiveNavigation',
            activityRecognitionInterval: 5000,
            stopTimeout: 5,

            // Disable aggressive GPS!
            disableMotionActivityUpdates: true,

            // Block mode!
            useSignificantChangesOnly: true,

            // Application config!
            debug: false,
            stopOnTerminate: true,
            startOnBoot: true
        }, function (state) {
            $log.debug('BackgroundGeolocation ready: ', state);
            if (!state.enabled) {
                $window.BackgroundGeolocation.start();
            }
        });
    };

    /**
     * Geofencing for iOS/Background geolocation for Android
     *
     * @param data
     */
    service.addProximityAlert = function (data) {
        $log.debug('-- Adding a proximity alert --');

        var proximityAlerts = localStorage.getItem('proximity_alerts');
        var jsonProximityAlerts = JSON.parse(proximityAlerts);

        if (jsonProximityAlerts === null) {
            jsonProximityAlerts = [];
            jsonProximityAlerts.push(data);
            localStorage.setItem('proximity_alerts', JSON.stringify(jsonProximityAlerts));
        } else {
            var index = proximityAlerts.indexOf(JSON.stringify(data));
            if (index === -1) {
                jsonProximityAlerts.push(data);
                localStorage.setItem('proximity_alerts', JSON.stringify(jsonProximityAlerts));
            }
        }

        switch (Push.device_type) {
            case SB.DEVICE.TYPE_IOS:

                    $log.debug('-- iOS --');
                    $window.BackgroundGeolocation.addGeofence({
                        identifier: 'push' + data.additionalData.message_id,
                        radius: Number.parseInt(data.additionalData.radius * 1000, 10),
                        latitude: data.additionalData.latitude,
                        longitude: data.additionalData.longitude,
                        notifyOnEntry: true
                    }, function () {
                        $log.debug('Successfully added geofence');
                    }, function (error) {
                        $log.debug('Failed to add geofence', error);
                    });

                break;

            case SB.DEVICE.TYPE_ANDROID:

                    $log.debug('-- ANDROID --');
                    service.startBackgroundGeolocation();

                break;
        }
    };

    /**
     * Update push badge.
     */
    service.updateUnreadCount = function () {
        Push.updateUnreadCount()
            .then(function (data) {
                Push.unread_count = data.unread_count;
                $rootScope.$broadcast(SB.EVENTS.PUSH.unreadPush);
            });
    };

    /**
     * LocalNotification wrapper.
     *
     * @param messageId
     * @param title
     * @param message
     */
    service.sendLocalNotification = function (messageId, title, message) {
        $log.debug('-- Push-Service, sending a Local Notification --');

        var localMessage = angular.copy(message);
        if (Push.device_type === SB.DEVICE.TYPE_IOS) {
            localMessage = '';
        }

        var params = {
            id: messageId,
            title: title,
            text: localMessage
        };

        if (Push.device_type === SB.DEVICE.TYPE_ANDROID) {
            params.icon = 'res://icon.png';
        }

        $cordovaLocalNotification.schedule(params);

        Push.markAsDisplayed(messageId);
    };

    /**
     * Trying to fetch latest Push & InApp messages on app Start.
     */
    service.fetchMessagesOnStart = function () {
        Push.getLastMessages(false)
            .then(function (data) {
                // Last push!
                var push = data.push_message;
                if (push) {
                    service.displayNotification(push);
                }

                // Last InApp Message!
                var inappMessage = data.inapp_message;
                if (inappMessage) {
                    inappMessage.type = 'inapp';
                    inappMessage.message = inappMessage.text;
                    inappMessage.config = {
                        buttons: [
                            {
                                text: $translate.instant('OK'),
                                type: 'button-custom',
                                onTap: function () {
                                    Push.markInAppAsRead();
                                }
                            }
                        ]
                    };

                    if ((inappMessage.cover !== null) && (inappMessage.additionalData === undefined)) {
                        inappMessage.additionalData = {
                            cover: inappMessage.cover
                        };
                    }

                    service.displayNotification(inappMessage);
                }
        });
    };

    /**
     * Displays a notification to the user
     *
     * @param {Object} messagePayload

     * @returns Promise
     */
    service.displayNotification = function (messagePayload) {
        $log.debug('PUSH messagePayload', messagePayload);

        // Prevent an ID being shown twice.
        $session.getItem('pushMessageIds')
            .then(function (pushMessageIds) {
                var localPushMessageIds = pushMessageIds;
                if (pushMessageIds === null || !Array.isArray(pushMessageIds)) {
                    localPushMessageIds = [];
                }

                var messageId = parseInt(messagePayload.additionalData.message_id, 10);
                if (localPushMessageIds.indexOf(messageId) === -1) {
                    // Store acknowledged messages in localstorage.
                    localPushMessageIds.push(messageId);
                    $session.setItem('pushMessageIds', localPushMessageIds);

                    var extendedPayload = messagePayload.additionalData;

                    if ((extendedPayload !== undefined) &&
                        (extendedPayload.cover || extendedPayload.action_value)) {
                        // Prevent missing or not base url!
                        var coverUri = extendedPayload.cover;
                        try {
                            if (coverUri.indexOf('http') !== 0) {
                                coverUri = DOMAIN + extendedPayload.cover;
                            }
                        } catch (e) {
                            // No cover!
                        }

                        var isInAppMessage = ((messagePayload.type !== undefined) &&
                            (messagePayload.type === 'inapp'));

                        var config = {
                            buttons: [
                                {
                                    text: $translate.instant('Cancel'),
                                    type: 'button-custom',
                                    onTap: function () {
                                        if (isInAppMessage) {
                                            Push.markInAppAsRead();
                                        }
                                        // Simply closes!
                                    }
                                },
                                {
                                    text: $translate.instant('View'),
                                    type: 'button-custom',
                                    onTap: function () {
                                        if (isInAppMessage) {
                                            Push.markInAppAsRead();
                                        }

                                        if ((extendedPayload.open_webview !== true) &&
                                            (extendedPayload.open_webview !== 'true')) {
                                            $location.path(extendedPayload.action_value);
                                        } else {
                                            LinkService.openLink(extendedPayload.action_value);
                                        }
                                    }
                                }
                            ],
                            cssClass: 'push-popup',
                            title: messagePayload.title,
                            template:
                            '<div class="list card">' +
                                '<div class="item item-image' + (extendedPayload.cover ? '' : ' ng-hide') + '">' +
                                    '<img src="' + (coverUri) + '">' +
                                '</div>' +
                                '<div class="item item-custom">' +
                                    '<span>' + messagePayload.message + '</span>' +
                                '</div>' +
                            '</div>'
                        };

                        if (messagePayload.config !== undefined) {
                            config = angular.extend(config, messagePayload.config);
                        }

                        // Handles case with only a cover image!
                        if ((extendedPayload.action_value === undefined) ||
                            (extendedPayload.action_value === '') ||
                            (extendedPayload.action_value === null)) {
                            config.buttons = [
                                {
                                    text: $translate.instant('OK'),
                                    type: 'button-custom',
                                    onTap: function () {
                                        // Simply closes!
                                    }
                                }
                            ];
                        }

                        $log.debug('Message payload (ionicPopup):', messagePayload, config);
                        Dialog.ionicPopup(config);
                    } else {
                        var localTitle = (messagePayload.title !== undefined) ?
                            messagePayload.title : 'Notification';
                        $log.debug('Message payload (alert):', messagePayload);
                        Dialog.alert(localTitle, messagePayload.message, 'OK');
                    }

                    // Search for less resource consuming maybe use Push factory directly!
                    $rootScope.$broadcast(SB.EVENTS.PUSH.unreadPushs, messagePayload.count);
                }

                // Nope!
                $log.debug('Will not display duplicated message: ', messagePayload);
            }).catch(function (err) {
                // we got an error
                $log.debug('We got an error with the localForage when trying to display push message: ', messagePayload);
                $log.debug(err);
            });
    };

    return service;
});
