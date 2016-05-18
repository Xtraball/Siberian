App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/catalog/mobile_category_list/index/value_id/:value_id", {
        controller: 'CategoryListController',
        templateUrl: function(params) {
            return BASE_URL+"/catalog/mobile_category_list/template/value_id/"+params.value_id;
        },
        code: "catalog"
    }).when(BASE_URL+"/catalog/mobile_category_product_view/index/value_id/:value_id/product_id/:product_id", {
        controller: 'CategoryProductViewController',
        templateUrl: function(params) {
            return BASE_URL+"/catalog/mobile_category_product_view/template/value_id/"+params.value_id;
        },
        code: "catalog-product-view"
    });

}).controller('CategoryListController', function($window, $scope, $routeParams, $location, Url, Sidebar, Catalog, $timeout) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.sidebar = new Sidebar("catalog");
    $scope.value_id = Catalog.value_id = $routeParams.value_id;
    $scope.template_view = Url.get("catalog/mobile_category_product_list/template", {value_id: $routeParams.value_id});
    $scope.collection = new Array();

    $scope.loadContent = function() {

        $scope.sidebar.is_loading = true;

        Catalog.findAll().success(function(data) {

            $scope.sidebar.reset();

            $scope.sidebar.collection = data.categories;
            $scope.page_title = data.page_title;
            $scope.sidebar.showFirstItem(data.categories);
        }).finally(function() {
            $scope.is_loading = false;
            $scope.sidebar.is_loading = false;
        });
    };

    $scope.showItem = function(item) {
        $location.path(Url.get("catalog/mobile_category_product_view/index", {value_id: $scope.value_id, product_id: item.id}));
    };

    $scope.sidebar.loadItem = function(item) {
        $scope.collection = item.collection;
        $scope.sidebar.current_item = item;
        $scope.sidebar.show = false;
    };

    $scope.loadContent();

}).controller('CategoryProductViewController', function($window, $scope, $routeParams, Catalog, Pictos, Application) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = Catalog.value_id = $routeParams.value_id;
    Catalog.product_id = $routeParams.product_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Catalog.find($routeParams.product_id).success(function(product) {
            $scope.product = product;

            if($scope.product.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.product.name,
                            "picture": $scope.product.picture ? $scope.product.picture : null,
                            "content_url": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});