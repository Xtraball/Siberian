/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .controller('FanwallGalleryController', function ($rootScope, $scope, $state, $stateParams, $timeout, $ionicScrollDelegate,
                                                      Fanwall, FanwallGallery) {
        angular.extend($scope, {
            isLoading: false,
            collection: [],
            hasMore: false,
        });

        $scope.getCardDesign = function () {
            return Fanwall.getSettings().cardDesign;
        };

        $scope.getSettings = function () {
            return Fanwall.getSettings();
        };

        $scope.imagePath = function (image) {
            if (image.length <= 0) {
                return './features/fanwall2/assets/templates/images/placeholder.png'
            }
            return IMAGE_URL + 'images/application' + image;
        };

        $scope.loadMore = function () {
            $scope.loadContent(false, true);
        };

        $scope.loadContent = function (refresh, loadMore) {
            if (refresh === true) {
                $scope.isLoading = true;
                $scope.collection = [];

                $timeout(function () {
                    $ionicScrollDelegate.$getByHandle('mainScroll').scrollTop();
                });
            }

            FanwallGallery
                .findAll($scope.collection.length, refresh)
                .then(function (payload) {
                    $scope.collection = $scope.collection.concat(payload.collection);

                    $rootScope.$broadcast('fanwall.pageTitle', {pageTitle: payload.pageTitle});

                    $scope.hasMore = $scope.collection.length < payload.total;

                }, function (payload) {
                    // Error payload!
                }).then(function () {
                    if (loadMore === true) {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                    }

                    if (refresh === true) {
                        $scope.isLoading = false;
                    }
                });
        };

        $rootScope.$on('fanwall.refresh', function () {
            // Refresh only the 'active' tab
            if ($scope.currentTab === 'gallery') {
                $scope.loadContent(true);
            }
        });

        $scope.loadContent($scope.collection.length === 0);
    });
