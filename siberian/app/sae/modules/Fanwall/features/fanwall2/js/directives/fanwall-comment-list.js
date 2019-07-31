angular
.module("starter")
.directive("fanwallCommentList", function ($timeout, ModalScrollDelegate, Lightbox) {
    return {
        restrict: "E",
        replace: true,
        templateUrl: "features/fanwall2/assets/templates/l1/modal/directives/comment-list.html",
        link: function (scope) {
            scope.$watch("post", function () {
                // Updating local `post` instance
                scope._post = scope.post;
            });
        },
        controller: function ($rootScope, $scope) {
            $scope.scrollToBottom = function () {
                $timeout(function () {
                    ModalScrollDelegate
                        .$getByHandle("fanwall-comment-list")
                        .scrollBottom(true);

                    $timeout(function () {
                        Lightbox.run(".list-comments");
                    }, 200);
                }, 200);
            };

            $scope.listDidRender = function () {
                $scope.scrollToBottom();
            };

            $rootScope.$on("fanwall.refresh.comments", function (event, payload) {
                // Comments are updated!
                if (payload.postId === $scope.post.id) {
                    $timeout(function () {
                        $scope.post.comments = angular.copy(payload.comments);
                        $scope.post.commentCount = $scope.post.comments.length;
                    });
                }
            });
        }
    };
});
