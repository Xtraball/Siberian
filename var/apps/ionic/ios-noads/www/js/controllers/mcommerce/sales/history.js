App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-history', {
        url: BASE_PATH+"/mcommerce/mobile_sales_customer/history/value_id/:value_id",
        controller: 'MCommerceSalesHistoryViewController',
        templateUrl: "templates/mcommerce/l1/sales/history.html",
        cache:false
    }).state('mcommerce-sales-history-details', {
        url: BASE_PATH+"/mcommerce/mobile_sales_customer/history_details/value_id/:value_id/order_id/:order_id",
        controller: 'MCommerceSalesHistoryDetailsController',
        templateUrl: "templates/mcommerce/l1/sales/history_details.html",
        cache:false
    });

}).controller('MCommerceSalesHistoryViewController', function ($ionicLoading, $scope, $state, $stateParams, $timeout, $translate, McommerceSalesCustomer) {

    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("Order history");

    $scope.showLoader = function() {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });
    };

    $scope.orders = new Array();
    $scope.offset = 0;
    $scope.can_load_older_posts = true;

    McommerceSalesCustomer.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        $scope.showLoader();

        McommerceSalesCustomer.getOrderHistory($scope.offset).success(function(data) {

            $scope.orders = $scope.orders.concat(data.orders);
            $scope.offset += data.orders.length;

            if(data.orders.length <= 0) {
                $scope.can_load_older_posts = false;
            }
            console.log("can load:", $scope.can_load_older_posts);
        }).finally(function() {
            $scope.$broadcast('scroll.infiniteScrollComplete');
            $ionicLoading.hide();
        });
    };

    $scope.loadContent();

    $scope.showDetails = function(order_id) {
        $state.go("mcommerce-sales-history-details", {value_id: $scope.value_id, order_id: order_id});
    };

    $scope.loadMore = function() {
        console.log("load more");
        $scope.loadContent();
    };

}).controller('MCommerceSalesHistoryDetailsController', function ($ionicLoading, $scope, $state, $stateParams, $timeout, $translate, McommerceSalesCustomer) {

    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("Order details");

    $scope.order_id = $stateParams.order_id;

    $ionicLoading.show({
        template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
    });

    McommerceSalesCustomer.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        McommerceSalesCustomer.getOrderDetails($scope.order_id).success(function(data) {

            $scope.order = data.order;

        }).finally(function() {
            $ionicLoading.hide();
        });
    };

    $scope.loadContent();

});