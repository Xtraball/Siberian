App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/api/backoffice_key_list", {
        controller: 'ApiKeyController',
        templateUrl: BASE_URL+"/api/backoffice_key_list/template"
    });

}).controller("ApiKeyController", function($scope, Header, ApiKey, Label) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;

    ApiKey.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    ApiKey.findAll().success(function(data) {
        $scope.apis = data.apis;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.saveKeys = function() {

        $scope.form_loader_is_visible = true;

        ApiKey.save($scope.apis).success(function(data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
        }).error(function(data) {
            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.form_loader_is_visible = false;
        });
    }

});
