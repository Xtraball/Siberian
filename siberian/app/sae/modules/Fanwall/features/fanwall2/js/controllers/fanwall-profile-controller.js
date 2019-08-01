/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.6
 */
angular
.module("starter")
.controller("FanwallPRofileController", function ($scope, $stateParams, FanwallPost) {
    angular.extend($scope, {
        isLoading: false,
        collection: [],
        hasMore: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.loadContent = function (refresh, loadMore) {
        $scope.isLoading = false;
    };

    $scope.loadContent();
});