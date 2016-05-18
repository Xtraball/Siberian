"use strict";

App.directive('sbCheckConnection', function ($rootScope) {
    return {
        link: function ($scope, element) {

            $rootScope.$on("connectionStateChange",function(event, args) {
                if(args.isOnline) {
                    element.removeClass("is-offline");
                } else {
                    element.addClass("is-offline");
                }
            });
        }
    };
});