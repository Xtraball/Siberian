"use strict";

App.directive('layoutSidebar', function (LayoutService) {
    return {
        restrict: 'A',
        template: '<div ng-show="isVisible()" class="" ng-class="getClass()" >\
                        <div class="items" tabbar ng-class="{\'transparent-background\': tabbar_is_transparent}"></div>\
                    </div>',
        replace: true,
        scope: {},
        link: function ($scope, element, attrs) {

            $scope.isVisible = function () {
                return LayoutService.isInitialized;// && LayoutService.properties.menu.isVisible;
            };

            $scope.getClass = function () {

                if(!LayoutService.isInitialized()) return "";

                $scope.tabbar_is_transparent = LayoutService.properties.tabbar_is_transparent;

                var classes = new Array();
                classes.push('layout-' + LayoutService.properties.menu.position + '-sidebar');

                if(LayoutService.properties.layoutId) {
                    classes.push('layout-' + LayoutService.properties.layoutId);
                }

                if(LayoutService.properties.layoutId == "l1" && LayoutService.properties.menu.visibility != "homepage") {
                    classes.push("slide-top");
                } else if(LayoutService.properties.layoutId == "l9") {
                    classes.push("slide-left scrollable_content");
                }

                return classes.join(" ");
            };

        }
    };
});