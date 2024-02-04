/**
 * Push2Service
 *
 * @author Xtraball SAS
 *
 * @version 5.0.10
 */
angular
    .module('starter')
    .service('Push2Service', function ($cordovaLocalNotification, $timeout, $location, $log, $q, $rootScope, $translate,
                                       $injector, $window, $session, Application, Dialog, LinkService, Pages, Push2, SB) {
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
        },
    };

    service.onStart = function () {
        Application.loaded.then(function () {
            // App runtime!
            try {
                $timeout(function () {
                    service.configure(
                        Application.application.osAppId,
                        Application.application.pushIconcolor);
                    service.init();
                }, 1000);
            } catch (e) {
                console.error('[Push2Service] An error occured while registering device for Push.', e.message);
            }
        });
    };

    /**
     * @param appId
     * @param iconColor
     */
    service.configure = function (appId, iconColor) {
        service.appId = appId;

        // Validating push color!
        if (!(/^#[0-9A-F]{6}$/i).test(iconColor)) {
            $log.debug('[Push2Service] Invalid iconColor: ' + iconColor);
        } else {
            service.settings.android.iconColor = iconColor;
        }
    };

    /**
     * If available, initialize push
     */
    service.init = function () {
        if (!$window.plugins.OneSignal) {
            $log.error("[Push2Service] OneSignal plugin is missing");
            return;
        }

        if (service.appId === undefined || service.appId === null || service.appId === '') {
            $log.error("[Push2Service] Push appId is missing");
            return;
        }

        // Uncomment to set OneSignal device logging to VERBOSE
        $window.plugins.OneSignal.setLogLevel(6, 0);

        // NOTE: Update the setAppId value below with your OneSignal AppId.
        $window.plugins.OneSignal.setAppId(service.appId);

        $window.plugins.OneSignal.promptForPushNotificationsWithUserResponse((accepted) => {
            console.log("[Push2Service] User accepted notifications: " + accepted);
        });

        $window.plugins.OneSignal.setNotificationWillShowInForegroundHandler(function(notificationReceivedEvent) {
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, {
                event: notificationReceivedEvent,
                notification: notificationReceivedEvent.getNotification(),
                origin: 'foreground'
            });
        });

        $window.plugins.OneSignal.setNotificationOpenedHandler(function(openedEvent) {
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, {
                event: null,
                notification: openedEvent.notification,
                origin: 'opened_handler'
            });
        });

        $window.plugins.OneSignal.setExternalUserId($session.getExternalUserId(Application.id), (results) => {
            // The results will contain push and email success statuses
            console.log('[Push2Service] Results of setting external user id');
            console.log(results);

            // Push can be expected in almost every situation with a success status, but
            // as a pre-caution its good to verify it exists
            if (results.push && results.push.success) {
                console.log('[Push2Service] Results of setting external user id push status:');
                console.log(results.push.success);
            }

            // Verify the email is set or check that the results have an email success status
            if (results.email && results.email.success) {
                console.log('[Push2Service] Results of setting external user id email status:');
                console.log(results.email.success);
            }

            // Verify the number is set or check that the results have an sms success status
            if (results.sms && results.sms.success) {
                console.log('[Push2Service] Results of setting external user id sms status:');
                console.log(results.sms.success);
            }

            $window.plugins.OneSignal.getDeviceState(function(stateChanges) {
                console.log('[Push2Service] OneSignal getDeviceState: ' + JSON.stringify(stateChanges));
                Push2.registerPlayer(stateChanges);
            });

        });

        // Register for push events!
        $rootScope.$on(SB.EVENTS.PUSH.notificationReceived, function (event, data) {
            // Refresh to prevent the need for pullToRefresh!
            var pushFeature = _.filter(Pages.getActivePages(), function (page) {
                return (page.code === 'push2');
            });
            if (pushFeature.length >= 1) {
                Push2.setValueId(pushFeature[0].value_id);
                Push2.findAll(0, true);
            }

            // We continue to display even if Push2 is not active, we do not require it.
            service.onNotificationReceived(data);
        });

        $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
            $log.info('[Received SB.EVENTS.AUTH.loginSuccess]');
            service.afterLoginOrRegister();
        });

        $rootScope.$on(SB.EVENTS.AUTH.registerSuccess, function () {
            $log.info('[Received SB.EVENTS.AUTH.registerSuccess]');
            service.afterLoginOrRegister();
        });

        service.push = $window.plugins.OneSignal;
    };

    service.afterLoginOrRegister = function () {
        $log.info('[Push2Service.afterLoginOrRegister]');

        if (service.push === null) {
            $log.error('[Push2Service.afterLoginOrRegister] Push is not initialized');
            return;
        }

        // We are updating the external user id after a login (if changed)
        service.push.setExternalUserId($session.getExternalUserId(Application.id), (results) => {
            service.push.getDeviceState(function(stateChanges) {
                console.log('[Push2Service.afterLoginOrRegister] OneSignal getDeviceState: ' + JSON.stringify(stateChanges));
                Push2.registerPlayer(stateChanges);
            });
        });
    };

    // @deprecated
    service.isRegistered = function () {
        return $q.reject({deprecated: true});
    };

    /**
     * Update push badge.
     */
    service.updateUnreadCount = function () {
        $log.info('@deprecated [Push2Service.updateUnreadCount]');
    };

    /**
     * LocalNotification wrapper.
     *
     * @param messageId
     * @param title
     * @param message
     */
    service.sendLocalNotification = function (messageId, title, message, origin) {
        // Should be OKayish
        $log.debug('[Push2Service.sendLocalNotification] Sending a local notification from ' + origin + '');

        var localMessage = angular.copy(message);
        if (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) {
            localMessage = '';
        }

        var params = {
            id: messageId,
            title: title,
            sound: (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) ? 'res://Sounds/sb_beep4.caf' : 'res://sb_beep4',
            text: localMessage
        };

        if (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID) {
            params.smallIcon = 'res://ic_icon';
            params.icon = 'res://icon';
        }

        try {
            $cordovaLocalNotification.schedule(params);
        } catch (e) {
            console.error('[Push2Service::Error]');
            console.error(e);
            // Seems sound can create issues
            delete x.sound;
            $cordovaLocalNotification.schedule(params);
        }
    };

    // @deprecated
    service.fetchMessagesOnStart = function () {
        $log.info('@deprecated [Push2Service.fetchMessagesOnStart]');
    };

    service.onNotificationReceived = function (data) {
        $log.info('[Push2Service.onNotificationReceived] messagePayload', data);

        // Special case with empty title/message === FORCE SILENT
        var trimmedTitle = data.notification.title.trim();
        var trimmedBody = data.notification.body.trim();
        if (trimmedTitle.length === 0 &&
            trimmedBody.length === 0) {
            data.origin = 'silent';
        }

        if (data.extractXPath('notification.additionalData.isSilent', false)) {
            data.origin = 'force_silent';
        }

        switch (data.origin) {
            case 'opened_handler':
                $log.info('opened_handler', data);
                service.displayForegroundNotification(data);
                break;
            case 'foreground':
                data.event.complete(data.notification);
                service.displayForegroundNotification(data);
                break;
            case 'silent':
                $log.info('Silent notification!', data);
                data.event.complete(null);
                break;
            case 'force_silent':
                $log.info('Force silent notification!', data);
                data.event.complete(null);
                break;
        }

    };

    service.displayForegroundNotification = function (OSNotification) {
        var additionalData = OSNotification.notification.additionalData;

        let title = OSNotification.notification.title.trim();
        let body = OSNotification.notification.body.trim();
        let messageId = OSNotification.notification.notificationId;

        // End fast in the simple condition!
        if (additionalData === undefined) {
            Dialog.alert(title, body, 'OK');
            return;
        }

        // Otherwise we have additional data!
        if (additionalData.cover || additionalData.action_value) {
            // Prevent missing or not base url!
            var coverUri = additionalData.cover;
            try {
                if (coverUri.indexOf('http') !== 0) {
                    coverUri = DOMAIN + additionalData.cover;
                }
            } catch (e) {}

            var pushPopupTemplate =
                '<img src="#COVER_URI#" class="#KLASS#" />' +
                '<span>#MESSAGE#</span>';

            var config = {
                buttons: [
                    {
                        text: $translate.instant('Cancel', 'push'),
                        type: 'button-custom',
                        onTap: function () {
                            // Simply closes!
                        }
                    },
                    {
                        text: $translate.instant('View', 'push'),
                        type: 'button-custom',
                        onTap: function () {
                            if ((additionalData.open_webview !== true) &&
                                (additionalData.open_webview !== 'true')) {
                                $location.path(additionalData.action_value);
                            } else {
                                LinkService.openLink(additionalData.action_value, {}, false);
                            }
                        }
                    }
                ],
                cssClass: 'push-popup',
                title: title,
                template: pushPopupTemplate
                    .replace('#COVER_URI#', coverUri)
                    .replace('#KLASS#', (additionalData.cover ? '' : ' ng-hide'))
                    .replace('#MESSAGE#', body)
            };

            // Handles case with only a cover image!
            if ((additionalData.action_value === undefined) ||
                (additionalData.action_value === '') ||
                (additionalData.action_value === null)) {
                config.buttons = [
                    {
                        text: $translate.instant('OK', 'push'),
                        type: 'button-custom',
                        onTap: function () {
                            // Simply closes!
                        }
                    }
                ];
            }

            $log.debug('[Push2Service.ionicPopup] Message payload:', OSNotification, config);
            // Also copy to "local notification" this way we ensure message is explicitely notified!
            if (additionalData.extractXPath('duplicateForeground', false)) {
                service.sendLocalNotification(messageId, title, body, 'duplicate_foreground');
            }
            Dialog.ionicPopup(config);

        } else {
            $log.debug('[Push2Service.alert] Message payload:', OSNotification);

            // Also copy to "local notification" this way we ensure message is explicitely notified!
            if (additionalData.extractXPath('duplicateForeground', false)) {
                service.sendLocalNotification(messageId, title, body, 'duplicate_foreground');
            }
            Dialog.alert(title, body, 'OK');
        }
    }

    // Push simulator!
    window.pushService = service;

    return service;
});
