App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice/notif_list", {
        controller: 'NotifListController',
        templateUrl: BASE_URL+"/backoffice/notif_list/template"
    }).when(BASE_URL+"/backoffice/notif_message/message_id/:message_id", {
        controller: 'NotifMessageController',
        templateUrl: BASE_URL+"/backoffice/notif_message/template"
    });

}).controller("NotifListController", function($scope, Header, Notif) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Notif.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Notif.findAll().success(function(data) {
        $scope.notifs = data.notifs;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.markAs = function(notif) {

        if(notif.loader_is_visible) {
            return;
        }
        notif.loader_is_visible = true;

        Notif.markAs(notif).success(function(data) {
            notif.is_read = data.is_read;
        }).finally(function() {
            notif.loader_is_visible = false;
        });

    };

    $scope.markAllRead = function() {
        $scope.content_loader_is_visible = true;
        Notif.markAllRead().success(function(data) {
            $scope.content_loader_is_visible = false;
            $scope.notifs.forEach(function(notif) {
                notif.is_read = true;
            });
        }).finally(function() {
            $scope.content_loader_is_visible = false;
        });
    };

}).controller("NotifMessageController", function($location, $scope, $routeParams, Header, Notif, Backoffice) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Notif.loadMessageData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Notif.findMessage($routeParams.message_id).success(function(data) {
        $scope.notif = data.notif;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.removeLocks = function() {
        $scope.content_loader_is_visible = true;
        Backoffice.clearCache("generator").success(function (data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.content_loader_is_visible = false;
        });
    };

    $scope.openSettings = function() {
        $scope.content_loader_is_visible = true;
        $location.path("/backoffice/advanced_configuration");
    };

    $scope.androidSdkRestart = function() {
        $scope.content_loader_is_visible = true;
        Backoffice.clearCache("android_sdk").success(function (data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.content_loader_is_visible = false;
        });
    };

});
