angular
.module("starter")
.directive("fanwallCommentItem", function ($interval, $filter, $timeout, $translate, Customer, Dialog, Loader, Fanwall,
                                           FanwallPost) {
        return {
            restrict: 'E',
            templateUrl: "features/fanwall/assets/templates/l1/modal/directives/comment-item.html",
            controller: function ($scope) {
                $scope.getCardDesign = function () {
                    return Fanwall.cardDesign;
                };

                $scope.getSettings = function () {
                    return Fanwall.settings;
                };

                $scope.authorImagePath = function () {
                    if ($scope.comment.author.image.length <= 0) {
                        return "./features/fanwall/assets/templates/images/customer-placeholder.png"
                    }
                    return IMAGE_URL + "images/customer" + $scope.comment.author.image;
                };

                $scope.imagePath = function () {
                    return IMAGE_URL + "images/application" + $scope.comment.image;
                };

                $scope.authorName = function () {
                    return $scope.comment.author.firstname + " " + $scope.comment.author.lastname;
                };

                $scope.publicationDate = function () {
                    return $filter("moment_calendar")($scope.comment.date * 1000);
                };

                $scope.flagComment = function (comment) {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    var title = $translate.instant("Report this message!", "fanwall");
                    var message = $translate.instant("Please let us know why you think this message is inappropriate.", "fanwall");
                    var placeholder = $translate.instant("Your message.", "fanwall");

                    return Dialog
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
            }
        };
    });


