angular
.module("starter")
.directive("fanwallCommentList", function ($timeout, ModalScrollDelegate, Lightbox) {
    return {
        restrict: "E",
        replace: true,
        templateUrl: "features/fanwall/assets/templates/l1/modal/directives/comment-list.html",
        controller: function ($scope) {
            console.log("fanwallCommentList my post is", $scope.post);

            $scope.scrollToBottom = function () {
                ModalScrollDelegate
                .$getByHandle("fanwall-comment-list")
                .scrollBottom(true);

                Lightbox.run(".list-comments");
            };

            $scope.listDidRender = function () {
                $scope.scrollToBottom();
            };
        }
    };
});
