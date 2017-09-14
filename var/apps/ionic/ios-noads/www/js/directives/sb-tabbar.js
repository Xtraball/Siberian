/* global
 App, angular
 */

angular.module('starter').directive('sbTabbar', function ($pwaRequest, $ionicHistory, Modal, $ionicSlideBoxDelegate,
                                    $ionicSideMenuDelegate, $location, $rootScope, $session, $timeout,
                                    $translate, $window, $ionicPlatform, Analytics, Application,
                                    Customer, Dialog, HomepageLayout, LinkService, Pages, Url, SB) {
    return {
        restrict: 'A',
        templateUrl: function () {
            return HomepageLayout.getTemplate();
        },
        scope: {},
        link: function ($scope, element, attrs) {
            $scope.tabbar_is_visible = Pages.is_loaded;
            $scope.tabbar_is_transparent = HomepageLayout.properties.tabbar_is_transparent;
            $scope.animate_tabbar = !$scope.tabbar_is_visible;
            $scope.pages_list_is_visible = false;
            $scope.active_page = 0;
            $scope.card_design = false;

            $scope.layout = HomepageLayout;

            $scope.loadContent = function () {
                HomepageLayout.getOptions()
                    .then(function (options) {
                        $scope.options = options;
                    });

                HomepageLayout.getData()
                    .then(function (data) {
                        $scope.data = data;
                        $scope.push_badge = data.push_badge;
                    });

                HomepageLayout.getFeatures()
                    .then(function (features) {
                        // filtered active options!
                        $scope.features = features;

                        $timeout(function () {
                            if (!Pages.is_loaded) {
                                Pages.is_loaded = true;
                                $scope.tabbar_is_visible = true;
                            }
                        }, 500);

                        // Load first feature is needed!
                        if ($rootScope.loginFeature === true) {
                            if ($rootScope.loginFeatureBack === true) {
                                $ionicHistory.goBack();
                            } else {
                                $rootScope.loginFeatureBack = false;
                            }
                            $rootScope.loginFeature = null;
                        } else if (!Application.is_customizing_colors &&
                            HomepageLayout.properties.options.autoSelectFirst &&
                            (features.first_option !== false)) {
                            var feat_index = 0;
                            for (var fi = 0; fi < features.options.length; fi = fi + 1) {
                                var feat = features.options[fi];
                                // Don't load unwanted features on first page!
                                if ((feat.code !== 'code_scan') &&
                                    (feat.code !== 'radio') &&
                                    (feat.code !== 'padlock')) {
                                    feat_index = fi;
                                    break;
                                }
                            }

                            if (features.options[feat_index].path !== $location.path()) {
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

            $scope.gotoPage = function (index) {
                $ionicSlideBoxDelegate.$getByHandle('slideBoxLayout').slide(index);
            };

            $scope.closePreviewer = function () {
                $window.location = 'app:closeApplication';
            };

            $scope.login = function ($scope) {
                $rootScope.loginFeature = null;
                Customer.loginModal($scope);
            };

            $scope.loadContent();

            if ($rootScope.isOverview) {
                $scope.$on('tabbarStatesChanged', function () {
                    $scope.loadContent();
                });
            }

            $rootScope.$on(SB.EVENTS.CACHE.layoutReload, function () {
                $scope.loadContent();
            });

            $rootScope.$on(SB.EVENTS.PUSH.unreadPushs, function (event, args) {
                $timeout(function () {
                    $scope.push_badge = args;
                });
            });

            $rootScope.$on(SB.EVENTS.PUSH.readPushs, function () {
                $scope.push_badge = 0;
            });

            var rebuildOptions = function () {
                $timeout(function () {
                    HomepageLayout.setNeedToBuildTheOptions(true);
                    $scope.loadContent();
                });
            };

            $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
                rebuildOptions();
            });
            $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
                rebuildOptions();
            });
            $rootScope.$on(SB.EVENTS.PADLOCK.unlockFeatures, function () {
                rebuildOptions();
            });
            $rootScope.$on(SB.EVENTS.CACHE.pagesReload, function () {
                rebuildOptions();
            });

            Application.loaded
                .then(function () {
                    rebuildOptions();
                });

            if ($rootScope.isOverview) {
                $window.changeIcon = function (id, url) {
                    angular.forEach($scope.features.options, function (option) {
                        if (option.id == id) {
                            $timeout(function () {
                                option.icon_url = url;
                            });
                        }
                    });
                };
            }
        }
    };
});

angular.module('starter').directive('tabbarItems', function ($rootScope, $timeout, $log, HomepageLayout) {
    return {
        restrict: 'A',
        scope: {
            option: '='
        },
        link: function (scope, element) {
            element.on('click', function () {
                $log.debug('Clicked Option: ', scope.option);
                $rootScope.$broadcast('OPTION_POSITION', scope.option.position);

                $timeout(function () {
                    HomepageLayout.openFeature(scope.option, scope);
                });
            });
        }
    };
});
