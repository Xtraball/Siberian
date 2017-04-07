"use strict";

App.directive('sbTabbar', function ($sbhttp, $ionicHistory, $ionicModal, $ionicSlideBoxDelegate, $ionicSideMenuDelegate, $location, $rootScope, $timeout, $translate, $window, $ionicPlatform, Analytics, Application, Customer, Dialog, HomepageLayout, LinkService, Pages, Url, AUTH_EVENTS, PADLOCK_EVENTS, PUSH_EVENTS) {
    return {
        restrict: 'A',
        templateUrl: function() {
            return HomepageLayout.getTemplate();
        },
        scope: {},
        link: function ($scope, element, attrs) {

            $scope.tabbar_is_visible            = Pages.is_loaded;
            $scope.tabbar_is_transparent        = HomepageLayout.properties.tabbar_is_transparent;
            $scope.animate_tabbar               = !$scope.tabbar_is_visible;
            $scope.pages_list_is_visible        = false;
            $scope.active_page                  = 0;

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
                        var feat_index = 0;
                        for(var fi = 0; fi < features.options.length; fi++) {
                            var feat = features.options[fi];
                            /** Don't load unwanted features on first page. */
                            if((feat.code !== "code_scan") && (feat.code !== "radio") && (feat.code !== "padlock")) {
                                feat_index = fi;
                                break;
                            }
                        }

                        if(features.options[feat_index].path != $location.path()) {
                            $ionicHistory.nextViewOptions({
                                historyRoot: true,
                                disableAnimate: false
                            });

                            $location.path(features.options[feat_index].path).replace();
                        }

                    }

                });
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

                    if(feature.code !== "padlock") { /** do not clear history if we open the padlock */
                        if(feature.path !== $location.path()) {
                            $ionicHistory.nextViewOptions({
                                historyRoot: true,
                                disableAnimate: false
                            });
                        }
                    }
                    break;
                    default:
                }

                if(attrs.isDisabled) {
                    return false;
                }

                if(feature.code === "tabbar_account") {
                    var account_feature = { id: 0 };
                    Analytics.storePageOpening(account_feature);
                    $scope.login();
                } else if(feature.code === "tabbar_more") {
                    $scope.tabbar_is_visible = false;
                    $scope.pages_list_is_visible = true;
                    $scope.more();
                } else if($rootScope.isOffline && feature.offline_mode !== true) {
                    $rootScope.onlineOnly();
                    return false;
                } else if(feature.is_link) {
                    var options = {
                        "hide_navbar" : !!feature.hide_navbar,
                        "use_external_app" : !!feature.use_external_app
                    };
                    LinkService.openLink(feature.url, options);
                    Analytics.storePageOpening(feature);
                } else {
                    Analytics.storePageOpening(feature);


                    if (!Application.is_customizing_colors && HomepageLayout.properties.options.autoSelectFirst) {

                        if(feature.path !== $location.path()) {
                            $ionicHistory.nextViewOptions({
                                historyRoot: true,
                                disableAnimate: false
                            });
                            $location.path(feature.path).replace();
                        }

                    } else {
                        $location.path(feature.path);
                    }
                }

            };

            $scope.gotoPage = function(index) {
                $ionicSlideBoxDelegate.$getByHandle('slideBoxLayout').slide(index);
            };

            $scope.closePreviewer = function() {
                $window.location = "app:closeApplication";
            };

            $scope.login = function($scope) {
                $rootScope.loginFeature = null;
                Customer.loginModal($scope);
            };

            $scope.loadContent();

            if($rootScope.isOverview) {
                $scope.$on("tabbarStatesChanged", function() {
                    if(!HomepageLayout.isInitialized()) {
                        var device_uid = null;
                        if ($window.device) {
                            device_uid = $window.device.uuid;
                        }
                        $sbhttp.get(Url.get("front/mobile/loadv2", {
                            add_language: true,
                            sid: localStorage.getItem("sb-auth-token"),
                            device_uid: device_uid
                        }), {timeout: 5000}).then(function (response) {
                            Pages.data = response.data.homepage;
                            $scope.loadContent();
                        });

                    }
                });
            }

            $rootScope.$on(PUSH_EVENTS.unreadPushs, function(event, args) {
                $timeout(function() {
                    $scope.push_badge = args;
                });
            });

            $rootScope.$on(PUSH_EVENTS.readPushs, function() {
                $scope.push_badge = 0;
            });

            $scope.modalUrl = HomepageLayout.getModalTemplate();

            $scope.more = function() {
                $ionicModal.fromTemplateUrl($scope.modalUrl, {
                    scope: $scope,
                    animation: "slide-in-up"
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

            /* pages_list_is_visible is true means that the ... button in the main menu was clicked */
            $ionicPlatform.onHardwareBackButton(function(e){
                if($scope.pages_list_is_visible){
                    $scope.closeMore();
                }
            });

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
                $scope.tabbar_is_transparent = ($rootScope.tabbar_is_transparent !== null) ? $rootScope.tabbar_is_transparent : $scope.tabbar_is_transparent;
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

App.directive('tabbarItems', function ($rootScope, $timeout, $log) {
    return {
        restrict: 'A',
        scope: {
            option: '=',
            goToUrl: '&'
        },
        link: function(scope, element) {

            element.on("click", function () {
                $log.debug("Clicked Option: ", scope.option);
                $rootScope.$broadcast("OPTION_POSITION", scope.option.position);
                $rootScope.$broadcast("CLICKED_OPTION", scope.option);
                $timeout(function () {
                    scope.goToUrl(scope.option);
                });
            });

        }
    };
});
