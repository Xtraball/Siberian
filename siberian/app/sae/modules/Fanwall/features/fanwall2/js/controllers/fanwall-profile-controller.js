/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.6
 */
angular
.module("starter")
.controller("FanwallProfileController", function ($scope, $stateParams, Customer, FanwallPost) {
    angular.extend($scope, {
        isLoading: false,
        collection: [],
        hasMore: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.customerFullname = function () {
        var customer = Customer.customer;

        return customer.firstname + " " + customer.lastname;
    };

    $scope.profileCallback = function () {
        console.log("profile callback");
    };

    $scope.editProfile = function () {
        return Customer.loginModal(
            undefined,
            $scope.profileCallback,
            $scope.profileCallback,
            $scope.profileCallback);
    };

    $scope.showBlockedUsers = function () {
        // @todo Open modal wit blocked users, and allows to unblock!
        alert("showBlockedUsers");
    };

    $scope.moreActions = function () {
        // @todo Shows more actions? to define!
        alert("moreActions");
    };

    $scope.loadMore = function () {
        $scope.loadContent(true);
    };

    $scope.loadContent = function (loadMore) {
        FanwallPost
        .findAllProfile($scope.collection.length)
        .then(function (payload) {
            console.log("FanwallProfilePostController $scope.loadContent success", payload);
            $scope.collection = $scope.collection.concat(payload.collection);
            $scope.hasMore = $scope.collection.length < payload.total;
        }, function (payload) {
            console.error("findAllProfile", payload);
        }).then(function () {
            if (loadMore === true) {
                $scope.$broadcast("scroll.infiniteScrollComplete");
            }

            $scope.isLoading = false;
        });
    };

    $scope.loadContent(false);
});