App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice/notif_list", {
        controller: 'NotifListController',
        templateUrl: BASE_URL+"/backoffice/notif_list/template"
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

    }

});
