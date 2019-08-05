/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.6
 */
angular
.module("starter")
.controller("FanwallProfileController", function ($scope, $stateParams, Customer, FanwallUtils, FanwallPost) {
    angular.extend($scope, {
        isLoading: true,
        collection: [],
        customer: Customer.customer,
        hasMore: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.customerFullname = function () {
        return $scope.customer.firstname + " " + $scope.customer.lastname;
    };

    $scope.profileCallback = function () {
        console.log("profile callback");
    };

    $scope.customerImagePath = function () {
        // Empty image
        if ($scope.customer.image.length <= 0) {
            return "./features/fanwall2/assets/templates/images/customer-placeholder.png";
        }
        return IMAGE_URL + "images/customer" + $scope.customer.image;
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
        $scope.loadContent(true);
    };

    $scope.loadContent = function (loadMore) {
        FanwallPost
        .findAllProfile($scope.collection.length)
        .then(function (payload) {
            $scope.collection = $scope.collection.concat(payload.collection);
            $scope.hasMore = $scope.collection.length < payload.total;
        }, function (payload) {
            // Error!
        }).then(function () {
            if (loadMore === true) {
                $scope.$broadcast("scroll.infiniteScrollComplete");
            }

            $scope.isLoading = false;
        });
    };

    $scope.loadContent(false);
});