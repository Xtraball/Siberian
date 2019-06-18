/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallEditController", function (Location, $pwaRequest, $ionicActionSheet, $rootScope, $scope, $state,
                                               $stateParams, $timeout, $translate, Application, Dialog, FanwallPost,
                                               FanwallComment, Picture, Loader) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        new_post: {
            "text": null
        },
        preview_src: null,
        readyToPost: false,
        cardDesign: false
    });

    FanwallPost.setValueId($stateParams.value_id);
    FanwallComment.setValueId($stateParams.value_id);

    $scope.sendPost = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if (!$scope.new_post.text) {
            Dialog.alert("Error", "You have to enter a text message.", "OK", -1);
            return;
        }

        Loader.show();

        FanwallPost
        .createComment($scope.new_post.text, $scope.preview_src, $scope.position)
        .then(function (data) {
            Newswall.findAll(0, true);

            $state.go("newswall-list", {
                value_id: $scope.value_id
            });
        }, function (data) {
            Dialog.alert("Error", data.message, "OK", -1)
                .then(function () {
                    return true;
                });
        }).then(function () {
            Loader.hide();
        });

        $scope.readyToPost = false;
    };

    /**
     * Error message is handled by the Picture service.
     */
    $scope.takePicture = function () {
        Picture.takePicture()
            .then(function (response) {
                $scope.preview_src = response.image;
            });
    };

    Location.getLocation()
        .then(function (position) {
            $scope.position = position.coords;
        }, function (error) {
            $scope.position = null;
        });
});
