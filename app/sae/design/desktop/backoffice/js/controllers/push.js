App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/push/backoffice_certificate", {
        controller: 'PushController',
        templateUrl: BASE_URL+"/push/backoffice_certificate/template"
    });

}).controller("PushController", function($scope, Header, Push) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Push.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Push.findAll().success(function(push) {
        $scope.push = push;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.saveKeys = function() {

        $scope.form_loader_is_visible = true;

        Push.save($scope.push.keys).success(function(data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
        }).error(function(data) {

            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;

        }).finally(function() {
            $scope.form_loader_is_visible = false;
        });
    };

});
