App.service("Connection", function($cordovaDialogs, $http, $rootScope, $timeout, $translate, $window, Application, Dialog) {

    var service = {};

    $rootScope.isOnline = service.isOnline = true;
    service.show_popup = null;

    service.setIsOffline = function() {

        if(!$rootScope.isOnline) return;

        this.isOnline = false;
        $rootScope.isOnline = false;

        if(!Application.is_webview) {
            OfflineMode.useCache("1", function () {

                if(!service.show_popup) {
                    service.show_popup = true;

                    Dialog.alert($translate.instant("Info"), $translate.instant("You have gone offline"), $translate.instant("OK")).then(function() {
                        service.show_popup = null;
                    });
                }

                $rootScope.$broadcast("connectionStateChange", {isOnline: false});
            }, null);
        }

        sbLog('offline confirmed');
    };

    service.setIsOnline = function() {

        if($rootScope.isOnline) return;

        this.isOnline = true;
        $rootScope.isOnline = true;

        if(!Application.is_webview) {
            OfflineMode.useCache("0", function() {
                $rootScope.$broadcast("connectionStateChange", {isOnline: true});
            }, null);
        }

        sbLog('online confirmed');
    };

    service.check = function () {

        if(!$rootScope.isOnline && !$window.navigator.onLine) {
            return;
        }

        var url = DOMAIN + "/check_connection.php?t=" + Date.now();
        var result = false;

        $http({ method: 'HEAD', url: url })
            .success(function(response) {
                service.setIsOnline();
                result = true;

            }).error(function() {
            service.setIsOffline();
            $timeout(service.check, 3000);
        });

        return result;
    };

    return service;
});