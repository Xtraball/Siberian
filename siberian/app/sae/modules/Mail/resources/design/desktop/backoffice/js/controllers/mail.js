/**
 *
 */
App.config(function($routeProvider) {
    $routeProvider.when(BASE_URL + "/mail/backoffice_log", {
        controller: 'MailLogController',
        templateUrl: BASE_URL + "/mail/backoffice_log/template"
    });
}).controller('MailLogController', function($scope, Header, Mail) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;

    $scope.content_loader_is_visible = true;

    $scope.loadContent = function () {
        Mail
            .loadLogs()
            .success(function (data) {
                $scope.header.title = data.page.title;
                $scope.header.icon = data.page.icon;

                $scope.collection = data.collection;
            }).error(function () {
                // Something went wrong!
            }).finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.loadContent();
});
