angular
.module("starter")
.directive("fanwallGallery", function ($timeout, ModalScrollDelegate, Lightbox) {
    return {
        restrict: "E",
        replace: true,
        templateUrl: "features/fanwall2/assets/templates/l1/tabs/directives/gallery.html",
        controller: function ($scope) {
            $scope.listDidRender = function () {
                $timeout(function () {
                    Lightbox.run(".fanwall-gallery");
                }, 200);
            };

            $scope.imagePath = function (item) {
                return IMAGE_URL + "images/application" + item.image;
            };
        }
    };
});
