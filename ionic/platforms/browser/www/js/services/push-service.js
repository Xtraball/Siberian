/**
 * PushService
 *
 * @author Xtraball SAS
 *
 * @version 4.15.7
 */
angular.module('starter').service('PushService', function ($cordovaLocalNotification, $location, $log, $q, $rootScope,
                                                           $translate, $window, $session, Application, Dialog,
                                                           LinkService, Pages, Push, SB) {
    var service = {
        push: null,
        isReady: null,
        isReadyPromise: null,
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
        service.isReady = $q.defer();
        service.isReadyPromise = service.isReady.promise;
        service.init();

        if (service.push && $rootScope.isNativeApp) {
            service.push.on('registration', function (data) {
                $log.debug('device_token: ' + data.registrationId);

                Push.device_token = data.registrationId;
                service.registerDevice();

                // Resolve promise!
                service.isReady.resolve();
            });

            service.onNotificationReceived();
            service.push.on('error', function (error) {
                $log.debug(error.message);

                // Reject
                service.isReady.reject();
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
            service.isReady.reject();
        }

        if (!$rootScope.isNativeApp) {
            Application.loaded.then(function () {
                // When Application is loaded, register at least for InApp
                service.fetchMessagesOnStart();
            });
            service.isReady.reject();
        }
    };

    /**
     * Registration!
     */
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

    /**
     * Android!
     */
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
            $rootScope.$broadcast(SB.EVENTS.PUSH.notificationReceived, data);

            service.push.finish(function () {
                $log.debug('push finish success');
                // success!
            }, function () {
                $log.debug('push finish error');
                // error!
            });
        });
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

                        var pushPopupTemplate =
                            '<img src="#COVER_URI#" class="#KLASS#">' +
                            '<span>#MESSAGE#</span>';

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
    };

    // Push simulator!
    window.pushService = service;

    return service;
});
