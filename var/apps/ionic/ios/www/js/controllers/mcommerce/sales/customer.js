App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-customer', {
        url: BASE_PATH+"/mcommerce/mobile_sales_customer/index/value_id/:value_id",
        controller: 'MCommerceSalesCustomerViewController',
        templateUrl: "templates/mcommerce/l1/sales/customer.html"
    })

}).controller('MCommerceSalesCustomerViewController', function ($state, $stateParams, $scope, $translate, Dialog, McommerceCart, McommerceSalesCustomer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesCustomer.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("My information");

    $scope.loadContent = function () {
        McommerceCart.find().success(function (data) {
            $scope.cart = data.cart;
        }).finally(function () {
            $scope.is_loading = false;
        });
    };
    
    $scope.goToDeliveryPage = function () {
        $state.go("mcommerce-sales-delivery", {value_id: $stateParams.value_id});
    };

    $scope.updateCustomerInfos = function () {

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            var postParameters = {
                'product_id': $scope.product_id,
                'customer': $scope.cart.customer
            };

            McommerceSalesCustomer.updateCustomerInfos(postParameters).success(function (data) {
                $scope.goToDeliveryPage();
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert("", data.message, $translate.instant("OK"));
                }

                $scope.is_loading = false;
            });
        }
    };

    $scope.right_button = {
        action: $scope.updateCustomerInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});