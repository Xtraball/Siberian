/**
 * Cabride factory
 */
angular.module('starter')
    .factory('Cabride', function (CabrideSocket, Customer, Application, Pages, Modal, Location, SB,
                                  $q, $session, $rootScope, $interval, $timeout, $log, $ionicPlatform, ContextualMenu,
                                  $pwaRequest, PushService, Push, Dialog, Loader, $state, $ionicSideMenuDelegate) {
        var factory = {
            value_id: null,
            settings: {
                avatarProvider: 'identicon',
                passengerPicture: "./features/cabride/assets/templates/images/001-passenger.svg",
                driverPicture: "./features/cabride/assets/templates/images/002-driver.svg",
            },
            isAlive: false,
            socket: null,
            uuid: null,
            waitPong: false,
            initPromise: false,
            helloPromise: $q.defer(),
            lobbyPromise: null,
            joinedLobby: false,
            awaitPromises: [],
            rooms: [],
            publicRooms: [],
            privateRooms: [],
            /** Settings */
            user: null,
            isPassenger: false,
            isDriver: false,
            isTaxiLayout: false,
            /** Promises */
            updatePositionPromise: null,
        };

        factory.setValueId = function (valueId) {
            factory.value_id = valueId;

            return factory;
        };

        factory.currencySymbol = function () {
            return factory.settings.currency.symbol_native;
        };

        factory.getValueId = function () {
            return factory.value_id;
        };

        factory.onStart = function () {
            Application.loaded.then(function () {
                // App runtime!
                var cabride = _.find(Pages.getActivePages(), {
                    code: 'cabride'
                });

                // Module is not in the App!
                if (!cabride) {
                    return;
                }

                factory
                    .setValueId(cabride.value_id)
                    .init()
                    .then(function (success) {
                        // Debug connected users & rooms
                        setInterval(function () {
                            factory
                                .ping(true).promise
                                .then(function () {
                                    factory.setIsAlive();
                                }).catch(function () {
                                    factory.setIsGone();
                                });
                        }, 60000);
                    }).catch(function (error) {
                        console.log('cabride error', error);
                    });
            });
        };

        // Handle the known protocols
        factory.onMessage = function (message) {
            switch (message.event) {
                case 'hello':
                    if (message.uuid !== '') {
                        factory.sendEvent('hello');
                        factory.helloPromise.resolve(message);
                    } else {
                        factory.helloPromise.reject({
                            message: 'Empty or Invalid UUID!'
                        });
                    }
                    break;
                case 'ack': // Acknowledgments
                    break;
                case 'request-ok': // Acknowledgments
                    break;
                case 'request-ko': // Acknowledgments
                    break;
                case 'advert-drivers':
                    $rootScope.$broadcast('cabride.advertDrivers', {drivers: message.drivers});
                    break;
                case 'aggregate-information':
                    $rootScope.$broadcast('cabride.aggregateInformation', {information: message.information});
                    break;
                case 'update-request':
                    $rootScope.$broadcast('cabride.updateRequest', {request: message.request});
                    break;
                case 'ping': // Ping!
                    factory.sendEvent('pong');
                    break;
                case 'pong': // Pong!
                    factory.waitPong = false;
                    break;
                case 'joinlobby-ok':
                    if (factory.lobbyPromise !== null) {
                        factory.lobbyPromise.resolve();
                    }
                    factory.joinedLobby = true;
                    break;
                case 'joinlobby-ko':
                    if (factory.lobbyPromise !== null) {
                        factory.lobbyPromise.reject();

                        // Re-init the joinLobby promise after a ko!
                        $timeout(function () {
                            factory.lobbyPromise = null;
                        }, 1000);
                    }
                    factory.joinedLobby = false;
                    break;
                // Generally this case won't happen so much, but if it does, we can cleanly closes the connection
                case 'closing-websocket':
                    // close from here too!
                    break;
            }
        };

        factory.resetPromises = function () {
            factory.helloPromise = $q.defer();
            factory.lobbyPromise = null;
            factory.joinedLobby = false;
        };

        factory.updateRequest = function (request) {
            factory.sendEvent('update-request', {
                request: request
            });
        };

        factory.getMyRides = function () {
            return $pwaRequest.post('/cabride/mobile_ride/me', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.getMyPayments = function () {
            return $pwaRequest.post('/cabride/mobile_ride/my-payments', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.getPaymentHistory = function () {
            return $pwaRequest.post('/cabride/mobile_driver/payment-history', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.getPendingRides = function () {
            return $pwaRequest.post('/cabride/mobile_ride/pending', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.getAcceptedRides = function () {
            return $pwaRequest.post('/cabride/mobile_ride/accepted', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.getCompletedRides = function () {
            return $pwaRequest.post('/cabride/mobile_ride/completed', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.getCancelledRides = function () {
            return $pwaRequest.post('/cabride/mobile_ride/cancelled', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.declineRide = function (requestId) {
            return $pwaRequest.post('/cabride/mobile_ride/decline', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                cache: false
            });
        };

        factory.acceptRide = function (requestId, route) {
            return $pwaRequest.post('/cabride/mobile_ride/accept', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                data: {
                    route: route
                },
                cache: false
            });
        };

        factory.cancelRide = function (requestId, cancel) {
            return $pwaRequest.post('/cabride/mobile_ride/cancel', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                data: cancel,
                cache: false
            });
        };

        factory.cancelRideDriver = function (requestId, cancel) {
            return $pwaRequest.post('/cabride/mobile_ride/cancel-driver', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                data: cancel,
                cache: false
            });
        };

        factory.driveToPassenger = function (requestId, route) {
            return $pwaRequest.post('/cabride/mobile_ride/drive-to-passenger', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                data: {
                    route: route
                },
                cache: false
            });
        };

        factory.driveToDestination = function (requestId) {
            return $pwaRequest.post('/cabride/mobile_ride/drive-to-destination', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                cache: false
            });
        };

        factory.completeRide = function (requestId) {
            return $pwaRequest.post('/cabride/mobile_ride/complete', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                cache: false
            });
        };

        factory.getVehicleInformation = function () {
            return $pwaRequest.post('/cabride/mobile_ride/vehicle-information', {
                urlParams: {
                    value_id: factory.value_id,
                },
                cache: false
            });
        };

        factory.selectVehicleType = function (typeId) {
            return $pwaRequest.post('/cabride/mobile_ride/select-vehicle-type', {
                urlParams: {
                    value_id: factory.value_id,
                    typeId: typeId
                },
                cache: false
            });
        };

        factory.rateCourse = function (requestId, rating) {
            return $pwaRequest.post('/cabride/mobile_ride/rate-course', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                data: {
                    rating: rating
                },
                cache: false
            });
        };

        factory.saveDriver = function (driver) {
            return $pwaRequest.post('/cabride/mobile_ride/save-driver', {
                urlParams: {
                    value_id: factory.value_id,
                },
                data: {
                    driver: driver,
                },
                cache: false
            });
        };

        factory.saveCard = function (card, type) {
            return $pwaRequest.post('/cabride/mobile_payment/save-card', {
                urlParams: {
                    value_id: factory.value_id,
                },
                data: {
                    card: card,
                    type: type
                },
                cache: false
            });
        };

        factory.deleteVault = function (vault) {
            return $pwaRequest.post('/cabride/mobile_payment/delete-vault', {
                urlParams: {
                    value_id: factory.value_id,
                    vaultId: vault.client_vault_id
                },
                cache: false
            });
        };

        factory.sendEvent = function (eventType, payload) {
            var localPayload = angular.extend({
                event: eventType,
                uuid: factory.uuid
            }, payload);

            CabrideSocket.sendMsg(localPayload);
        };

        factory.requestRide = function (route) {
            return $pwaRequest.post('/cabride/mobile_request/ride', {
                urlParams: {
                    value_id: factory.value_id
                },
                data: {
                    route: route
                },
                cache: false
            });
        };

        factory.validateRequest = function (vehicleType, route, customFormFields, paymentId) {
            return $pwaRequest.post('/cabride/mobile_request/validate', {
                urlParams: {
                    value_id: factory.value_id
                },
                data: {
                    paymentId: paymentId,
                    customFormFields: customFormFields,
                    vehicleType: vehicleType,
                    route: route
                },
                cache: false
            });
        };

        factory.fetchRequest = function (requestId) {
            return $pwaRequest.get('/cabride/mobile_request/fetch', {
                urlParams: {
                    value_id: factory.value_id,
                    requestId: requestId
                },
                cache: false
            });
        };

        factory.rdModal = null;
        factory.requestDetailsModal = function (newScope, requestId, userType) {
            Loader.show();

            factory
            .fetchRequest(requestId)
            .then(function (payload) {
                Modal
                .fromTemplateUrl("features/cabride/assets/templates/l1/modal/request-details.html", {
                    scope: angular.extend(newScope, {
                        close: function () {
                            factory.rdModal.remove();
                        },
                        request: payload.request,
                        userType: userType
                    }),
                    animation: "slide-in-right-left"
                }).then(function (modal) {
                    factory.rdModal = modal;
                    factory.rdModal.show();

                    return modal;
                });
            }).then(function () {
                Loader.hide();
            });
        };

        factory.rcModal = null;
        factory.rateCourseModal = function (request) {
            Modal
            .fromTemplateUrl("features/cabride/assets/templates/l1/modal/rate-course.html", {
                scope: angular.extend($rootScope.$new(true), {
                    request: request,
                    close: function () {
                        factory.rcModal.remove();
                    }
                }),
                animation: "slide-in-right-left"
            }).then(function (modal) {
                factory.rcModal = modal;
                factory.rcModal.show();

                return modal;
            });
        };

        factory.clModal = null;
        factory.cancelModal = function (request, userType) {
            Modal
            .fromTemplateUrl("features/cabride/assets/templates/l1/modal/cancel-course.html", {
                scope: angular.extend($rootScope.$new(true), {
                    request: request,
                    userType: userType,
                    close: function () {
                        factory.clModal.remove();
                    }
                }),
                animation: "slide-in-right-left"
            }).then(function (modal) {
                factory.clModal = modal;
                factory.clModal.show();

                return modal;
            });
        };

        /**
         * Short aliases
         */
        factory.updatePosition = function () {
            if (factory.joinedLobby === false) {
                return;
            }

            // Wait until we have an identified user!
            if (!factory.user) {
                return;
            }

            Location
            .getLocation()
            .then(function (position) {
                factory.lastPosition = position.coords;
                var payload = {
                    userId: Customer.customer.id,
                    position: {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    }
                };
                if (factory.isDriver) {
                    payload.driverId = factory.user.driverId;
                    payload.userType = "driver";
                } else {
                    payload.clientId = factory.user.clientId;
                    payload.userType = "passenger";
                }
                factory.sendEvent('update-position', payload);
            }, function () {
                // Skipping this time!
            });
        };

        factory.startUpdatePosition = function () {
            // Ensure we start only once the position poller!
            factory.updatePosition();
            if (factory.updatePositionPromise === null) {
                factory.updatePositionPromise = $interval(function () {
                    factory.updatePosition();
                }, 15000);
            }
        };

        factory.stopUpdatePosition = function () {
            // Stops only of started/promise exists!
            if (factory.updatePositionPromise !== null) {
                $interval.cancel(factory.updatePositionPromise);
                factory.updatePositionPromise = null;
            }
        };

        /**
         * Ping every minute!
         */
        factory.ping = function (retry, previousPromise) {
            var pingPromise = (previousPromise === undefined) ?
                $q.defer() : previousPromise;

            factory.sendEvent('ping', {});
            factory.waitPong = true;

            $timeout(function () {
                if (factory.waitPong === true) {
                    if (retry) {
                        factory.ping(false, pingPromise);
                    } else {
                        pingPromise.reject();
                    }
                } else {
                    pingPromise.resolve();
                }
            }, 60000);

            return pingPromise;
        };

        /**
         * Confirm server is alive
         */
        factory.setIsAlive = function () {
            $rootScope.$broadcast('cabride.isAlive');
            factory.isAlive = true;
        };

        /**
         * Confirm server is gone
         */
        factory.setIsGone = function () {
            $rootScope.$broadcast('cabride.isGone');
            factory.isAlive = false;
        };

        /**
         * Set user as Passenger
         */
        factory.setIsPassenger = function (update) {
            $rootScope.$broadcast('cabride.isPassenger');
            factory.isPassenger = true;
            factory.isDriver = false;

            // Update in DB!
            if (update === true) {
                factory.updateUser("passenger");
            }
        };

        /**
         * Set user as Passenger
         */
        factory.setIsDriver = function (update) {
            $rootScope.$broadcast('cabride.isDriver');
            factory.isPassenger = false;
            factory.isDriver = true;

            // Update in DB!
            if (update === true) {
                factory.updateUser("driver");
            }
        };

        /**
         * Fetch user
         */
        factory.fetchUser = function () {
            factory.updateUserPush();
            return $pwaRequest.post('/cabride/mobile_view/fetch-user', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        /**
         * Ensure user is registered for pushes!
         */
        factory.updateUserPush = function () {
            PushService
                .isReadyPromise
                .then(function () {
                    $pwaRequest.post('/cabride/mobile_view/update-user-push', {
                        urlParams: {
                            value_id: factory.value_id,
                            device: Push.device_type,
                            token: Push.device_token
                        },
                        cache: false
                    });
                }, function () {
                    $log.info("[Ride] not registering user device for push.");
                });
        };

        /**
         * Update user
         */
        factory.updateUser = function (userType) {
            return $pwaRequest.post('/cabride/mobile_view/update-user', {
                urlParams: {
                    value_id: factory.value_id,
                    userType: userType
                },
                cache: false
            });
        };

        /**
         * Update user
         */
        factory.toggleOnlineStatus = function (isOnline) {
            return $pwaRequest.post('/cabride/mobile_view/toggle-online', {
                urlParams: {
                    value_id: factory.value_id,
                    isOnline: isOnline
                },
                cache: false
            });
        };

        /**
         *
         * @param isTaxiLayout
         */
        factory.setIsTaxiLayout = function (isTaxiLayout) {
            factory.isTaxiLayout = isTaxiLayout;

            // Clear ContextualMenu
            ContextualMenu.clear();
        };

        /**
         * Short aliases
         *
         * @param payload
         */
        factory.sendMessage = function (payload) {
            var localPayload = angular.extend({
                messageId: Date.now() * 1000000, // To nanoseconds (only for instant sorting)
                userId: Customer.customer.id
            }, payload);
            factory.sendEvent('request', localPayload);
        };

        /**
         *
         * @param sbToken
         * @param appKey
         */
        factory.joinLobby = function (sbToken, appKey) {
            var deferred = $q.defer();
            if (factory.lobbyPromise === null) {
                factory.sendEvent('join-lobby', {
                    sbToken: sbToken,
                    appKey: appKey,
                    valueId: factory.value_id
                });
                factory.lobbyPromise = deferred;
            } else {
                $log.info('You already joined the lobby!');
                deferred.resolve();
            }
            return deferred.promise;
        };

        /**
         * Fetch wss & feature settings
         */
        factory.fetchSettings = function () {
            return $pwaRequest.post('/cabride/mobile_view/fetch-settings', {
                urlParams: {
                    value_id: factory.value_id
                },
                cache: false
            });
        };

        factory.authUser = function () {
            factory
            .joinLobby($session.getId(), APP_KEY)
            .then(function () {
                factory.initPromise.resolve();

                // Send position updates to the server!
                factory.startUpdatePosition();
            }).catch(function (error) {
                factory.initPromise.reject(error);
            }).finally(function () {
                $log.info('cabride joinLobby finally');
            });
        };

        /**
         * Initializes the cabride connection
         *
         * @return Promise
         */
        factory.init = function () {
            if (factory.isAlive) {
                if (!factory.joinedLobby) {
                    factory.authUser();
                    return factory.initPromise.promise;
                }

                return $q.resolve();
            }

            if (factory.initPromise === false) {
                factory.initPromise = $q.defer();
            } else {
                return factory.initPromise.promise;
            }

            factory
                .fetchSettings()
                .then(function (response) {

                    factory.settings = angular.extend({}, factory.settings, response.settings);

                    if (factory.settings.driverPicture.length > 0) {
                        factory.settings.driverPicture = IMAGE_URL + factory.settings.driverPicture;
                    } else {
                        factory.settings.driverPicture = "./features/cabride/assets/templates/images/002-driver.svg";
                    }

                    if (factory.settings.passengerPicture.length > 0) {
                        factory.settings.passengerPicture = IMAGE_URL + factory.settings.passengerPicture;
                    } else {
                        factory.settings.passengerPicture = "./features/cabride/assets/templates/images/001-passenger.svg";
                    }

                    if (response.settings.navBackground.length > 0) {
                        $rootScope.$broadcast('cabride.setNavBackground',
                            {navBackground: "url('" + IMAGE_URL + response.settings.navBackground + "')"});
                    }

                    // Init socket connection!
                    CabrideSocket.initSocket(factory);

                }).catch(function (error) {
                    factory.initPromise.reject(error);
                });

            return factory.initPromise.promise;
        };

        $ionicPlatform.on('resume', function () {
            if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                factory.startUpdatePosition();
            }
        });

        $ionicPlatform.on('pause', function () {
            if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                factory.stopUpdatePosition();
            }
        });

        $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
            factory.stopUpdatePosition();
        });

        $rootScope.$on("cabride.updateUser", function (event, user) {
            factory.user = user;
        });

        $rootScope.$on("cabride.isTaxiLayout", function () {
            factory.setIsTaxiLayout(true);
        });

        $rootScope.$on("cabride.isOnline", function (event, isOnline) {
            // Refresh driver markers
            factory
            .toggleOnlineStatus(isOnline)
            .then(function (payload) {
                $rootScope.$broadcast("cabride.setIsOnline", payload.isOnline);
            }, function (error) {
                $rootScope.$broadcast("cabride.setIsOnline", false);
                Dialog
                .alert("Incomplete profile!", error.message, "OK", 5000)
                .then(function () {
                    if ($ionicSideMenuDelegate.isOpenLeft()) {
                        $ionicSideMenuDelegate.toggleLeft();
                    }
                    if ($ionicSideMenuDelegate.isOpenRight()) {
                        $ionicSideMenuDelegate.toggleRight();
                    }
                    $state.go("cabride-vehicle-information");
                });
            });
        });

        // We will hook push when App is open to force a local notification
        $rootScope.$on(SB.EVENTS.PUSH.notificationReceived, function (event, data) {
            try {
                if (cordova.plugins.notification && cordova.plugins.notification.local) {
                    // Ok it's a cabride payload!
                    if (data.additionalData &&
                        data.additionalData.additional_payload &&
                        data.additionalData.additional_payload.cabride) {

                        // Check if it's a foreground push
                        if (data.additionalData.foreground) {
                            var msgPayload = data;
                            // Process, otherwise, push was already in notification tab.
                            cordova.plugins.notification.local.schedule({
                                title: msgPayload.title,
                                text: msgPayload.message,
                                smallIcon: "res://icon.png",
                                icon: IMAGE_URL + "/app/local/modules/Cabride/features/cabride/icons/cabride-push.png"
                            });
                        }
                    }
                }
            } catch (e) {
                // Silently fails! we will have the modal anyway!
            }
        });

        return factory;
    });
