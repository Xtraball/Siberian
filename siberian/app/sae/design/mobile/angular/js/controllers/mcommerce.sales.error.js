App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_sales_error/index/value_id/:value_id", {
        controller: 'MCommerceSalesErrorViewController',
        templateUrl: BASE_URL + "/mcommerce/mobile_sales_error/template",
        code: "mcommerce-sales-error"
    });

}).controller('MCommerceSalesErrorViewController', function ($scope, $location, $routeParams, $timeout, Url, LayoutService) {

    $timeout(function() {
        var path_url = Url.get("mcommerce/mobile_category/index/value_id/"+$routeParams.value_id);
        if(LayoutService.properties.menu.visibility == "homepage" ) {
            path_url = BASE_URL;
        }
        $location.path(path_url);
    }, 4000);

    $scope.value_id = $routeParams.value_id;

});