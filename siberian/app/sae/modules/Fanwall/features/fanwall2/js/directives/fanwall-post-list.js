angular
.module("starter")
.directive("fanwallPostList", function ($timeout, Lightbox) {
    return {
        restrict: "E",
        replace: true,
        templateUrl: "features/fanwall2/assets/templates/l1/tabs/directives/post-list.html",
        link: function (scope) {
            scope.$watch("post", function () {
                // Updating local `post` instance
                console.log("Updating local post instance");
                scope._post = scope.post;
            });
        },
        controller: function ($scope) {
            $scope.listDidRender = function () {
                $timeout(function () {
                    Lightbox.run(".list-posts");
                }, 200);
            };
        }
    };
});
