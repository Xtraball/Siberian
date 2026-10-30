/**
 * sbSideMenu
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.3
 */
angular
.module("starter")
.directive("sbSideMenu", function () {
    return {
        restrict: "E",
        scope: {},
        templateUrl: "templates/page/side-menu.html",
        controller: function ($q, $scope, $rootElement, $rootScope, $injector, $timeout, $ionicHistory,
                              $ionicSideMenuDelegate, ContextualMenu, HomepageLayout, Application) {

            ContextualMenu.settings.isReady = $q.defer();

            $scope = angular.extend($scope, {
                contextualMenu: {
                    isEnabled: false,
                    side: 'right'
                },
                leftMenu: {
                    width: 0,
                    show: false,
                    src: 'blank-menu.html'
                },
                rightMenu: {
                    width: 0,
                    show: false,
                    src: 'blank-menu.html'
                }
            });

            // Custom go back, works with/without side-menus!
            $scope.goBack = function () {
                $ionicHistory.goBack();
            };

            $scope.showBottom = function () {
                return (
                    $scope.layout_id &&
                    ($scope.layout.menu.position === 'bottom') &&
                    ($scope.layout.menu.visibility === 'homepage')
                );
            };

            $scope.showAlways = function () {
                return Application.layoutShowAlways;
            };

            $scope.isMenuOpen = function() {
                return $ionicSideMenuDelegate.isOpen();
            };

            $scope.isMenuLeftOpen = function() {
                return $ionicSideMenuDelegate.isOpenLeft();
            };

            $scope.isMenuRightOpen = function() {
                return $ionicSideMenuDelegate.isOpenRight();
            };

            $scope.layoutIsRight = function () {
                if (!$scope.layout) {
                    return false;
                }
                return ($scope.layout.menu.position === "right");
            };

            $scope.layoutIsLeft = function () {
                if (!$scope.layout) {
                    return false;
                }
                return ($scope.layout.menu.position === "left");
            };

            $scope.layoutHasSideMenu = function () {
                if (!$scope.layout) {
                    return false;
                }
                return ($scope.layoutIsRight() || $scope.layoutIsLeft());
            };

            $scope.showLeftButton = function () {
                return $scope.layoutIsLeft();
            };

            $scope.showRightButton = function () {
                return $scope.layoutIsRight();
            };

            $scope.getLeftMenuWidth = function () {
                return $scope.leftMenu.width;
            };

            $scope.getLeftMenuSrc = function () {
                return $scope.leftMenu.src;
            };

            $scope.getLeftMenuShow = function () {
                return $scope.leftMenu.show;
            };

            $scope.getRightMenuWidth = function () {
                return $scope.rightMenu.width;
            };

            $scope.getRightMenuSrc = function () {
                return $scope.rightMenu.src;
            };

            $scope.getRightMenuShow = function () {
                return $scope.rightMenu.show;
            };

            $scope.contextualMenuIsReady = function () {
                return ContextualMenu.settings.isReady.promise;
            };

            $scope.backButtonIcon = function () {
                return Application.getBackIcon();
            };

            $scope.leftToggleIcon = function () {
                return Application.getLeftToggleIcon();
            };

            $scope.rightToggleIcon = function () {
                return Application.getRightToggleIcon();
            };

            $scope.init = function () {
                // Reset contextual menu ready promise!
                ContextualMenu.settings.isReady = $q.defer();

                $timeout(function () {
                    if ($scope.layoutIsLeft()) {
                        // Build left menu!
                        $scope.leftMenu = {
                            width: $scope.layout.menu.sidebarLeftWidth,
                            show: true,
                            src: "homepage-menu.html"
                        };

                        // Reset right & contextual!
                        $scope.contextualMenu.isEnabled = false;
                        $scope.contextualMenu.side = "right";
                        $scope.rightMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        // Enjoy contextual menu only (forced right)!
                        if (ContextualMenu.settings.isEnabled) {
                            $scope.contextualMenu.isEnabled = true;
                            $scope.contextualMenu.side = "right";
                            $scope.rightMenu = {
                                width: ContextualMenu.settings.width,
                                show: true,
                                src: ContextualMenu.settings.templateUrl
                            };
                        }

                    } else if ($scope.layoutIsRight()) {
                        // Reset left & contextual!
                        $scope.contextualMenu.isEnabled = false;
                        $scope.contextualMenu.side = "right";
                        $scope.leftMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        // Build right menu!
                        $scope.rightMenu = {
                            width: $scope.layout.menu.sidebarRightWidth,
                            show: true,
                            src: "homepage-menu.html"
                        };

                        // Enjoy contextual menu only (forced left)!
                        if (ContextualMenu.settings.isEnabled) {
                            $scope.contextualMenu.isEnabled = true;
                            $scope.contextualMenu.side = "left";
                            $scope.leftMenu = {
                                width: ContextualMenu.settings.width,
                                show: true,
                                src: ContextualMenu.settings.templateUrl
                            };
                        }
                    } else {
                        //  Reset left, right & contextual!
                        $scope.contextualMenu.isEnabled = false;
                        $scope.contextualMenu.side = "right";
                        $scope.leftMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        $scope.rightMenu = {
                            width: 0,
                            show: false,
                            src: "blank-menu.html"
                        };

                        // Enjoy contextual menu only!
                        if (ContextualMenu.settings.isEnabled) {
                            $scope.contextualMenu.isEnabled = true;
                            if (ContextualMenu.settings.preferredSide === "left") {
                                $scope.contextualMenu.side = "left";
                                $scope.leftMenu = {
                                    width: ContextualMenu.settings.width,
                                    show: true,
                                    src: ContextualMenu.settings.templateUrl
                                };
                            } else {
                                $scope.contextualMenu.side = "right";
                                $scope.rightMenu = {
                                    width: ContextualMenu.settings.width,
                                    show: true,
                                    src: ContextualMenu.settings.templateUrl
                                };
                            }
                        }
                    }

                    // It's ready, wait 20ms for the breathing!
                    $timeout(function () {
                        ContextualMenu.settings.isReady.resolve();
                    }, 20);
                }, 1);
            };

            $rootScope.$on("contextualMenu.init", function () {
                $scope.init();
            });

            $rootScope.$on("contextualMenu.toggle", function () {
                if ($scope.contextualMenu.isEnabled) {
                    if ($scope.contextualMenu.side === "left") {
                        $ionicSideMenuDelegate.toggleLeft(!$scope.isMenuLeftOpen());
                    } else {
                        $ionicSideMenuDelegate.toggleRight(!$scope.isMenuRightOpen());
                    }
                }
            });

            $rootScope.$on("contextualMenu.close", function () {
                if ($scope.contextualMenu.isEnabled) {
                    if ($scope.contextualMenu.side === "left") {
                        $ionicSideMenuDelegate.toggleLeft(false);
                    } else {
                        $ionicSideMenuDelegate.toggleRight(false);
                    }
                }
            });

            $rootScope.$on("contextualMenu.open", function () {
                if ($scope.contextualMenu.isEnabled) {
                    if ($scope.contextualMenu.side === "left") {
                        $ionicSideMenuDelegate.toggleLeft(true);
                    } else {
                        $ionicSideMenuDelegate.toggleRight(true);
                    }
                }
            });

            // Close all side menus!
            $rootScope.$on("sideMenu.close", function () {
                if ($ionicSideMenuDelegate.isOpenLeft()) {
                    $ionicSideMenuDelegate.toggleLeft(false);
                }
                if ($ionicSideMenuDelegate.isOpenRight()) {
                    $ionicSideMenuDelegate.toggleRight(false);
                }
            });

            // Init when ready!
            HomepageLayout
                .getFeatures()
                .then(function () {

                    // Default settings, module, layout!
                    $scope.layout = HomepageLayout.properties;
                    $scope.layout_id = HomepageLayout.properties.layoutId;
                    $scope.layout_code = HomepageLayout.properties.layoutCode;

                    // Store the nature of the fixed layout
                    Application.layoutShowAlways = (
                        $scope.layout_id &&
                        ($scope.layout.menu.position === 'bottom') &&
                        ($scope.layout.menu.visibility === 'always')
                    );

                    var layoutId = ('layout-' + $scope.layout_id)
                        .replace(/[^a-zA-Z0-9_\-]+/, '-')
                        .replace('.', '-')
                        .replace(/\-\-*/, '-');

                    var extendedClass = [
                        layoutId,
                        $scope.layout_code
                    ];

                    if (Application.layoutShowAlways) {
                        extendedClass.push('layout-always-visible');
                    }

                    angular
                        .element($rootElement)
                        .addClass(extendedClass.join(' '));

                    $scope.init();
                });
        }
    };
});
