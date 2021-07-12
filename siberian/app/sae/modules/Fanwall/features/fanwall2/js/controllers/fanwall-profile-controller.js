/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .controller('FanwallProfileController', function ($scope, $rootScope, $stateParams, $timeout,
                                                      Customer, FanwallUtils, FanwallPost, Lightbox) {
        angular.extend($scope, {
            isLoading: true,
            collection: [],
            customer: Customer.customer,
            hasMore: false
        });

        $scope.customerFullname = function () {
            return $scope.customer.firstname + ' ' + $scope.customer.lastname;
        };

        $scope.profileCallback = function () {
            // Do nothing!
            $scope.loadContent(true, false);
            $timeout(function () {
                $scope.customer = Customer.customer;
            });
        };

        $scope.customerImagePath = function () {
            // Empty image
            if ($scope.customer.image &&
                $scope.customer.image.length > 0) {
                return IMAGE_URL + 'images/customer' + $scope.customer.image;
            }
            return './features/fanwall2/assets/templates/images/customer-placeholder.png';
        };

        $scope.editProfile = function () {
            return Customer.loginModal(
                undefined,
                $scope.profileCallback,
                $scope.profileCallback,
                $scope.profileCallback);
        };

        $scope.showBlockedUsers = function () {
            FanwallUtils.showBlockedUsersModal();
        };

        $scope.loadMore = function () {
            $scope.loadContent(false, true);
        };

        $scope.loadContent = function (refresh, loadMore) {
            if (refresh) {
                $scope.isLoading = true;
                $scope.collection = [];
            }

            FanwallPost
                .findAllProfile($scope.collection.length)
                .then(function (payload) {
                    $scope.collection = $scope.collection.concat(payload.collection);
                    $scope.hasMore = $scope.collection.length < payload.total;

                    $scope.hasRejected = _.find($scope.collection, {'status': 'rejected'}) !== undefined;

                    $timeout(function () {
                        // We need a single instance for every post*
                        document
                            .querySelectorAll('.list-posts fanwall-post-item')
                            .forEach(function (item) {
                                Lightbox.run('[rel="fanwall-gallery-' + item.id + '"]');
                            });
                    }, 200);
                }, function (payload) {
                    // Error!
                }).then(function () {
                    if (loadMore === true) {
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                    }

                    $scope.isLoading = false;
                });
        };

        $rootScope.$on('fanwall.profile.reload', function () {
            $scope.loadContent(true, false);
        });

        $scope.loadContent(true, false);
    });
