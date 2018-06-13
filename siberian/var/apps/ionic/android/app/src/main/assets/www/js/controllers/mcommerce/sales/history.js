/*global
    App, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesHistoryViewController", function (Loader, $scope, $state, $stateParams,
                                                               $translate, McommerceSalesCustomer) {

    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("Order history");

    $scope.showLoader = function() {
        Loader.show();
    };

    $scope.orders = [];
    $scope.offset = 0;
    $scope.can_load_older_posts = true;

    McommerceSalesCustomer.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        $scope.showLoader();

        McommerceSalesCustomer.getOrderHistory($scope.offset)
            .then(function(data) {

                if(data.orders) {
                    $scope.orders = $scope.orders.concat(data.orders);
                    $scope.offset += data.orders.length;

                    if(data.orders.length <= 0) {
                        $scope.can_load_older_posts = false;
                    }
                    return true;
                } else {
                    $scope.orders = [];
                    return false;
                }

            }).then(function(refresh) {
                if(refresh) {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }
                Loader.hide();
            });
    };

    $scope.loadContent();

    $scope.showDetails = function(order_id) {
        $state.go("mcommerce-sales-history-details", {value_id: $scope.value_id, order_id: order_id});
    };

    $scope.loadMore = function() {
        $scope.loadContent();
    };

}).controller("MCommerceSalesHistoryDetailsController", function (Loader, $scope, $stateParams,
                                                                  $translate, McommerceSalesCustomer) {

    $scope.value_id = $stateParams.value_id;
    $scope.page_title = $translate.instant("Order details");
    $scope.order_id = $stateParams.order_id;

    Loader.show();

    McommerceSalesCustomer.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        McommerceSalesCustomer.getOrderDetails($scope.order_id)
            .then(function(data) {

                $scope.order = data.order;

            }).then(function() {
                Loader.hide();
            });
    };

    $scope.loadContent();

});