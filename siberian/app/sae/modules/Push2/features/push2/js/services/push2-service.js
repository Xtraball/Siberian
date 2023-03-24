/**
 * Push2Service
 *
 * @author Xtraball SAS
 *
 * @version 5.0.0
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
                console.error('An error occured while registering device for Push.', e.message);
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

        // Uncomment to set OneSignal device logging to VERBOSE
        $window.plugins.OneSignal.setLogLevel(6, 0);

        // NOTE: Update the setAppId value below with your OneSignal AppId.
        $window.plugins.OneSignal.setAppId(service.appId);

        $window.plugins.OneSignal.promptForPushNotificationsWithUserResponse((accepted) => {
            console.log("User accepted notifications: " + accepted);
        });

        $window.plugins.OneSignal.setNotificationOpenedHandler(function(jsonData) {
            console.log('notificationOpenedCallback: ' + JSON.stringify(jsonData));
        });

        $window.plugins.OneSignal.setNotificationWillShowInForegroundHandler(function(jsonData) {
            console.log('notificationWillShowInForegroundHandler: ' + JSON.stringify(jsonData));
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, jsonData.getNotification());
        });

        $window.plugins.OneSignal.setNotificationOpenedHandler(function(jsonData) {
            console.log('setNotificationOpenedHandler: ' + JSON.stringify(jsonData));
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, jsonData.getNotification());
        });

        $window.plugins.OneSignal.setExternalUserId($session.getExternalUserId(Application.id), (results) => {
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

            $window.plugins.OneSignal.getDeviceState(function(stateChanges) {
                console.log('OneSignal getDeviceState: ' + JSON.stringify(stateChanges));
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
            service.displayNotification(data);
        });

        service.push = $window.plugins.OneSignal;
    };

    // @deprecated
    service.isRegistered = function () {
        return $q.reject({deprecated: true});
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
        $log.debug('-- Push2-Service, sending a Local Notification --');

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
            console.error('[PushService::Error]');
            console.error(e);
            // Seems sound can create issues
            delete x.sound;
            $cordovaLocalNotification.schedule(params);
        }
    };

    // @deprecated
    service.fetchMessagesOnStart = function () {
        $log.info('[PUSH.fetchMessagesOnStart]');
    };

    // @deprecated
    service.displayNotification = function (messagePayload) {
        $log.info('[PUSH.displayNotification] messagePayload', messagePayload);

        // Prevent an ID being shown twice.
        try {
            $session
                .getItem('pushMessageIds')
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
                            (extendedPayload.cover || extendedPayload.action_url)) {
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

                            var pushPopupTemplate =
                                '<img src="#COVER_URI#" class="#KLASS#">' +
                                '<span>#MESSAGE#</span>';

                            var config = {
                                buttons: [
                                    {
                                        text: $translate.instant('Cancel', 'push'),
                                        type: 'button-custom',
                                        onTap: function () {
                                            // @deprecated
                                            //if (isInAppMessage) {
                                            //    Push.markInAppAsRead();
                                            //}
                                            // Simply closes!
                                        }
                                    },
                                    {
                                        text: $translate.instant('View', 'push'),
                                        type: 'button-custom',
                                        onTap: function () {
                                            // @deprecated
                                            //if (isInAppMessage) {
                                            //    Push.markInAppAsRead();
                                            //}

                                            if ((extendedPayload.open_webview !== true) &&
                                                (extendedPayload.open_webview !== 'true')) {
                                                $location.path(extendedPayload.action_url);
                                            } else {
                                                LinkService.openLink(extendedPayload.action_url, {}, false);
                                            }
                                        }
                                    }
                                ],
                                cssClass: 'push-popup',
                                title: messagePayload.title,
                                template: pushPopupTemplate
                                    .replace('#COVER_URI#', coverUri)
                                    .replace('#KLASS#', (extendedPayload.cover ? '' : ' ng-hide'))
                                    .replace('#MESSAGE#', messagePayload.message)
                            };

                            var trimmedTitle = messagePayload.title.trim();
                            var trimmedMessage = messagePayload.message.trim();

                            if (trimmedTitle.length === 0 && trimmedMessage.length === 0) {
                                // This is a "silent push" it's up to whom sent it to handle it!
                                $log.debug("Silent push (empty title, message)", messagePayload);
                            } else if (extendedPayload.isSilent === true) {
                                // This is a "silent push" it's up to whom sent it to handle it!
                                $log.debug("Silent push (forced silent)", messagePayload);
                            } else {
                                // This is a regular push!
                                if (messagePayload.config !== undefined) {
                                    config = angular.extend(config, messagePayload.config);
                                }

                                // Handles case with only a cover image!
                                if ((extendedPayload.action_url === undefined) ||
                                    (extendedPayload.action_url === '') ||
                                    (extendedPayload.action_url === null)) {
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

                                $log.debug('Message payload (ionicPopup):', messagePayload, config);
                                // Also copy to "local notification" this way we ensure message is explicitely notified!
                                if (extendedPayload.foreground) {
                                    service.sendLocalNotification(messageId, trimmedTitle, trimmedMessage);
                                }
                                Dialog.ionicPopup(config);
                            }
                        } else {
                            var otherTrimmedTitle = messagePayload.title.trim();
                            var otherTrimmedMessage = messagePayload.message.trim();

                            if (otherTrimmedTitle.length === 0 && otherTrimmedMessage.length === 0) {
                                // This is a "silent push" it's up to whom sent it to handle it!
                                $log.debug("Silent push (empty title, message)", messagePayload);
                            } else {
                                var localTitle = (messagePayload.title !== undefined) ?
                                    messagePayload.title : 'Notification';
                                $log.debug('Message payload (alert):', messagePayload);

                                // Also copy to "local notification" this way we ensure message is explicitely notified!
                                if (extendedPayload && extendedPayload.foreground) {
                                    service.sendLocalNotification(messageId, otherTrimmedTitle, otherTrimmedMessage);
                                }

                                Dialog.alert(localTitle, messagePayload.message, 'OK');
                            }
                        }

                        // Search for less resource consuming maybe use Push factory directly!
                        $rootScope.$broadcast(SB.EVENTS.PUSH.unreadPushs, messagePayload.count);
                    }

                    // Nope!
                    $log.debug('Will not display duplicated message: ', messagePayload);
                }).catch(function (err) {
                    // We got an error!
                    $log.debug('We got an error with the localForage when trying to display push message: ', messagePayload);
                    $log.debug(err);
                });
        } catch (e) {
            $log.debug('We got an exception when trying to display push message: ', messagePayload);
            $log.debug(e);
        }
    };

    // Push simulator!
    window.pushService = service;

    return service;
});
