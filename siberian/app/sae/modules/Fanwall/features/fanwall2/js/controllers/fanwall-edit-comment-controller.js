/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("FanwallEditCommentController", function ($scope, $rootScope, $stateParams, $translate,
                                                      Fanwall, FanwallPost, Dialog, Picture, Loader) {

    angular.extend($scope, {
        pageTitle: $translate.instant("Edit comment", "fanwall"),
        form: {
            text: "",
            picture: "",
            date: null,

        }
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.getCardDesign = function () {
        return Fanwall.cardDesign;
    };

    $scope.getSettings = function () {
        return Fanwall.settings;
    };

    $scope.picturePreview = function () {
        // Empty image
        if ($scope.form.picture.indexOf("/") === 0) {
            return IMAGE_URL + "images/application" + $scope.form.picture;
        }
        return $scope.form.picture;
    };

    $scope.takePicture = function () {
        return Picture
            .takePicture()
            .then(function (success) {
                $scope.form.picture = success.image;
            });
    };

    $scope.removePicture = function () {
        $scope.form.picture = "";
    };

    $scope.clearForm = function () {
        $scope.form = {
            text: "",
            picture: ""
        };
    };

    $scope.canSend = function () {
        return ($scope.form.text.length > 0 || $scope.form.picture.length > 0);
    };

    $scope.sendComment = function () {
        var commentId = $scope.comment.id;
        var postId = $scope.comment.postId;

        if (!$scope.canSend()) {
            Dialog.alert("Error", "You must send at least a message or a picture.", "OK", -1, "fanwall");
            return false;
        }

        Loader.show();

        // Append now
        $scope.form.date = Math.round(Date.now() / 1000);

        return FanwallPost
            .sendComment(postId, commentId, $scope.form)
            .then(function (payload) {
                Loader.hide();
                $rootScope.$broadcast("fanwall.refresh");
                $rootScope.$broadcast("fanwall.refresh.comments", {comments: payload.comments, postId: payload.postId});
                $scope.close();
            }, function (payload) {
                // Show error!
                Loader.hide();
                Dialog.alert("Error", payload.message, "OK", -1, "fanwall");
            });
    };

    if ($scope.comment !== undefined) {
        // Replace <br /> with \n for textarea, leave other formatting intact!
        $scope.form.text = $scope.comment.text.replace(/(<br( ?)(\/?)>)/gm, "\n");
        if ($scope.comment.image.length > 0) {
            $scope.form.picture = $scope.comment.image;
        }
    }
});
