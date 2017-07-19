/*global
 App, angular, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesPaymentViewController", function (Loader, $scope, $state, $stateParams,
                                                               $translate, McommerceCart, McommerceSalesPayment,
                                                               Dialog) {

    $scope.page_title = $translate.instant("Payment");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.loadContent = function () {
        Loader.show();
        McommerceCart.find()
            .then(function (data) {

            $scope.cart = data.cart;

            McommerceSalesPayment.findPaymentMethods()
                .then(function (data) {
                    $scope.paymentMethods = data.paymentMethods;

                    $scope.paymentMethodId = data.paymentMethods.reduce(function (paymentMethodId, paymentMethod) {
                        if ($scope.cart.paymentMethodId === paymentMethod.id) {
                            paymentMethodId = paymentMethod.id;
                        }

                        return paymentMethodId;
                    }, null);

                    if($scope.paymentMethods.length == 1 && $scope.paymentMethods[0].code == "free") {
                        //Free purchase we can skip the payment method selection
                        $scope.cart.paymentMethodId = $scope.paymentMethods[0].id;
                        $scope.updatePaymentInfos();
                    }

                }).then(function () {
                    $scope.is_loading = false;
                Loader.hide();
                });

        }, function () {
            $scope.is_loading = false;
                Loader.hide();
        });
    };

    $scope.updatePaymentInfos = function () {

        if(!$scope.is_loading) {

            $scope.is_loading = true;
            Loader.show();

            var postParameters = {
                'payment_method_id': $scope.cart.paymentMethodId
            };

            McommerceSalesPayment.updatePaymentInfos(postParameters)
                .then(function (data) {
                    $scope.goToConfirmationPage();

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

    $scope.goToConfirmationPage = function () {
        if($scope.cart.paymentMethodId) {
            $state.go("mcommerce-sales-confirmation", {
                value_id: $stateParams.value_id
            });
        } else {
            Dialog.alert("", "Please choose a payment method.", "OK");
        }
    };

    $scope.right_button = {
        action: $scope.updatePaymentInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});