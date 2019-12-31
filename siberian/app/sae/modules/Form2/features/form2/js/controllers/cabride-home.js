/**
 * Cabride version 2 controllers
 */
angular.module('starter')
.controller('CabrideHome', function ($window, $state, $scope, $rootScope, $timeout, $translate,
                                     $ionicSideMenuDelegate, Modal, Cabride, CabrideUtils, Customer,
                                     Loader, GoogleMaps, Dialog, Location, SB, PaymentMethod) {
    angular.extend($scope, {
        pageTitle: Cabride.settings.pageTitle,
        valueId: Cabride.getValueId(),
        isAlive: Cabride.isAlive,
        isLoggedIn: Customer.isLoggedIn(),
        isLoading: true,
        customer: null,
        crMap: null,
        crMapPin: null,
        showMapPin: true,
        driverMarkers: [],
        gmapsAutocompleteOptions: {},
        ride: {
            isSearching: false,
            pickupPlace: null,
            pickupAddress: "",
            dropoffPlace: null,
            dropoffAddress: "",
            distance: null,
            duration: null
        },
        currentRoute: null,
        isPassenger: false,
        isDriver: false,
        locationIsEnabled: Location.isEnabled,
        removeSideMenu: null
    });

    $rootScope.$on('cabride.isAlive', function () {
        $timeout(function () {
            $scope.isAlive = true;
        });
    });

    $rootScope.$on('cabride.isGone', function () {
        $timeout(function () {
            $scope.isAlive = false;
        });
    });

    $rootScope.$on('cabride.isOnline', function (event, isOnline) {
        $scope.isOnline = isOnline;
    });

    $rootScope.$on('cabride.advertDrivers', function (event, payload) {
        // Refresh driver markers
        $scope.drawDrivers(payload.drivers);
    });

    $rootScope.$on('location.isEnabled', function (event, payload) {
        // Refresh driver markers
        $timeout(function () {
            $scope.locationIsEnabled = payload;
        });
    });

    $scope.$on('$ionicView.enter', function () {
        $ionicSideMenuDelegate.canDragContent(false);
    });

    $scope.$on('$ionicView.afterLeave', function () {
        $ionicSideMenuDelegate.canDragContent(true);
    });

    $scope.cs = function () {
        return Cabride.currencySymbol();
    };

    $scope.getPageTitle = function () {
        return Cabride.settings.pageTitle;
    };

    $scope.passengerPicture = function () {
        return Cabride.settings.passengerPicture;
    };

    $scope.driverPicture = function () {
        return Cabride.settings.driverPicture;
    };

    $scope.reconnect = function () {
        Cabride.init();
    };

    $scope.isTaxiLayout = function () {
        return Cabride.isTaxiLayout;
    };

    $scope.openMenu = function () {
        CabrideUtils.openMenu();
    };

    // Init contextual menu for initial triggers!
    CabrideUtils.rebuildContextualMenu();

    // Passenger / Driver choice!
    $scope.selectPassenger = function () {
        // Check if the user is logged in
        if (!Customer.isLoggedIn()) {
            Customer.loginModal($scope,
                /** Login */
                function () {
                    Loader.show();
                    // Check if it's a driver first!
                    Cabride
                    .fetchUser()
                    .then(function (payload) {
                        $rootScope.$broadcast("cabride.updateUser", payload.user);
                        switch (payload.user.type) {
                            case "driver":
                                $scope.setIsDriver(false);
                                $rootScope.$broadcast("cabride.setIsOnline", payload.user.isOnline);
                                $rootScope.$broadcast("cabride.isOnline", payload.user.isOnline);
                                break;
                            case "passenger":
                            case "new":
                            default:
                                $scope.setIsPassenger(true);
                        }

                        Loader.hide();
                    }).catch(function () {
                        Loader.hide();
                    });
                },
                /** Logout */
                function () {
                },
                /** Register */
                function () {
                    $scope.setIsPassenger(true);
                });
        } else {
            $scope.setIsPassenger(true);
        }
    };

    $scope.setIsPassenger = function (update) {
        Cabride.setIsPassenger(update);
        $scope.isPassenger = true;
        $scope.isDriver = false;
        $scope.rebuild();
    };

    $scope.selectDriver = function () {
        // Opens driver form
        // Check if the user is logged in
        if (!Customer.isLoggedIn()) {
            Customer.loginModal($scope,
                /** Login */
                function () {
                    $scope.setIsDriver(true);
                },
                /** Logout */
                function () {
                },
                /** Register */
                function () {
                    $scope.setIsDriver(true);
                });
        } else {
            $scope.setIsDriver(true);
        }
    };

    $scope.setIsDriver = function (update) {
        Cabride.setIsDriver(update);
        $scope.isPassenger = false;
        $scope.isDriver = true;
        $scope.rebuild();
    };

    $scope.initMap = function () {
        $scope.crMap = GoogleMaps.createMap("crMap", {
            zoom: 10,
            center: {
                lat: Cabride.settings.defaultLat,
                lng: Cabride.settings.defaultLng
            },
            disableDefaultUI: true
        });

        // Center on user location if default is blank!
        if (Cabride.settings.defaultLat === 0 &&
            Cabride.settings.defaultLng === 0) {
            $timeout(function () {
                $scope.centerMe();
            }, 500);
        }

        var icon = {
            url: "./features/cabride/assets/templates/images/004-blank.png",
            width: 48,
            height: 48,
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(24, 48)
        };

        var center = $scope.crMap.getCenter();
        $scope.crMapPin = new google.maps.Marker({
            position: center,
            map: $scope.crMap,
            icon: icon
        });

        google.maps.event.addListener($scope.crMap, "center_changed", function () {
            // 0.5 seconds after the center of the map has changed,
            // set back the marker position.
            $timeout(function () {
                var center = $scope.crMap.getCenter();
                $scope.crMapPin.setPosition(center);
            }, 500);
        });
    };

    $scope.drawDrivers = function (drivers) {
        // Clear markers!
        for (var indexMarker in $scope.driverMarkers) {
            var driverMarker = $scope.driverMarkers[indexMarker];
            driverMarker.setMap(null);
        }
        $scope.driverMarkers = [];

        for (var indexDriver in drivers) {
            var driver = drivers[indexDriver];
            var myLatlng = new google.maps.LatLng(driver.position.latitude, driver.position.longitude);

            var a = {
                lat: function () {
                    return driver.position.latitude;
                },
                lng: function () {
                    return driver.position.longitude;
                }
            };
            var b = {
                lat: function () {
                    return driver.previous.latitude;
                },
                lng: function () {
                    return driver.previous.longitude;
                }
            };
            var heading = google.maps.geometry.spherical.computeHeading(a, b);

            var icon = {
                url: CabrideUtils.taxiIcon(heading),
                size: new google.maps.Size(120, 120),
                scaledSize: new google.maps.Size(36, 36),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(18, 18)
            };

            var tmpMarker = new google.maps.Marker({
                position: myLatlng,
                map: $scope.crMap,
                icon: icon,
            });
            $scope.driverMarkers.push(tmpMarker);
        }
    };

    $scope.centerMe = function () {
        Location
        .getLocation()
        .then(function (position) {
            $scope.crMap.setCenter(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
            $scope.crMap.setZoom(15);
        }, function () {
            Dialog.alert(
                "Location",
                "Sorry we are unable to locate you, please check your GPS settings & authorization.",
                "OK",
                -1,
                "cabride");
        });
    };

    $scope.zoomIn = function () {
        $scope.crMap.setZoom($scope.crMap.getZoom() + 1);
    };

    $scope.zoomOut = function () {
        $scope.crMap.setZoom($scope.crMap.getZoom() - 1);
    };

    $scope.canClear = function () {
        return $scope.ride.isSearching ||
            ($scope.ride.pickupAddress !== "") ||
            ($scope.ride.dropoffAddress !== "");
    };

    $scope.driverCanRegister = function () {
        return Cabride.settings.driverCanRegister;
    };

    // Pristine ride values!
    $scope.clearSearch = function () {
        $scope.ride = {
            isSearching: false,
            pickupPlace: null,
            pickupAddress: "",
            dropoffPlace: null,
            dropoffAddress: "",
            distance: null,
            duration: null,
        };

        CabrideUtils.clearRoute();
    };

    $scope.disableTap = function (inputId) {
        $scope.showMapPin = false;

        var input = document.getElementsByClassName("pac-container");
        // disable ionic data tab
        angular.element(input).attr("data-tap-disabled", "true");
        // leave input field if google-address-entry is selected
        angular.element(input).on("click", function () {
            document.getElementById(inputId).blur();
        });
    };

    $scope.displayMapPin = function () {
        $scope.showMapPin = true;
    };

    $scope.pinIcon = function () {
        if ($scope.isDriver && $scope.isOnline) {
            return "./features/cabride/assets/templates/images/003-pin-green.svg";
        }

        return "./features/cabride/assets/templates/images/003-pin.svg";
    };

    $scope.pinText = function () {
        if ($scope.ride.pickupAddress === "") {
            return {
                action: "pickup",
                class: "positive",
                text: $translate.instant("Set pick-up location", "cabride")
            };
        }
        if ($scope.ride.dropoffAddress === "") {
            return {
                action: "dropoff",
                class: "energized",
                text: $translate.instant("Set drop-off location", "cabride")
            };
        }
        if ($scope.ride.isSearching) {
            return {
                action: "loading",
                class: "ng-hide",
                text: ""
            };
        }
        if ($scope.ride.pickupAddress !== "" && $scope.ride.dropoffAddress !== "") {
            return {
                action: "search",
                class: "balanced",
                text: $translate.instant("Request a driver", "cabride")
            };
        }
        return {
            action: "none",
            class: "ng-hide",
            text: ""
        };
    };

    $scope.setPinLocation = function (action) {
        var center = $scope.crMap.getCenter();
        switch (action) {
            case "pickup":
                GoogleMaps
                .reverseGeocode({latitude: center.lat(), longitude: center.lng()})
                .then(function (results) {
                    if (results.length > 0) {
                        var pickupPlace = results[0];
                        $scope.ride.pickupAddress = pickupPlace.formatted_address;
                        $scope.ride.pickupPlace = pickupPlace;

                        $scope.setPickupAddress();
                    } else {
                        $scope.ride.pickupAddress = center.lat() + "," + center.lng();

                        $scope.setPickupAddress();
                    }
                }, function () {
                    Dialog.alert(
                        "Location",
                        "Your position doesn't resolve to a valid address.",
                        "OK",
                        -1,
                        "cabride");
                });
                break;
            case "dropoff":
                GoogleMaps
                .reverseGeocode({latitude: center.lat(), longitude: center.lng()})
                .then(function (results) {
                    if (results.length > 0) {
                        var dropoffPlace = results[0];
                        $scope.ride.dropoffAddress = dropoffPlace.formatted_address;
                        $scope.ride.dropoffPlace = dropoffPlace;

                        $scope.setDropoffAddress();
                    } else {
                        $scope.ride.dropoffAddress = center.lat() + "," + center.lng();

                        $scope.setDropoffAddress();
                    }
                }, function () {
                    Dialog.alert(
                        "Location",
                        "Your position doesn't resolve to a valid address.",
                        "OK",
                        -1,
                        "cabride");
                });
                break;
            case "search":
                $scope.requestRide();

                break;
            case "none":
            default:
                break;
        }
    };

    $scope.vaults = null;
    $scope.requestRide = function () {
        $scope.ride.isSearching = true;
        Cabride
        .requestRide($scope.currentRoute)
        .then(function (response) {
            if (response.collection && Object.keys(response.collection).length > 0) {
                $scope.vaults = response.vaults;
                $scope.showModal(response.collection);
            } else {
                Dialog.alert("", "We are sorry we didnt found any available driver around you!", "OK", -1, "cabride");
            }
        }, function (error) {
            Dialog.alert("", "We are sorry we didnt found any available driver around you!", "OK", -1, "cabride");
        }).then(function () {
            $scope.ride.isSearching = false;
        });
    };

    $scope.vtModal = null;
    $scope.showModal = function (vehicles) {
        Modal
        .fromTemplateUrl("features/cabride/assets/templates/l1/modal/vehicle-type.html", {
            scope: angular.extend($scope.$new(true), {
                close: function () {
                    $scope.vtModal.remove();
                },
                selectVehicle: function (vehicleType) {
                    $scope.selectVehicle(vehicleType);
                },
                vehicles: vehicles
            }),
            animation: "slide-in-right-left"
        }).then(function (modal) {
            $scope.vtModal = modal;
            $scope.vtModal.show();

            return modal;
        });
    };

    $scope.selectVehicle = function (vehicleType) {
        // Payment modal
        $scope.vehicleType = vehicleType;
        $scope.paymentTypeModal();
    };

    $scope.paymentTypeModal = function () {
        PaymentMethod.openModal($scope, {
            title: "Select a payment type",
            methods: Cabride.settings.paymentMethods,
            paymentType: PaymentMethod.AUTHORIZATION,
            enableVaults: true,
            displayAmount: false,
            payment: {
                displayAmount: $scope.vehicleType.pricing,
                amount: $scope.vehicleType.pricingValue
            },
            actions: [
                PaymentMethod.ACTION_AUTHORIZE
            ],
            onSelect: function (paymentId) {
                $scope.validateRequest(paymentId);
            }
        });
    };

    $scope.validateRequest = function (paymentId) {
        Loader.show("Sending request ...");
        Cabride
        .validateRequest($scope.vehicleType, $scope.currentRoute, Cabride.settings.customFormFields, paymentId)
        .then(function (response) {
            Loader.hide();
            Dialog
            .alert("Request sent", "Please now wait for a driver!", "OK", 2350)
            .then(function () {
                PaymentMethod.closeModal();
                $scope.vtModal.remove();
                $state.go("cabride-my-rides");
            });
            // Clear ride
            $scope.clearSearch();
        }, function (error) {
            Loader.hide();
            Dialog.alert("Sorry!", error.message, "OK");
        });
    };

    $scope.setPickupAddress = function () {
        $scope.checkRoute();
    };

    $scope.setDropoffAddress = function () {
        $scope.checkRoute();
    };

    $scope.checkRoute = function () {
        if ($scope.ride.pickupPlace && $scope.ride.dropoffPlace) {
            var pickup = {
                latitude: $scope.ride.pickupPlace.geometry.location.lat(),
                longitude: $scope.ride.pickupPlace.geometry.location.lng(),
            };
            var dropoff = {
                latitude: $scope.ride.dropoffPlace.geometry.location.lat(),
                longitude: $scope.ride.dropoffPlace.geometry.location.lng(),
            };

            CabrideUtils
            .getSimpleDirection(
                pickup,
                dropoff,
                {}
            ).then(function (route) {
                $scope.currentRoute = route;
                CabrideUtils.displayRoute($scope.crMap, route);
                var leg = _.get(route, "routes[0].legs[0]", false);
                if (leg) {
                    $scope.ride.distance = leg.distance.text;
                    $scope.ride.duration = leg.duration.text;
                }
            }, function () {
                // Clear route
            });
        }
    };

    $scope.geoPickup = function () {
        Location
        .getLocation()
        .then(function (position) {
            GoogleMaps
            .reverseGeocode(position.coords)
            .then(function (results) {
                if (results.length > 0) {
                    var pickupPlace = results[0];
                    $scope.ride.pickupAddress = pickupPlace.formatted_address;
                    $scope.ride.pickupPlace = pickupPlace;

                    $scope.setPickupAddress();
                }
            }, function () {
                Dialog.alert(
                    "Location",
                    "Your position doesn't resolve to a valid address.",
                    "OK", -1, "cabride");
            })
        }, function () {
            Dialog.alert(
                "Location",
                "Sorry we are unable to locate you, please check your GPS settings & authorization.",
                "OK", -1, "cabride");
        });
    };

    $scope.geoDropoff = function () {
        Location
        .getLocation()
        .then(function (position) {
            GoogleMaps
            .reverseGeocode(position.coords)
            .then(function (results) {
                if (results.length > 0) {
                    var dropoffPlace = results[0];
                    $scope.ride.dropoffAddress = dropoffPlace.formatted_address;
                    $scope.ride.dropoffPlace = dropoffPlace;

                    $scope.setDropoffAddress();
                }
            }, function () { // Error!
                Dialog.alert(
                    "Location",
                    "Your position doesn't resolve to a valid address.",
                    "OK", -1, "cabride");
            });
        }, function () { // Error!
            Dialog.alert(
                "Location",
                "Sorry we are unable to locate you, please check your GPS settings & authorization.",
                "OK", -1, "cabride");
        });
    };

    $scope.rebuild = function () {
        $scope.initMap();
    };

    // Init build!
    GoogleMaps
    .ready
    .then(function () {
        $scope.rebuild();
    });

    // On load!
    $scope.init = function () {
        Cabride
        .init()
        .then(function () {
            // If logged-in, do not force login at startup anymore!
            if (Customer.isLoggedIn()) {
                Customer
                .find()
                .then(function (customer) {
                    $scope.customer = customer;
                    $scope.customer.metadatas = _.isObject($scope.customer.metadatas)
                        ? $scope.customer.metadatas
                        : {};
                    $scope.avatarUrl = Customer.getAvatarUrl($scope.customer.id);

                    Cabride
                    .fetchUser()
                    .then(function (payload) {
                        $rootScope.$broadcast("cabride.updateUser", payload.user);
                        switch (payload.user.type) {
                            case "driver":
                                $scope.setIsDriver(false);
                                $rootScope.$broadcast("cabride.setIsOnline", payload.user.isOnline);
                                $rootScope.$broadcast("cabride.isOnline", payload.user.isOnline);
                                break;
                            case "passenger":
                                $scope.setIsPassenger(false);
                                break;
                            case "new":
                            default:
                                if (!Cabride.settings.driverCanRegister) {
                                    $scope.selectPassenger();
                                }
                        }

                        $scope.isLoading = false;
                    });
                });
            } else {
                $scope.isLoading = false;
            }
        }, function () {
            $scope.isLoading = false;
        }).catch(function () {
            $scope.isLoading = false;
        });
    };

    $scope.init();

    // Asking for the current layout!
    $rootScope.$broadcast("cabride.isTaxiLayoutActive");

    $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.init();
        $scope.isDriver = false;
        $scope.isPassenger = false;
        $scope.isLoggedIn = false;
    });

    $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.init();
        if (Customer.isLoggedIn()) {
            Customer
            .find()
            .then(function (customer) {
                $scope.customer = customer;
                $scope.customer.metadatas = _.isObject($scope.customer.metadatas)
                    ? $scope.customer.metadatas
                    : {};
                $scope.avatarUrl = Customer.getAvatarUrl($scope.customer.id);

                Cabride
                .fetchUser()
                .then(function (payload) {
                    $rootScope.$broadcast("cabride.updateUser", payload.user);
                    switch (payload.user.type) {
                        case "driver":
                            $scope.setIsDriver(false);
                            $rootScope.$broadcast("cabride.setIsOnline", payload.user.isOnline);
                            break;
                        case "passenger":
                            $scope.setIsPassenger(false);
                            break;
                        case "new":
                        default:
                            if (!Cabride.settings.driverCanRegister) {
                                $scope.selectPassenger();
                            }
                    }

                    $scope.isLoading = false;
                });
            });
        }
    });

    $rootScope.$on(SB.EVENTS.AUTH.registerSuccess, function () {
        $scope.init();
    });

    $rootScope.$on(SB.EVENTS.AUTH.editSuccess, function () {
        $scope.init();
    });

    // Action on state-name! shortcuts for passenger/driver signup
    if (!Customer.isLoggedIn()) {
        var currentState = $state.current.name;
        if (currentState === "cabride-signup-passenger") {
            $scope.selectPassenger();
        }
        if (currentState === "cabride-signup-driver") {
            $scope.selectDriver();
        }
    }
});

