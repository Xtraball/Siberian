/**
 * PushService
 *
 * @author Xtraball SAS
 *
 * @version 5.0.0
 */
angular
    .module('starter')
    .service('PushService', function ($cordovaLocalNotification, $location, $log, $q, $rootScope, $translate,
                                      $window, $session, Application, Dialog, LinkService, Pages, Push, SB) {
    var service = {
        appId: null,
        push: null,
        isEnabled: true,
        settings: {
            android: {
                icon: 'ic_icon',
                iconColor: '#0099C7',
                sound: true,
                soundname: 'sb_beep4',
                vibrate: true
            },
            ios: {
        //        clearBadge: false,
        //        critical: Application.useCriticalPush,
        //        alert: true,
        //        badge: true,
        //        sound: true,
        //        soundname: 'sb_beep4',
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
    service.configure = function (appId, iconColor) {
        service.appId = appId;

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
        if (!$window.plugins.OneSignal) {
            $log.error("OneSignal plugin is missing");
            return;
        }

        //service.push = $window.PushNotification.init(service.settings);

        // Uncomment to set OneSignal device logging to VERBOSE
        $window.plugins.OneSignal.setLogLevel(6, 0);

        // NOTE: Update the setAppId value below with your OneSignal AppId.
        $window.plugins.OneSignal.setAppId(service.appId);
        $window.plugins.OneSignal.setNotificationOpenedHandler(function(jsonData) {
            console.log('notificationOpenedCallback: ' + JSON.stringify(jsonData));
        });

        //Prompts the user for notification permissions.
        //    * Since this shows a generic native prompt, we recommend instead using an In-App Message to prompt for notification permission (See step 6) to better communicate to your users what notifications they will get.
        $window.plugins.OneSignal.promptForPushNotificationsWithUserResponse(function(accepted) {
            console.log("User accepted notifications: " + accepted);
        });

        $window.plugins.OneSignal.setNotificationWillShowInForegroundHandler(function(jsonData) {
            console.log('notificationWillShowInForegroundHandler: ' + JSON.stringify(jsonData));
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, jsonData.getNotification());
        });

        $window.plugins.OneSignal.setNotificationOpenedHandler(function(jsonData) {
            console.log('setNotificationOpenedHandler: ' + JSON.stringify(jsonData));
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, jsonData.getNotification());
        });

        $window.plugins.OneSignal.setExternalUserId($session.getExternalUserId(), (results) => {
            // The results will contain push and email success statuses
            console.log('Results of setting external user id');
            console.log(results);

            // Push can be expected in almost every situation with a success status, but
            // as a pre-caution its good to verify it exists
            if (results.push && results.push.success) {
                console.log('Results of setting external user id push status:');
                console.log(results.push.success);
            }

            // Verify the email is set or check that the results have an email success status
            if (results.email && results.email.success) {
                console.log('Results of setting external user id email status:');
                console.log(results.email.success);
            }

            // Verify the number is set or check that the results have an sms success status
            if (results.sms && results.sms.success) {
                console.log('Results of setting external user id sms status:');
                console.log(results.sms.success);
            }
        });

        service.push = $window.plugins.OneSignal;
    };

    // @deprecated
    service.isRegistered = function () {
        return $q.reject({deprecated: true});
    };

    /**
     * Handle registration, and various push events
     */
    service.register = function (registerOnly) {
        service.init();

        var localRegisterOnly = (registerOnly === null) ? false : registerOnly;

        service.init();

        if ($rootScope.isNativeApp) {


            if (!localRegisterOnly) {
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
            }
        }
    };

    /**
     * Registration!
     */
    service.registerDevice = function () {
        return Push.registerPlayer();
    };

    service.onNotificationReceived = function () {
        $log.info('[PUSH.onNotificationReceived]');
    };

    /**
     * Update push badge.
     */
    service.updateUnreadCount = function () {
        $log.info('[PUSH.updateUnreadCount]');
    };

    /**
     * LocalNotification wrapper.
     *
     * @param messageId
     * @param title
     * @param message
     */
    service.sendLocalNotification = function (messageId, title, message) {
        // Should be OKayish
        $log.debug('-- Push-Service, sending a Local Notification --');

        var localMessage = angular.copy(message);
        if (Push.device_type === SB.DEVICE.TYPE_IOS) {
            localMessage = '';
        }

        var params = {
            id: messageId,
            title: title,
            sound: (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) ? 'res://Sounds/sb_beep4.caf' : 'res://sb_beep4',
            text: localMessage
        };

        if (Push.device_type === SB.DEVICE.TYPE_ANDROID) {
            params.smallIcon = 'res://ic_icon';
            params.icon = 'res://icon';
        }

        try {
            $cordovaLocalNotification.schedule(params);
        } catch (e) {
            console.error('[PushService::Error]');
            console.error(e);
            // Seems sound can create issues
            delete x.sound;
            $cordovaLocalNotification.schedule(params);
        }

        Push.markAsDisplayed(messageId);
    };

    // @deprecated
    service.fetchMessagesOnStart = function () {
        $log.info('[PUSH.fetchMessagesOnStart]');
    };

    // @deprecated
    service.displayNotification = function (messagePayload) {
        $log.info('[PUSH.displayNotification] messagePayload', messagePayload);
    };

    // Push simulator!
    window.pushService = service;

    return service;
});
