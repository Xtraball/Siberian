App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-delivery', {
        url: BASE_PATH+"/mcommerce/mobile_sales_delivery/index/value_id/:value_id",
        controller: 'MCommerceSalesDeliveryViewController',
        templateUrl: "templates/mcommerce/l1/sales/delivery.html",
        cache:false
    });

}).controller('MCommerceSalesDeliveryViewController', function ($ionicLoading, $scope, $stateParams, $state, $translate, Dialog, McommerceCart, McommerceSalesDelivery, SafePopups) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.page_title = $translate.instant("Delivery");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesDelivery.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.clients_calculate_change_form_is_visible = false;

    $scope.loadContent = function () {
        $scope.is_loading = true;
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });
        McommerceCart.find().success(function (data) {
            $scope.cart = data.cart;
            $scope.cart.delivery_method_extra_client_amount = $scope.cart.paid_amount ? parseFloat($scope.cart.paid_amount) : $scope.cart.total;
            $scope.cart.delivery_method_extra_amount_due = ($scope.cart.delivery_amount_due) ? parseFloat($scope.cart.delivery_amount_due) : 0;
            $scope.calculateAmountDue();

            McommerceSalesDelivery.findStore().success(function (data) {

                $scope.clients_calculate_change_form_is_visible = data.clients_calculate_change;

                $scope.selectedStore = data.store;

            }).finally(function () {
                $scope.is_loading = false;
                $ionicLoading.hide();
            });

        }).error(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.selectDeliveryMethod = function(cart, delivery_method) {
        var tmp_total_with_fees = cart.base_total_without_fees + delivery_method.price;

        cart.total = parseFloat(tmp_total_with_fees != cart.total && !cart.deliveryCost ? tmp_total_with_fees : cart.total);

        cart.delivery_method_extra_client_amount = cart.total;
        cart.deliveryMethodId = delivery_method.id;
        $scope.clients_calculate_change_form_is_visible = delivery_method.code == "home_delivery" && $scope.selectedStore.clients_calculate_change;
        $scope.cart.delivery_method_extra_amount_due = 0;
    };

    $scope.calculateAmountDue = function() {
        var price = parseFloat($scope.cart.delivery_method_extra_client_amount);

        if(isNaN(price) || price < $scope.cart.total) {
            if(isNaN(price)) {
                $scope.cart.delivery_method_extra_client_amount = "";
            }
            $scope.cart.delivery_method_extra_amount_due = null;
            return;
        }

        $scope.cart.delivery_method_extra_amount_due = (price - $scope.cart.total).toFixed(2);
        $scope.cart.total = $scope.cart.total;

    };

    $scope.updateDeliveryInfos = function () {

        if($scope.cart.delivery_method_extra_amount_due == null) {
            SafePopups.show("alert",{
                title: $translate.instant(''),
                template: $translate.instant("Remaining due is incorrect."),
                buttons: [{
                    text: $translate.instant("OK")
                }]
            });
            return;
        }

        if(!$scope.cart.deliveryMethodId) {
            SafePopups.show("alert",{
                title: $translate.instant(''),
                template: $translate.instant("You have to choose a delivery method."),
                buttons: [{
                    text: $translate.instant("OK")
                }]
            });
            return;
        }

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            $ionicLoading.show({
                template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
            });

            var postParameters = {
                'delivery_method_id': $scope.cart.deliveryMethodId,
                'paid_amount': $scope.clients_calculate_change_form_is_visible ? $scope.cart.delivery_method_extra_client_amount:null,
                'store_id': $scope.selectedStore.id
            };

            McommerceSalesDelivery.updateDeliveryInfos(postParameters).success(function (data) {
                $scope.goToPaymentPage();
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    SafePopups.show("alert",{
                        title: $translate.instant(''),
                        template: $translate.instant(data.message),
                        buttons: [{
                            text: $translate.instant("OK")
                        }]
                    });
                }
            }).finally(function() {
                $scope.is_loading = false;
                $ionicLoading.hide();
            });
        }
    };

    $scope.goToPaymentPage = function () {
        $state.go("mcommerce-sales-payment", {value_id: $stateParams.value_id});
    };

    $scope.right_button = {
        action: $scope.updateDeliveryInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});