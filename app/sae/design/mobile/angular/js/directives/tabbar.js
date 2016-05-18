"use strict";

App.directive('tabbar', function ($window, $rootScope, $timeout, $location, LayoutService, Pages, Application, Push, AUTH_EVENTS, PUSH_EVENTS, Url) {
    return {
        restrict: 'A',
        templateUrl: Url.get('/front/mobile_home/menu'),
        scope: {},
        link: function ($scope, element, attrs) {

            $scope.tabbar_is_visible = Pages.is_loaded;
            $scope.animate_tabbar = !$scope.tabbar_is_visible;
            $scope.pages_list_is_visible = false;

            $scope.layout = LayoutService;

            $scope.loadContent = function() {

                LayoutService.getOptions().then(function (options) {
                    $scope.options = options;
                });

                LayoutService.getFeatures().then(function (features) {

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
                            //click: function () {
                            //    $scope.tabbar_is_visible = false;
                            //    $scope.pages_list_is_visible = true;
                            //}
                        });

                        //If layout 10, we need to reorder icons in order to have the more button at middle
                        if($scope.features.layoutId == "l10") {
                            var third_option = features.overview.options[2];
                            var fourth_option = features.overview.options[3];
                            features.overview.options[2] = features.overview.options[4];
                            features.overview.options[3] = third_option;
                            features.overview.options[4] = fourth_option;
                            features.options = features.options.slice(4,features.options.length);
                        }

                    }

                    $timeout(function () {
                        if(!Pages.is_loaded) {
                            Pages.is_loaded = true;
                            $scope.tabbar_is_visible = true;
                        }
                    }, 500);

                });

                $scope.close_previewer_button_is_visible = Application.is_previewer && Application.is_ios;
            };

            $scope.closeList = function () {
                $scope.tabbar_is_visible = true;
                $scope.pages_list_is_visible = false;
            };

            $scope.goToUrl = function(option) {
                if(option.code == "tabbar_more") {
                    $scope.tabbar_is_visible = false;
                    $scope.pages_list_is_visible = true;
                } else {
                    if (option.code == "code_scan") {
                        $window.scan_camera_protocols = JSON.stringify(["tel:", "http:", "https:", "geo:", "ctc:"]);
                        Application.openScanCamera({protocols: ["tel:", "http:", "https:", "geo:", "ctc:"]}, function (qrcode) {}, function () {});
                    } else {
                        $location.path(option.path);
                    }

                    if ($scope.pages_list_is_visible) {
                        $scope.closeList();
                    }
                }
            };

            $scope.closePreviewer = function() {
                $window.location = "app:closeApplication";
            };

            $scope.loadContent();

            if($rootScope.isOverview) {
                $scope.$on("tabbarStatesChanged", function() {
                    if(!LayoutService.isInitialized()) {
                        $scope.loadContent();
                    }
                });
            }

            if(Application.is_android) {
                $rootScope.$on("ready_for_code_scan", function() {
                    $timeout(function() {
                        LayoutService.rebuildOptions();
                        $scope.loadContent();
                    });
                });
            }

            $rootScope.$on(PUSH_EVENTS.unreadPushs, function() { $scope.pushs = Push.pushs; });
            $rootScope.$on(PUSH_EVENTS.readPushs, function() { $scope.pushs = 0; });

            $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() { $timeout(function() {$scope.loadContent();}); });
            $rootScope.$on(AUTH_EVENTS.loginSuccess, function() { $timeout(function() {$scope.loadContent();}); });

            if($rootScope.isOverview) {
                $window.changeIcon = function(id, url) {
                    angular.forEach($scope.features.options, function(option) {
                        if(option.id == id) {
                            $timeout(function() {
                                option.icon_url = url;
                            });
                        }
                    });
                };
            }
        }
    };
});

App.directive('tabbarItems', function ($timeout, Application) {
    return {
        restrict: 'A',
        scope: {
            option: '=',
            goToUrl: '&'
        },
        link: function(scope, element, attrs) {
            if(scope.option.url) {
                if(scope.option.is_link) {
                    if(!Application.is_android) element.prop("target", "_blank");
                    else element.prop("target", "_parent");

                    element.prop("href", scope.option.url);
                } else {
                    element.prop("href", "javascript:void(0);");
                    element.on("click", function () {
                        $timeout(function () {
                            scope.goToUrl(scope.option);
                        });
                    });
                }
            }
        }
    };
});