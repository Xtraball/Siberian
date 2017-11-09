/* global
    App, angular, window
 */

angular.module('starter').directive('sbPageBackground', function ($rootScope, $state, $stateParams, $pwaRequest,
                                                                  $session, $timeout, $window, Application) {
    var init = false,
        isUpdating = false,
        deviceScreen = $session.getDeviceScreen(),
        deffered = $pwaRequest.defer(),
        backgroundImages = deffered.promise,
        catched = false,
        orientationChange = $window.matchMedia('(orientation: portrait)');

    $session.loaded
        .then(function () {
            var loadBackgrounds = function (refresh) {
                $pwaRequest.get('front/mobile/backgroundimages', {
                    urlParams: {
                        device_width: deviceScreen.width,
                        device_height: deviceScreen.height
                    },
                    refresh: refresh
                }).then(function (data) {
                    deffered.resolve(data);
                }).catch(function () {
                    if (!catched) {
                        catched = true;
                        $timeout(loadBackgrounds(false), 1);
                    }
                }); // Main load, then
            };
            loadBackgrounds(true);
        });

    return {
        restrict: 'A',
        controller: function ($scope) {
            $scope.valueId = ($state.current.name === 'home') ? 'home' : $stateParams.value_id;
        },
        link: function (scope, element) {
            var networkDone = false;

            scope.setBackgroundImageStyle = function (src, color) {
                if (!isUpdating) {
                    isUpdating = true;
                    var el = angular.element(element);

                    el.addClass('has-background-image');
                    if (color !== undefined) {
                        el.css({
                            'background-image': 'linear-gradient(' + color + ', ' + color + '), url(\'' + src + '\')'
                        });
                    } else {
                        el.css({
                            'background-image': 'url(\'' + src + '\')'
                        });
                    }

                    $timeout(function () {
                        if (navigator.splashscreen !== undefined) {
                            navigator.splashscreen.hide();
                        }
                    }, 20);
                }
                isUpdating = false;
            };

            // Default base64 fast image!
            Application.ready
                .then(function () {
                    if (!networkDone) {
                        scope.setBackgroundImageStyle(Application.default_background);
                    }
                });

            var updateBackground = function () {
                backgroundImages
                    .then(function (data) {
                        Application.ready
                            .then(function () {
                                networkDone = true;

                                var valueId = angular.copy(scope.valueId);

                                if (deviceScreen.orientation === 'landscape') {
                                    valueId = 'landscape_' + valueId;
                                }

                                var exists = (angular.isDefined(valueId) &&
                                                angular.isDefined(data.backgrounds) &&
                                                 angular.isDefined(data.backgrounds[valueId]));
                                var fallback = ((Application.homepage_background || (valueId === 'home' || valueId === 'landscape_home')) &&
                                                angular.isDefined(data.backgrounds) &&
                                                angular.isDefined(data.backgrounds.home));

                                if (exists || fallback) {
                                    var backgroundImage = data.backgrounds[valueId];
                                    window.tmpImg = new Image();
                                    window.tmpImg.src = backgroundImage;
                                    window.tmpImg.onload = function () {
                                        scope.setBackgroundImageStyle(backgroundImage);
                                    };

                                    if (window.tmpImg.complete || $rootScope.isOffline) {
                                        scope.setBackgroundImageStyle(backgroundImage);
                                    }
                                    delete window.tmpImg;
                                } else {
                                    scope.setBackgroundImageStyle(
                                        './img/placeholder/white-1.png',
                                        Application.colors.background.rgba
                                    );
                                }

                                $timeout(function () {
                                    if (navigator.splashscreen !== undefined) {
                                        navigator.splashscreen.hide();
                                    }
                                }, 20);
                            });
                    });
            };

            if (!init) {
                $rootScope.$on('$stateChangeStart', function (evt, toState, toParams) {
                    scope.valueId = (toState.name === 'home') ? 'home' : toParams.value_id;
                    updateBackground();
                });

                orientationChange.addListener(function () {
                    // Refresh device screen!
                    deviceScreen = $session.setDeviceScreen();
                    // Then trigger background update!
                    updateBackground();
                });
                init = true;
            }

            scope.valueId = ($state.current.name === 'home') ? 'home' : $stateParams.value_id;
            updateBackground();
        }
    };
});
