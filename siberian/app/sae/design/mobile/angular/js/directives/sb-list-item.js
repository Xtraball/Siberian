"use strict";

App.directive('sbListItem', function ($timeout, Application) {
    return {
        restrict: 'A',
        scope: {
            item: '=',
            showItem: '&'
        },
        link: function(scope, element, attrs) {
            if(scope.item.action_value && (scope.item.action_value.startsWith("http://") || scope.item.action_value.startsWith("https://"))) {
                if(!Application.is_android) element.prop("target", "_blank");
                else element.prop("target", "_parent");

                    element.prop("href", scope.item.action_value);
            } else {
                element.prop("href", "javascript:void(0);");
                element.on("click", function () {
                    $timeout(function () {
                        scope.showItem(scope.item);
                    });
                });
            }
        }
    };
});