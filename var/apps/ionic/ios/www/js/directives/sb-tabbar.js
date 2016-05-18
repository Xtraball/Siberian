"use strict";

App.directive('sbTabbar', function ($ionicHistory, $ionicModal, $ionicSlideBoxDelegate, $ionicSideMenuDelegate, $location, $rootScope, $timeout, $translate, $window, Application, Customer, Dialog, HomepageLayout, Pages, Url, AUTH_EVENTS, PADLOCK_EVENTS, PUSH_EVENTS) {
    return {
        restrict: 'A',
        templateUrl: function() {
            return "templates/home/" + HomepageLayout.properties.layoutId + "/view.html";
            // Url.get('/front/mobile_home/menu')
        },
        scope: {},
        link: function ($scope, element, attrs) {

            $scope.tabbar_is_visible = Pages.is_loaded;
            $scope.tabbar_is_transparent = HomepageLayout.properties.tabbar_is_transparent;
            $scope.animate_tabbar = !$scope.tabbar_is_visible;
            $scope.pages_list_is_visible = false;
            $scope.active_page = 0;
            $scope.modalDefault = "templates/home/modal/view.html";
            $scope.modalLayout10 = "templates/home/l10/modal.html";

            $scope.layout = HomepageLayout;

            $scope.loadContent = function() {

                HomepageLayout.getOptions().then(function (options) {
                    $scope.options = options;
                });

                HomepageLayout.getData().then(function (data) {
                    $scope.data = data;
                    $scope.push_badge = data.push_badge;
                });

                HomepageLayout.getFeatures().then(function (features) {

                    // filtered active options
                    $scope.features = features;

                    if ($scope.features.overview.hasMore) {
                        // add the "more" icon
                        $scope.features.overview.options.push({
                            name: features.data.more_items.name,
                            icon_url: features.data.more_items.icon_url,
                            icon_is_colorable: features.data.more_items.icon_is_colorable,
                            code: features.data.more_items.code,
                            url: "tabbar_more"
                        });

                        //If layout 10, we need to reorder icons in order to have the more button at middle
                        if ($scope.features.layoutId == "l10") {
                            var third_option = features.overview.options[2];
                            var fourth_option = features.overview.options[3];
                            features.overview.options[2] = features.overview.options[4];
                            features.overview.options[3] = third_option;
                            features.overview.options[4] = fourth_option;
                            features.options = features.options.slice(4, features.options.length);
                        }

                    }

                    $timeout(function () {
                        if(!Pages.is_loaded) {
                            Pages.is_loaded = true;
                            $scope.tabbar_is_visible = true;
                        }
                    }, 500);

                    /** Load first feature is needed */
                    if($rootScope.loginFeature === true) {
                    	$ionicHistory.goBack();
                    	$rootScope.loginFeature = null;
                    	
                    } else if (!Application.is_customizing_colors && HomepageLayout.properties.options.autoSelectFirst && features.first_option !== false) {
                        $ionicHistory.nextViewOptions({
                            historyRoot: true,
                            disableAnimate: false
                        });
                        $location.path(features.first_option.path);
                    }

                });
                //$scope.close_previewer_button_is_visible = Application.is_previewer && ionic.Platform.isIos();
            };

            $scope.closeList = function () {
                $scope.tabbar_is_visible = true;
                $scope.pages_list_is_visible = false;
            };

            $scope.goTo = function(feature) {

                if($scope.moreModal) {
                    $scope.closeMore();
                }

                /** Clear history for side-menu feature */
                switch($scope.data.layout.position) {
                    case 'left': case 'right':
                    if($ionicSideMenuDelegate.isOpenLeft()){
                        $ionicSideMenuDelegate.toggleLeft();
                    }
                    if($ionicSideMenuDelegate.isOpenRight()){
                        $ionicSideMenuDelegate.toggleRight();
                    }

                    if(feature.code != "padlock") { /** do not clear history if we open the padlock */
	                    $ionicHistory.nextViewOptions({
	                        historyRoot: true,
	                        disableAnimate: false
	                    });
                    }
                    break;
                    default:
                }

                if(attrs.isDisabled) return false;

                if(feature.code == "tabbar_account") {
                    $scope.login();
                } else if(feature.code == "tabbar_more") {
                    $scope.tabbar_is_visible = false;
                    $scope.pages_list_is_visible = true;
                    $scope.more();
                } else if(feature.is_link) {
                    if($rootScope.isOverview) {
                        Dialog.alert($translate.instant("Error"), $translate.instant("This feature is available from the application only"), $translate.instant("OK"));
                        return;
                    }

                    if(ionic.Platform.isAndroid() && feature.url.indexOf("pdf") >= 0) {
                        $window.open(feature.url, "_system", "location=no");
                    } else {
                    $window.open(feature.url, $rootScope.getTargetForLink(), "location=no");
                    }
                } else {
                    $location.path(feature.path);
                }

            };

            $scope.gotoPage = function(index) {
                $ionicSlideBoxDelegate.$getByHandle('slideBoxLayout').slide(index);
            };

            $scope.closePreviewer = function() {
                $window.location = "app:closeApplication";
            };

            $scope.login = function($scope) { $rootScope.loginFeature = null; Customer.loginModal($scope) };

            $scope.loadContent();

            if($rootScope.isOverview) {
                $scope.$on("tabbarStatesChanged", function() {
                    if(!HomepageLayout.isInitialized()) {
                        $scope.loadContent();
                    }
                });
            }

            $rootScope.$on(PUSH_EVENTS.unreadPushs, function(event, args) {
                $timeout(function() {
                    $scope.push_badge = args;
                });
            });
            $rootScope.$on(PUSH_EVENTS.readPushs, function() { $scope.push_badge = 0; });

            /** Layout 1, Layout 2, Layout 10 more modal, and maybe more */
            $scope.modalUrl = (HomepageLayout.properties.layoutId != 'l10') ? $scope.modalDefault : $scope.modalLayout10;

            $scope.more = function() {
                $ionicModal.fromTemplateUrl($scope.modalUrl, {
                    scope: $scope,
                    animation: 'slide-in-up'
                }).then(function(modal) {
                    $scope.moreModal = modal;
                    $scope.moreModal.show();
                });
            };

            $scope.closeMore = function() {
                $scope.moreModal.hide();
                $scope.tabbar_is_visible = true;
                $scope.pages_list_is_visible = false;
            };

            $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() {
                $timeout(function() {
                    HomepageLayout.setNeedToBuildTheOptions(true);
                    $scope.loadContent();
                });
            });

            $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
                $timeout(function() {
                    HomepageLayout.setNeedToBuildTheOptions(true);
                    $scope.loadContent();
                });
            });

            $rootScope.$on(PADLOCK_EVENTS.unlockFeatures, function() {
                $timeout(function() {
                    HomepageLayout.setNeedToBuildTheOptions(true);
                    $scope.loadContent();
                });
            });

            if($rootScope.isOverview) {
                $scope.tabbar_is_transparent = $rootScope.tabbar_is_transparent != null ? $rootScope.tabbar_is_transparent : $scope.tabbar_is_transparent;
                $window.changeIcon = function(id, url) {
                    angular.forEach($scope.features.options, function(option) {
                        if(option.id == id) {
                            $timeout(function() {
                                option.icon_url = url;
                            });
                        }
                    });
                };
                $window.toggleTabbarIsTransparent = function(value) {
                    $timeout(function() {
                        $rootScope.tabbar_is_transparent = value;
                        $scope.tabbar_is_transparent = value;
                    });
                };
            }

        }
    };
});

App.directive('tabbarItems', function ($timeout, $window) {
    return {
        restrict: 'A',
        scope: {
            option: '=',
            goToUrl: '&'
        },
        link: function(scope, element) {

            element.on("click", function () {
                sbLog("Clicked Option: ", scope.option);
                $timeout(function () {
                    scope.goToUrl(scope.option);
                });
            });

        }
    };
});