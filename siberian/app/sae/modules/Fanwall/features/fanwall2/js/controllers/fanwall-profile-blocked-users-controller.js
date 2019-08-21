/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.6
 */
angular
.module("starter")
.controller("FanwallProfileBlockedUsersController", function ($scope, $rootScope, $stateParams, FanwallPost) {
    angular.extend($scope, {
        isLoading: true,
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        $scope.isLoading = true;

        FanwallPost
        .findAllBlocked()
        .then(function (payload) {
            $scope.collection = payload.collection;
        }, function (payload) {
            // Error!
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.loadContent();

    $rootScope.$on("fanwall.blockedUsers.refresh", function () {
        $scope.loadContent();
    });
});