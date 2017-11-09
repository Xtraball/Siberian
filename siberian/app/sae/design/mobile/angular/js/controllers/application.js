App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/application/mobile_customization_colors", {
        controller: 'ApplicationCustomizationController',
        templateUrl: BASE_URL+"/application/mobile_customization_colors/template",
        code: "application"
    }).when(BASE_URL+"/application/mobile_tc_view/index/tc_id/:tc_id", {
        controller: 'ApplicationTcController',
        templateUrl: BASE_URL+"/application/mobile_tc_view/template",
        code: "application"
    });

}).controller('ApplicationCustomizationController', function($window, $scope, $timeout) {

    $scope.is_loading = true;
    $scope.show_mask = false;
    $scope.elements = {
        "header": false,
        "subheader": false

    };

    $window.showMask = function(code) {

        $timeout(function() {
            $scope.show_mask = true;
            $scope.elements[code] = true;
        });

        //$scope.$apply();
    };

    $window.hideMask = function() {

        $timeout(function() {
            $scope.show_mask = false;
            for(var i in $scope.elements) {
                $scope.elements[i] = false;
            }
        });
        //$scope.$apply();

    }

}).controller('ApplicationTcController', function($scope, $routeParams, Tc) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.loadContent = function () {

        Tc.find($routeParams.tc_id).success(function (data) {

            $scope.html_file_path = data.html_file_path;
            $scope.page_title = data.page_title;

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});