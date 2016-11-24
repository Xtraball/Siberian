App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('catalog-category-list', {
        url: BASE_PATH+"/catalog/mobile_category_list/index/value_id/:value_id",
        controller: 'CategoryListController',
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l5"; break;
                case "3": layout_id = "l6"; break;
                case "1":
                default: layout_id = "l3";
            }
            return 'templates/html/'+layout_id+'/list.html';
        }
    }).state('catalog-product-view', {
        url: BASE_PATH+"/catalog/mobile_category_product_view/index/value_id/:value_id/product_id/:product_id",
        controller: 'CategoryProductViewController',
        templateUrl: "templates/catalog/category/l1/product/view.html"
    });

}).controller('CategoryListController', function($filter, $window, $scope, $state, $stateParams, $timeout, Url, Catalog) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.value_id = Catalog.value_id = $stateParams.value_id;
    $scope.products = new Array();

    $scope.loadContent = function() {
        $scope.is_loading = true;
        Catalog.findAll().success(function(data) {
            $scope.categories = data.categories;
            $scope.page_title = data.page_title;

            $scope.tooltip = {
                collection: $scope.categories,
                current_item: $scope.categories[0],
                button_label: $scope.categories[0] ? $scope.categories[0].name : null,
                onItemClicked: function(item) {
                    $scope.showTooltip(item);
                }
            };

            if($scope.categories.length) {
                var category = $scope.categories[0];
                if(category.children) category = category.children[0];
                $scope.current_category = category;
                $scope.showTooltip(category);
            }

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.showTooltip = function(category) {

        if(category.children) {
            category.show_children = !category.show_children;
        } else {
            $scope.collection = category.collection;
            $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
            $scope.current_category = category;
            $scope.tooltip.current_item = $scope.current_category;
            $scope.tooltip.button_label = $scope.current_category.name;
        }

    };

    $scope.showItem = function(item) {
        $state.go("catalog-product-view", { value_id: $scope.value_id, product_id: item.id });
    };

    $timeout(function() {
        $scope.loadContent();
    });

}).controller('CategoryProductViewController', function($window, $scope, $stateParams, Catalog/*, Pictos, Application*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = Catalog.value_id = $stateParams.value_id;
    Catalog.product_id = $stateParams.product_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Catalog.find($stateParams.product_id).success(function(product) {
            $scope.product = product;
            $scope.page_title = product.name;
            

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.loadContent();

});