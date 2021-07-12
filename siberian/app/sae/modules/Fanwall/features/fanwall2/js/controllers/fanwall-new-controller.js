/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .controller('FanwallNewController', function ($scope, $rootScope, $session, $state, $stateParams, $translate, $q,
                                                  Customer, Fanwall, FanwallPost, Dialog, Loader, Location,
                                                  GoogleMaps, Popover, $timeout) {

        angular.extend($scope, {
            pageTitle: $translate.instant('Create a post', 'fanwall'),
            form: {
                text: '',
                pictures: [],
                date: null,
                limit: Fanwall.getSettings().max_images,
                location: {
                    latitude: 0,
                    longitude: 0,
                    locationShort: ''
                }
            },
            enbleModeration: Fanwall.getSettings().enable_moderation,
            cardDesign: Fanwall.getSettings().cardDesign,
            fetchingLocation: false,
            shortLocation: '',
            popoverItems: [],
            actionsPopover: null,
            preference: 'always',
            preferenceKey: 'socialwall.location.preference'
        });

        $scope.getCardDesign = function () {
            return Fanwall.getSettings().cardDesign;
        };

        $scope.getSettings = function () {
            return Fanwall.getSettings();
        };

        $scope.locationIsDisabled = function () {
            return !Location.isEnabled;
        };

        $scope.myAvatar = function () {
            // Empty image
            if (Customer.customer &&
                Customer.customer.image &&
                Customer.customer.image.length > 0) {
                return IMAGE_URL + 'images/customer' + Customer.customer.image;
            }
            return './features/fanwall2/assets/templates/images/customer-placeholder.png';
        };

        $scope.myName = function () {
            return Customer.customer.firstname + ' ' + Customer.customer.lastname;
        };

        $scope.clearForm = function () {
            $scope.form = {
                text: '',
                pictures: [],
                location: {
                    latitude: 0,
                    longitude: 0,
                    locationShort: ''
                }
            };
        };

        $scope.canSend = function () {
            return ($scope.form.text.length > 0 || $scope.form.pictures.length > 0);
        };

        $scope.sendPost = function () {
            var postId = ($scope.post !== undefined) ? $scope.post.id : null;

            if ($scope.fetchingLocation) {
                Dialog.alert('Wait', 'Please wait while we are fetching your location.', 'OK', 2350, 'fanwall');
                return false;
            }

            if (!$scope.canSend()) {
                Dialog.alert('Error', 'You must send at least a message or a picture.', 'OK', -1, 'fanwall');
                return false;
            }

            Loader.show();

            // Append now
            $scope.form.date = Math.round(Date.now() / 1000);

            return FanwallPost
                .sendPost(postId, $scope.form)
                .then(function (payload) {
                    Loader.hide();
                    $rootScope.$broadcast('fanwall.refresh');
                    $rootScope.$broadcast('fanwall.profile.reload');
                    $scope.close();
                }, function (payload) {
                    // Show error!
                    Loader.hide();
                    Dialog.alert('Error', payload.message, 'OK', -1, 'fanwall');
                });
        };

        /** Location preference */

        // Popover actions!
        $scope.openActions = function ($event) {
            $scope
                .closeActions()
                .then(function () {
                    Popover
                        .fromTemplateUrl('features/fanwall2/assets/templates/l1/modal/post/actions-popover.html', {
                            scope: $scope
                        }).then(function (popover) {
                        $scope.actionsPopover = popover;
                        $scope.actionsPopover.show($event);
                    });
                });
        };

        $scope.closeActions = function () {
            try {
                if ($scope.actionsPopover) {
                    return $scope.actionsPopover.hide();
                }
            } catch (e) {
                // We skip!
            }

            return $q.resolve();
        };

        // Re-init scope on settings change!
        $scope.changeLocationSettings = function (preference, reinit) {
            $scope.preference = preference;
            $session
                .setItem($scope.preferenceKey, preference)
                .then(function (value) {
                    if (reinit === true) {
                        $scope.init();
                    }
                })
                .catch(function (err) {
                    if (reinit === true) {
                        $scope.init();
                    }
                });
        };

        $scope.buildPopoverItems = function () {
            $scope.popoverItems = [];

            if (Location.isEnabled) {
                $scope.popoverItems.push({
                    label: $translate.instant('Locate me once', 'fanwall'),
                    icon: 'icon ion-android-locate',
                    click: function () {
                        $scope
                            .closeActions()
                            .then(function () {
                                $scope.changeLocationSettings('once', true);
                            });
                    }
                });

                $scope.popoverItems.push({
                    label: $translate.instant('Always ask', 'fanwall'),
                    icon: 'icon ion-help',
                    click: function () {
                        $scope
                            .closeActions()
                            .then(function () {
                                $scope.changeLocationSettings('ask', true);
                            });
                    }
                });

                $scope.popoverItems.push({
                    label: $translate.instant('Always locate me', 'fanwall'),
                    icon: 'icon ion-sb-location-on',
                    click: function () {
                        $scope
                            .closeActions()
                            .then(function () {
                                $scope.changeLocationSettings('always', true);
                            });
                    }
                });

                $scope.popoverItems.push({
                    label: $translate.instant('Never locate me', 'fanwall'),
                    icon: 'icon ion-sb-location-off',
                    click: function () {
                        $scope
                            .closeActions()
                            .then(function () {
                                $scope.changeLocationSettings('never', true);
                            });
                    }
                });
            } else {
                $scope.popoverItems.push({
                    label: $translate.instant('Check my location', 'fanwall'),
                    icon: 'icon ion-sb-location-off',
                    click: function () {
                        $scope
                            .closeActions()
                            .then(function () {
                                Location.requestLocation(function () {
                                    $scope.init();
                                });
                            });
                    }
                });
            }
        };

        if ($scope.post !== undefined) {
            $scope.pageTitle = 'Edit post';
            $scope.form.text = $scope.post.text.replace(/(<br( ?)(\/?)>)/gm, "\n");
            $scope.form.pictures = $scope.post.images;
        }

        $scope.noLocation = function () {
            $timeout(function () {
                $scope.fetchingLocation = false;
                $scope.form.location.latitude = 0;
                $scope.form.location.longitude = 0;
                $scope.shortLocation = $translate.instant('no location, check your preferences', 'fanwall');
                $scope.form.location.locationShort = $translate.instant('no location, check your preferences', 'fanwall');
            });
        };

        $scope.fetchLocation = function () {
            if (Location.isEnabled) {
                $scope.fetchingLocation = true;
                Location
                    .getLocation({timeout: 10000, enableHighAccuracy: false}, true)
                    .then(function (position) {
                        $scope.form.location.latitude = position.coords.latitude;
                        $scope.form.location.longitude = position.coords.longitude;

                        GoogleMaps
                            .reverseGeocode(position.coords)
                            .then(function (results) {
                                if (results.length > 0) {
                                    var place = results[0];

                                    try {
                                        $scope.shortLocation = _.find(place.address_components, function (item) {
                                            return item.types.indexOf('locality') >= 0;
                                        }).long_name;
                                    } catch (e) {
                                        $scope.shortLocation = place.formatted_address;
                                    }

                                    $scope.form.location.locationShort = $scope.shortLocation;
                                    $scope.fetchingLocation = false;
                                }
                            }, function () {
                                $scope.fetchingLocation = false;
                                $scope.shortLocation = Number.parseFloat(position.coords.latitude).toFixed(5) + ", " + Number.parseFloat(position.coords.longitude).toFixed(5);
                                $scope.form.location.locationShort = 'unknown';
                            });
                    }, function () {
                        $scope.noLocation();
                    });
            } else {
                $scope.fetchingLocation = false;
            }
        };

        $scope.init = function () {
            $scope.buildPopoverItems();

            switch ($scope.preference) {
                case 'never':
                    $scope.noLocation();
                    $scope.popoverIcon = 'icon ion-sb-location-off';
                    break;
                case 'once':
                    $scope.fetchLocation();
                    $scope.changeLocationSettings('never', false);
                    $scope.popoverIcon = 'icon ion-android-locate';
                    break;
                case 'ask':
                    $scope.popoverIcon = 'icon ion-help';
                    Dialog
                        .confirm(
                            'Location',
                            'Share my location for this post?',
                            ['YES', 'NO'],
                            -1,
                            'fanwall')
                        .then(function (success) {
                            if (success) {
                                $scope.fetchLocation();
                            } else {
                                $scope.noLocation();
                            }
                        });
                    break;
                case 'always':
                    $scope.popoverIcon = 'icon ion-sb-location-on';
                    $scope.fetchLocation();
                    break;
            }
        };

        $session
            .getItem($scope.preferenceKey)
            .then(function (preference) {
                if (preference === null) {
                    $scope.preference = 'always';
                } else {
                    $scope.preference = preference;
                }
                $scope.init();
            })
            .catch(function (err) {
                // Something went wrong!
                $scope.preference = 'always';
                $scope.init();
            });

    });
