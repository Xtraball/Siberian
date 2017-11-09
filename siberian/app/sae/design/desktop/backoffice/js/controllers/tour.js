App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice/tour_settings", {
        controller: 'TourController',
        templateUrl: BASE_URL+"/backoffice/tour_settings/template"
    });

}).controller("TourController", function($scope, Header, Tour) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Tour.load().success(function(data) {
        $scope.header.title = data.header.title;
        $scope.header.icon = data.header.icon;
        $scope.content_loader_is_visible = false;

        $scope.admin = data.admin;
        $scope.tour = data.tour;
    });

    $scope.loginAs = function() {
        $scope.content_loader_is_visible = true;
        Tour.loginAs($scope.admin.email).success(function(data) {
            window.localStorage.setItem("sb-tour", true);
            window.open(data.url);
        }).error(function(data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.content_loader_is_visible = false;
        });
    };

    $scope.changeStatus = function() {
        $scope.content_loader_is_visible = true;
        Tour.setStatus($scope.tour.is_active).success(function(data) {
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
            $scope.content_loader_is_visible = false;
        });
    }

});
