App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_sales_delivery/index/value_id/:value_id", {
        controller: 'MCommerceSalesDeliveryViewController',
        templateUrl: BASE_URL + "/mcommerce/mobile_sales_delivery/template",
        code: "mcommerce-sales-delivery"
    });

}).controller('MCommerceSalesDeliveryViewController', function ($scope, $routeParams, $location, McommerceCart, McommerceSalesDelivery, Message, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    McommerceCart.value_id = $routeParams.value_id;
    McommerceSalesDelivery.value_id = $routeParams.value_id;
    $scope.value_id = $routeParams.value_id;

    $scope.clients_calculate_change_form_is_visible = false;

    $scope.loadContent = function () {

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
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.setForm = function (form) {
        $scope.form = form;
    };

    $scope.selectedStoreUpdated = function() {

        if ($scope.form.selectedStore) {
            $scope.cart.deliveryMethodId = null;
        }

        $scope.clients_calculate_change_form_is_visible = false;
    };

    $scope.selectDeliveryMethod = function(cart, delivery_method) {
        var tmp_total_with_fees = cart.base_total_without_fees + delivery_method.price;

        cart.total = parseFloat(tmp_total_with_fees != cart.total ? tmp_total_with_fees : cart.total);

        cart.delivery_method_extra_client_amount = cart.total;
        cart.deliveryMethodId = delivery_method.id;
        $scope.clients_calculate_change_form_is_visible = delivery_method.code == "home_delivery" && $scope.form.selectedStore.$modelValue.clients_calculate_change;
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

        var form = $scope.form;

        form.submitted = true;

        if (form.$valid) {
            if($scope.cart.delivery_method_extra_amount_due == null) return;

            if(!$scope.is_loading) {
                $scope.is_loading = true;

                var postParameters = {
                    'delivery_method_id': $scope.cart.deliveryMethodId,
                    'paid_amount': $scope.clients_calculate_change_form_is_visible ? $scope.cart.delivery_method_extra_client_amount:null,
                    'store_id': $scope.selectedStore.id
                };

                McommerceSalesDelivery.updateDeliveryInfos(postParameters).success(function (data) {
                    $scope.goToPaymentPage();
                }).error(function (data) {
                    if (data && angular.isDefined(data.message)) {
                        $scope.message = new Message();
                        $scope.message.isError(true)
                            .setText(data.message)
                            .show();

                        $scope.is_loading = false;
                    }
                });
            }
        }
    };

    $scope.goToPaymentPage = function () {
        $location.path(Url.get("mcommerce/mobile_sales_payment/index", {
            value_id: $routeParams.value_id
        }));
    }

    $scope.header_right_button = {
        action: $scope.updateDeliveryInfos,
        title: "Next"
    };

    $scope.loadContent();

});