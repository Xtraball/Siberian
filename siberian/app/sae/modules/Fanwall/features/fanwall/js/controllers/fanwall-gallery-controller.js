/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallGalleryController", function ($pwaRequest, $scope, $state, $stateParams, FanwallPost) {
    angular.extend($scope, {
        isLoading: true,
        value_id: $stateParams.value_id,
        cardDesign: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    FanwallPost
    .findAllPhotos()
    .then(function (data) {
        $scope.collection = data.collection;
    }).then(function () {
        $scope.isLoading = false;
    });

    $scope.goToPost = function (item) {
        $state.go("newswall-view", {
            value_id: $stateParams.value_id,
            comment_id: item.id
        });
    };
});