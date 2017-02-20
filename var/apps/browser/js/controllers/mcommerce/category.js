App.config(function($stateProvider) {

    $stateProvider.state('mcommerce-category-list', {
        url: BASE_PATH+"/mcommerce/mobile_category/index/value_id/:value_id",
        controller: 'MCommerceListController',
        templateUrl: "templates/html/l3/list.html",
        cache:false
    }).state('mcommerce-subcategory-list', {
        url: BASE_PATH+"/mcommerce/mobile_category/index/value_id/:value_id/category_id/:category_id",
        controller: 'MCommerceListController',
        templateUrl: "templates/html/l3/list.html",
        cache:false
    }).state('mcommerce-redirect', {
        url: BASE_PATH+"/mcommerce/redirect/index/value_id/:value_id",
        controller: 'MCommerceRedirectController',
        cache:false
    });

}).controller('MCommerceListController', function($ionicLoading, $location, $scope, $state, $stateParams, McommerceCategory, Customer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $ionicLoading.show({
        template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
    });

    $scope.factory = McommerceCategory;
    $scope.collection = new Array();
    $scope.collection_is_empty = true;

    McommerceCategory.value_id = $stateParams.value_id;
    McommerceCategory.category_id = $stateParams.category_id;
    $scope.value_id = $stateParams.value_id;

    $scope.use_button_header = false;
    if(Customer.isLoggedIn() && !$stateParams.category_id) {
        $scope.use_button_header = true;
    }

    $scope.loadContent = function() {

        McommerceCategory.findAll().success(function(data) {

            $scope.show_search = data.show_search;
            $scope.collection = data.collection;
            $scope.collection_is_empty = $scope.collection.length > 0;

            $scope.cover = data.cover;
            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.openCart = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-cart-view", {value_id: $scope.value_id});
        }
    };

    $scope.openHistory = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-sales-history", {value_id: $scope.value_id});
        }
    };

    if(!$scope.use_button_header) {
        $scope.right_button = {
            action: $scope.openCart,
            icon: "ion-ios-cart"
        };
    }

    $scope.showItem = function(item) {
        $location.path(item.url);
    };

    $scope.loadContent();

}).controller('MCommerceRedirectController', function($ionicHistory, $scope, $state, $stateParams, HomepageLayout) {
    $scope.value_id = $stateParams.value_id;
    $scope.layout = HomepageLayout;

    $state.go('home').then(function() {
        if($scope.layout.properties.options.autoSelectFirst) {
            $ionicHistory.nextViewOptions({
                historyRoot: true,
                disableAnimate: false
            });
        }
        $state.go('mcommerce-category-list', {value_id: $scope.value_id});
    });

});