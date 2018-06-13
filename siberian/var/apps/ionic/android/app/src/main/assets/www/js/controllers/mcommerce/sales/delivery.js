/*global
 App, angular, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesDeliveryViewController", function (Loader, $scope, $stateParams, $state,
                                                                $translate, McommerceCart, McommerceSalesDelivery,
                                                                Dialog) {

    $scope.page_title = $translate.instant("Delivery");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesDelivery.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.clients_calculate_change_form_is_visible = false;

    $scope.loadContent = function () {
        $scope.is_loading = true;
        Loader.show();
        McommerceCart.find()
            .then(function (data) {
                $scope.cart = data.cart;
                $scope.cart.delivery_method_extra_client_amount = $scope.cart.paid_amount ? parseFloat($scope.cart.paid_amount) : $scope.cart.total;
                $scope.cart.delivery_method_extra_amount_due = ($scope.cart.delivery_amount_due) ? parseFloat($scope.cart.delivery_amount_due) : 0;
                $scope.calculateAmountDue();

                McommerceSalesDelivery.findStore()
                    .then(function (data) {
                        $scope.clients_calculate_change_form_is_visible = data.clients_calculate_change;
                        $scope.selectedStore = data.store;

                    }).then(function () {
                        $scope.is_loading = false;
                    Loader.hide();
                    });

            }, function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.selectDeliveryMethod = function(cart, delivery_method) {
        var tmp_total_with_fees = cart.base_total_without_fees + delivery_method.price;

        cart.total = parseFloat(tmp_total_with_fees != cart.total && !cart.deliveryCost ? tmp_total_with_fees : cart.total);

        cart.delivery_method_extra_client_amount = cart.total;
        cart.deliveryMethodId = delivery_method.id;
        $scope.clients_calculate_change_form_is_visible = ((delivery_method.code == "home_delivery") && $scope.selectedStore.clients_calculate_change);
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
            Dialog.alert("", "Remaining due is incorrect.", "OK").then(function() {
                return;
            });
        }

        if(!$scope.cart.deliveryMethodId) {
            Dialog.alert("", "You have to choose a delivery method.", "OK").then(function() {
                return;
            });
        }

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            Loader.show();

            var postParameters = {
                'delivery_method_id': $scope.cart.deliveryMethodId,
                'paid_amount': $scope.clients_calculate_change_form_is_visible ? $scope.cart.delivery_method_extra_client_amount:null,
                'store_id': $scope.selectedStore.id
            };

            McommerceSalesDelivery.updateDeliveryInfos(postParameters)
                .then(function (data) {
                    $scope.goToPaymentPage();
                }, function (data) {
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert("", data.message, "OK");
                    }
                }).then(function() {
                    $scope.is_loading = false;
                    Loader.hide();
                });
        }
    };

    $scope.goToPaymentPage = function () {
        $state.go("mcommerce-sales-payment", {
            value_id: $stateParams.value_id
        });
    };

    $scope.right_button = {
        action: $scope.updateDeliveryInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});