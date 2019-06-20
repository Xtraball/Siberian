/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallCommentController", function ($scope, $translate, $filter, $timeout, Picture, Loader, Dialog,
                                                  Customer, FanwallPost, ModalScrollDelegate) {

    angular.extend($scope, {
        form: {
            text: "",
            picture: null
        },
        isSending: false
    });

    $scope.repeatDone = function () {
        $timeout(function () {
            ModalScrollDelegate
            .$getByHandle("commentsScroll")
            .scrollBottom(true);
        }, 200);
    };

    $scope.authorImagePath = function (image) {
        if (image.length <= 0) {
            return "./features/fanwall/assets/templates/images/customer-placeholder.png"
        }
        return IMAGE_URL + "images/customer" + image;
    };

    $scope.imagePath = function (image) {
        return IMAGE_URL + "images/application" + image;
    };

    $scope.authorName = function (author) {
        return author.firstname + " " + author.lastname;
    };

    $scope.publicationDate = function (comment) {
        return $filter("moment_calendar")(comment.date * 1000);
    };

    $scope.flagComment = function (comment) {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }

        var title = $translate.instant("Report this message!", "fanwall");
        var message = $translate.instant("Please let us know why you think this message is inappropriate.", "fanwall");
        var placeholder = $translate.instant("Your message.", "fanwall");

        Dialog
        .prompt(
            title,
            message,
            "text",
            placeholder)
        .then(function (value) {
            Loader.show();

            FanwallPost
            .reportComment(comment.id, value)
            .then(function (payload) {
                Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
            }, function (payload) {
                Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
            }).then(function () {
                Loader.hide();
            });
        });
    };

    $scope.takePicture = function () {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }

        if ($scope.isSending) {
            return;
        }

        Picture
        .takePicture()
        .then(function (success) {
            $scope.form.picture = success.image;
        });
    };

    $scope.clearComment = function () {
        $timeout(function () {
            $scope.form = {
                text: "",
                picture: null
            };
        });
    };

    $scope.showClearComment = function () {
        return ($scope.form.text.length > 0 || $scope.form.picture !== null);
    };

    $scope.sendComment = function () {
        if (!Customer.isLoggedIn()) {
            return Customer.loginModal();
        }

        // Prevent multiple submits & empty comments!
        if ($scope.isSending || !$scope.showClearComment()) {
            return;
        }

        $scope.isSending = true;

        FanwallPost
        .sendComment($scope.post.id, $scope.form)
        .then(function (payload) {
            $scope.clearComment();

            // Post is updated!
            $scope.post.comments = payload.comments;
            $scope.post.commentCount = payload.comments.length;
        }, function (payload) {
            // Show error!
            Dialog.alert("Error", payload.message, "OK", -1, "fanwall");
        }).then(function () {
            $scope.isSending = false;
        });
    };
});