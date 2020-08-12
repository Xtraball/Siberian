angular
.module("starter")
.service("ContextualMenu", function ($rootScope, $timeout, $q) {

    var service = {
        settings: {
            isReady: $q.defer(),
            isEnabled: false,
            width: 275,
            preferredSide: "right", // Will try to enforce side, if possible!
            templateUrl: null
        }
    };

    // -- DEPRECATED --
    // Backward compatibility methods!
    service.reset = function () {
        // Nope!
    };

    service.set = function (templateURL, width) {
        service.init(templateURL, width);
    };
    // Backward compatibility methods!
    // -- DEPRECATED --

    service.init = function (templateUrl, width, preferredSide) {

        // Test values
        if (["left", "right", undefined].indexOf(preferredSide) === -1) {
            console.error("ContextualMenu.init: invalid preferredSide, must be `left` or `right`.");
            return;
        }

        $timeout(function () {
            service.settings.isReady = $q.defer();
            service.settings.isEnabled = true;
            service.settings.templateUrl = templateUrl;
            service.settings.width = width;
            service.settings.preferredSide = (preferredSide === undefined) ?
                service.settings.preferredSide : preferredSide;

            $rootScope.$broadcast("contextualMenu.init");
        });
    };

    // To clear a contextual menu, one must pass the exact `templateUrl`,
    // to avoid removing the wrong one!
    service.clear = function () {
        $timeout(function () {
            service.settings.isReady = $q.defer();
            service.settings.isEnabled = false;
            service.settings.templateUrl = null;
            service.settings.width = 275;
            service.settings.preferredSide = "right"; // Will try to enforce side, if possible!

            $rootScope.$broadcast("contextualMenu.init");
        });
    };

    service.open = function () {
        service.settings.isReady.promise
        .then(function () {
            $timeout(function () {
                $rootScope.$broadcast("contextualMenu.open");
            }, 20);
        });
    };

    service.close = function () {
        service.settings.isReady.promise
        .then(function () {
            $rootScope.$broadcast("contextualMenu.close");
        });
    };

    service.toggle = function () {
        service.settings.isReady.promise
        .then(function () {
            $timeout(function () {
                $rootScope.$broadcast("contextualMenu.toggle");
            }, 20);
        });
    };
    return service;
});
