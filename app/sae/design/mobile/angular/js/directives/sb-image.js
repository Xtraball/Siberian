"use strict";

App.directive('sbImage', function($timeout, Application) {
    return {
        restrict: 'A',
        scope: {
            image_src: "=imageSrc"
        },
        template: '<div class="image_loader relative scale-fade" ng-hide="is_hidden"><span class="loader block"></span></div>',
        link: function(scope, element) {

            scope.setBackgroundImageStyle = function() {
                $timeout(function() {
                    element.css('background-image', 'url('+img.src+')');
                    scope.is_hidden = true;
                });
            }

            var img = new Image();
            img.src = scope.image_src;
            img.onload = function () {
                scope.setBackgroundImageStyle();
            };
            img.onerror = function () {
                scope.setBackgroundImageStyle();
            };
            if(img.complete) {
                scope.setBackgroundImageStyle();
            } else if(Application.is_ios) {
                scope.setBackgroundImageStyle();
            }

        },
        controller: function($scope) {
            $scope.is_hidden = false;
        }
    };
});