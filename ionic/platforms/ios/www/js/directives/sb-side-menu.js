/* global
 angular
 */

angular.module('starter').directive('sbSideMenu', function ($rootElement, $rootScope, $ionicHistory, $translate,
                                                            $ionicSideMenuDelegate, $timeout, HomepageLayout,
                                                            ContextualMenu) {
    return {
        restrict: 'E',
        replace: true,
        scope: {},
        templateUrl: 'templates/page/side-menu.html',
        link: function (scope, element) {
            /** Defining the global functionalities of the page */
            HomepageLayout
                .getFeatures()
                .then(function (features) {
                    scope.layout = HomepageLayout.properties;
                    scope.layout_id = HomepageLayout.properties.layoutId;
                    angular.element($rootElement)
                        .addClass(('layout-'+scope.layout_id)
                        .replace(/[^a-zA-Z0-9_\-]+/, '-')
                        .replace('.', '-')
                        .replace(/\-\-*/, '-'));
                });

            /** Custom go back, works with/without side-menus */
            scope.goBack = function () {
                $ionicHistory.goBack();
            };

            /** Special trick to handle manual updates. */
            scope.checkForUpdate = function () {
                $rootScope.checkForUpdate();
            };

            scope.isMenuOpen = function() {
                return $ionicSideMenuDelegate.isOpen();
            };

            scope.isMenuLeftOpen = function() {
                return $ionicSideMenuDelegate.isOpenLeft();
            };

            scope.isMenuRightOpen = function() {
                return $ionicSideMenuDelegate.isOpenRight();
            };

            scope.layoutHasSideMenu = function () {
                if (!scope.layout) {
                    return false;
                }
                return ["left", "right"].indexOf(scope.layout.menu.position) >= 0;
            };

            scope.layoutIsRight = function () {
                if (!scope.layout) {
                    return false;
                }
                return (scope.layout.menu.position === "right");
            };

            scope.layoutIsLeft = function () {
                if (!scope.layout) {
                    return false;
                }
                return (scope.layout.menu.position === "left");
            };

            scope.showLeftMenu = function () {
                if (scope.layoutIsLeft()) {
                    return true;
                }

                if (scope.layoutIsRight() &&
                    scope.contextualMenuExists()) {
                    return true;
                }

                return false;
            };

            scope.showLeftButton = function () {
                return scope.layoutIsLeft();
            };

            scope.showRightMenu = function () {
                if (scope.layoutIsRight()) {
                    return true;
                }

                if (scope.layoutIsLeft() &&
                    scope.contextualMenuExists()) {
                    return true;
                }

                return false;
            };

            scope.showRightButton = function () {
                if (scope.layoutIsRight()) {
                    return true;
                }

                if (scope.layoutIsLeft() &&
                    scope.contextualMenuExists()) {
                    return false;
                }
                return false;
            };

            scope.showBottom = function () {
                return (scope.layout_id && (scope.layout.menu.position === "bottom") &&
                    (scope.layout.menu.visibility === "homepage"));
            };

            scope.showAlways = function () {
                return (scope.layout_id && (scope.layout.menu.position === "bottom") &&
                    (scope.layout.menu.visibility === "always"));
            };

            scope.contextualMenuSideWidth = function () {
                return ContextualMenu.width;
            };

            scope.contextualMenuIsEnabled = function () {
                return ContextualMenu.isEnabled;
            };

            scope.contextualMenuExists = function () {
                return ContextualMenu.exists;
            };

            scope.contextualMenu = function () {
                return ContextualMenu.templateURL;
            };

            /** ======== */
            scope.getLeftSrc = function () {
                // Layout HAS side menu AND is RIGHT, AND we have a Contextual menu, so the contextual is LEFT
                if (scope.contextualMenuExists() &&
                    scope.layoutHasSideMenu() &&
                    scope.layoutIsRight()) {
                    return scope.contextualMenu();
                }

                if (scope.layoutHasSideMenu() && scope.layoutIsLeft()) {
                    return "homepage-menu.html";
                }

                return "blank-menu.html";
            };

            scope.getLeftWidth = function () {
                // Layout HAS side menu AND is RIGHT, AND we have a Contextual menu, so the contextual is LEFT
                if (scope.contextualMenuExists() &&
                    scope.layoutHasSideMenu() &&
                    scope.layoutIsRight()) {
                    return scope.contextualMenuSideWidth();
                }

                if (scope.layoutHasSideMenu() && scope.layoutIsLeft()) {
                    return scope.layout.menu.sidebarLeftWidth;
                }

                return 0;
            };

            // SIDE MENU RIGHT
            scope.getRightSrc = function () {
                // Layout HAS side menu AND is LEFT, AND we have a Contextual menu, so the contextual is RIGHT
                if (scope.contextualMenuExists() &&
                    scope.layoutHasSideMenu() &&
                    scope.layoutIsLeft()) {
                    return scope.contextualMenu();
                }

                // Layout  HAS no side menu, so ContextualMenu is FORCED right
                if (!scope.layoutHasSideMenu() &&
                    scope.contextualMenuExists()) {
                    return scope.contextualMenu();
                }

                if (scope.layoutHasSideMenu() && scope.layoutIsRight()) {
                    return "homepage-menu.html";
                }

                return "blank-menu.html";
            };

            scope.getRightWidth = function () {
                // Layout HAS side menu AND is LEFT, AND we have a Contextual menu, so the contextual is RIGHT
                if (scope.contextualMenuExists() &&
                    scope.layoutHasSideMenu() &&
                    scope.layoutIsLeft()) {
                    return scope.contextualMenuSideWidth();
                }

                // Layout  HAS no side menu, so ContextualMenu is FORCED right
                if (!scope.layoutHasSideMenu() &&
                    scope.contextualMenuExists()) {
                    return scope.contextualMenuSideWidth();
                }

                if (scope.layoutHasSideMenu() && scope.layoutIsRight()) {
                    return scope.layout.menu.sidebarRightWidth;
                }

                return 0;
            };
        }
    };
});
