App.service("Connection", function($rootScope, $window, $http, $timeout, Application) {

    var service = {};

    service.isOnline = false;

    service.setIsOffline = function() {

        if(!$rootScope.isOnline) return;

        Application.call("setIsOnline", 0);

        this.isOnline = false;
        $rootScope.isOnline = false;

        $rootScope.$broadcast("connectionStateChange", {isOnline: false});

        console.log('offline confirmed');
    };

    service.setIsOnline = function() {

        if($rootScope.isOnline) return;

        Application.call("setIsOnline", 1);

        this.isOnline = true;
        $rootScope.isOnline = true;

        $rootScope.$broadcast("connectionStateChange", {isOnline: true});

        console.log('online confirmed');
    };

    service.check = function () {

        if(!$rootScope.isOnline && !$window.navigator.onLine) {
            return;
        }

        var url = "/check_connection.php?t=" + Date.now();

        $http({ method: 'HEAD', url: url })
            .success(function(response) {
                service.setIsOnline();
            }).error(function() {
                service.setIsOffline();
                $timeout(service.check, 3000);
            });

        return;
    };

    return service;
});