App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL + '/firewall', {
        controller: 'FirewallIndexController',
        templateUrl: BASE_URL + '/firewall/index/template'
    });

}).controller("FirewallIndexController", function($scope, $routeParams, Firewall, Header) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form = {
        newExtension: ''
    };

    $scope.loadContent = function () {
        Firewall
            .findAll()
            .success(function(data) {
                $scope.header.title = data.title;
                $scope.header.icon = data.icon;
                $scope.fw_upload_rules = data.fw_upload_rules;
            }).finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.deleteFwUploadRule = function (value) {
        $scope.content_loader_is_visible = true;

        Firewall
        .deleteFwUploadRule(value)
        .success(function(data) {
            $scope.loadContent();
            $scope.message.onSuccess(data);
        }).error(function(data) {
            $scope.message.onError(data);
        }).finally(function () {
            $scope.content_loader_is_visible = false;
        });
    };

    $scope.addFwUploadRule = function () {
        $scope.content_loader_is_visible = true;

        Firewall
            .addFwUploadRule($scope.form.newExtension)
            .success(function(data) {
                $scope.message.onSuccess(data);
                $scope.loadContent();
            }).error(function(data) {
                $scope.message.onError(data);
            }).finally(function () {
                $scope.content_loader_is_visible = false;

                $scope.form.newExtension = '';
            });
    };

    $scope.loadContent();
});
