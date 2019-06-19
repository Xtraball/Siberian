/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallCommentController", function ($scope, $translate, Loader, Dialog, FanwallPost) {

    $scope.authorImagePath = function (image) {
        if (image.length <= 0) {
            return "./features/fanwall/assets/templates/images/customer-placeholder.png"
        }
        return IMAGE_URL + "images/customer" + image;
    };

    $scope.authorName = function (author) {
        return author.firstname + " " + author.lastname;
    };

    $scope.publicationDate = function (comment) {
        return moment(comment.date * 1000).calendar();
    };

    $scope.flagComment = function (comment) {
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
});