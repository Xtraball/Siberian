App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/mcommerce/mobile_category/index/value_id/:value_id", {
        controller: 'MCommerceListController',
        templateUrl: BASE_URL+"/mcommerce/mobile_category/template",
        code: "mcommerce-category"
    }).when(BASE_URL+"/mcommerce/mobile_category/index/value_id/:value_id/category_id/:category_id", {
        controller: 'MCommerceListController',
        templateUrl: BASE_URL+"/mcommerce/mobile_category/template",
        code: "mcommerce-category"
    });

}).controller('MCommerceListController', function($scope, $routeParams, $location, McommerceCategory, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });
    
    $scope.is_loading = true;

    $scope.factory = McommerceCategory;
    $scope.collection = new Array();
    $scope.collection_is_empty = true;

    McommerceCategory.value_id = $routeParams.value_id;
    McommerceCategory.category_id = $routeParams.category_id;
    $scope.value_id = $routeParams.value_id;

    $scope.loadContent = function() {

        McommerceCategory.findAll().success(function(data) {

            $scope.show_search = data.show_search;
            $scope.collection = data.collection;
            $scope.collection_is_empty = $scope.collection.length > 0;

            $scope.cover = data.cover;
            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.openCart = function () {

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            $location.path(Url.get("mcommerce/mobile_cart/index", {
                value_id: $routeParams.value_id
            }));
        }
    };

    $scope.header_right_button = {
        action: $scope.openCart,
        title: "Cart"
    };
    
    $scope.showItem = function(item) {
        $location.path(item.url);
    };
    
    $scope.loadContent();

});