angular.module('starter')
    .directive('fanwallCommentForm', function ($timeout, Customer, Dialog, Picture, FanwallPost) {
        return {
            restrict: 'E',
            replace: true,
            templateUrl: "features/fanwall2/assets/templates/l1/modal/directives/comment-form.html",
            controller: function ($scope) {
                angular.extend($scope, {
                    form: {
                        text: "",
                        date: null,
                        picture: null
                    },
                    isSending: false
                });

                $scope.takePicture = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    if ($scope.isSending) {
                        return false;
                    }

                    return Picture
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

                $scope.instantAppend = function (text) {
                    var now = Math.round(Date.now() / 1000);
                    var comment = {
                        id: now,
                        postId: $scope.post.id,
                        customerId: Customer.customer.id,
                        text: text.replace(/(\r\n|\n\r|\r|\n)/g, "<br />"),
                        image: "",
                        isFlagged: false,
                        date: now,
                        history: [],
                        author: {
                            firstname: Customer.customer.firstname,
                            lastname: Customer.customer.lastname,
                            nickname: Customer.customer.nickname,
                            image: Customer.customer.image
                        }
                    };

                    $scope.post.comments.push(comment);
                };

                $scope.sendComment = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    // Prevent multiple submits & empty comments!
                    if ($scope.isSending || !$scope.showClearComment()) {
                        return false;
                    }

                    // Append now
                    $scope.form.date = Math.round(Date.now() / 1000);

                    // Instantly append post
                    $scope.instantAppend($scope.form.text);

                    $scope.isSending = true;

                    return FanwallPost
                    .sendComment($scope.post.id, null, $scope.form)
                    .then(function (payload) {
                        $scope.clearComment();

                        // Post is updated!
                        $timeout(function () {
                            $scope.post.comments = angular.copy(payload.comments);
                            $scope.post.commentCount = $scope.post.comments.length;
                        });

                    }, function (payload) {
                        // Show error!
                        Dialog.alert("Error", payload.message, "OK", -1, "fanwall");

                        $scope.post.comments.pop();

                    }).then(function () {
                        $scope.isSending = false;
                    });
                };
            }
        };
    });
