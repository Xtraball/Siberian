/*global
    App, angular, window
 */

angular.module('starter').directive('sbPageBackground', function ($rootScope, $state, $stateParams, $pwaRequest,
                                                                  $session, $timeout, Application) {
    var init = false,
        is_updating = false,
        device_screen = $session.getDeviceScreen(),
        deffered = $pwaRequest.defer(),
        backgroundimages = deffered.promise;

    $session.loaded
        .then(function () {
            $pwaRequest.get('front/mobile/backgroundimages', {
                urlParams: {
                    device_width:   device_screen.width,
                    device_height:  device_screen.height
                }
            }).then(function (data) {
                deffered.resolve(data);
            });
        });

    return {
        restrict: 'A',
        controller: function ($scope, $state, $stateParams) {
            $scope.value_id = ($state.current.name === 'home') ? 'home' : $stateParams.value_id;
        },
        link: function (scope, element) {

            var network_done = false;

            scope.setBackgroundImageStyle = function (src, color) {
                if(!is_updating) {
                    is_updating = true;
                    var el = angular.element(element);

                    el.addClass("has-background-image");
                    if(color !== undefined) {
                        el.css({
                            "background-image": "linear-gradient(" + color + ", " + color + "), url('" + src + "')"
                        });
                    } else {
                        el.css({
                            "background-image": "url('" + src + "')"
                        });
                    }

                    $timeout(function () {
                        if(navigator.splashscreen !== undefined) {
                            navigator.splashscreen.hide();
                        }
                    }, 20);
                }
                is_updating = false;
            };

            // Default base64 fast image!
            Application.ready
                .then(function () {
                    if (!network_done) {
                        scope.setBackgroundImageStyle(Application.default_background);
                    }
                });

            var updateBackground = function () {
                backgroundimages
                    .then(function (data) {
                        Application.ready
                            .then(function () {
                                network_done = true;

                                var value_id = angular.copy(scope.value_id);
                                var exists = (angular.isDefined(value_id) &&
                                                angular.isDefined(data.backgrounds) &&
                                                 angular.isDefined(data.backgrounds[value_id]));
                                var fallback = ((Application.homepage_background || (value_id === "home")) &&
                                                angular.isDefined(data.backgrounds) &&
                                                angular.isDefined(data.backgrounds["home"]));

                                if (fallback === true) {
                                    value_id = "home";
                                }

                                if (exists || fallback) {
                                    var background_image = data.backgrounds[scope.value_id];
                                    window.tmp_img = new Image();
                                    window.tmp_img.src = background_image;
                                    window.tmp_img.onload = function () {
                                        scope.setBackgroundImageStyle(background_image);
                                    };

                                    if (window.tmp_img.complete || $rootScope.isOffline) {
                                        scope.setBackgroundImageStyle(background_image);
                                    }
                                    delete window.tmp_img;
                                } else {
                                    scope.setBackgroundImageStyle(
                                        "./img/placeholder/white-1.png",
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

            if(!init) {
                $rootScope.$on('$stateChangeStart', function (evt, toState, toParams) {
                    scope.value_id = (toState.name === "home") ? "home" : toParams.value_id;
                    updateBackground();
                });
                init = true;
            }

            scope.value_id = ($state.current.name === "home") ? "home" : $stateParams.value_id;
            updateBackground();

        }
    };
});
