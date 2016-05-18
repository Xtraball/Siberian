"use strict";

App.directive("sbImageGallery", function($window) {
    return {
        restrict: 'A',
        scope: {
            gallery: "="
        },
        replace: true,
        template:
            '<div class="gallery fullscreen">'
                +'<ul class="block" rn-carousel rn-carousel-index="gallery.index" rn-click="true">'
                    +'<li ng-repeat="image in gallery.images">'
                        +'<div class="title" ng-if="image.title"><p>{{ image.title }}</p></div>'
                        +'<div sb-image image-src="image.src" ng-style="style_height"></div>'
                        +'<div class="description" ng-if="image.description"><p>{{ image.description }}</p></div>'
                    +'</li>'
                +'</ul>'
            +'</div>',
        link: function(scope, element) {
            scope.rnClick = function(index) {
                $window.history.back();
                scope.gallery.hide(index);
            };
            scope.style_height = {height: $window.innerHeight+"px"};
            scope.$on('$destroy', function() {
                scope.gallery.hide(0);
            });
        },
        controller: function($scope) {
            if(angular.isDefined($scope.gallery)) {
                $scope.current_index = $scope.gallery.index;
            }
        }
    };
});