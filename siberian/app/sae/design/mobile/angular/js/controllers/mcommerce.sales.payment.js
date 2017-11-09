App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_sales_payment/index/value_id/:value_id", {
        controller: 'MCommerceSalesPaymentViewController',
        templateUrl: BASE_URL + "/mcommerce/mobile_sales_payment/template",
        code: "mcommerce-sales-payment"
    });

}).controller('MCommerceSalesPaymentViewController', function ($scope, $routeParams, $location, McommerceCart, McommerceSalesPayment, Message, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    McommerceCart.value_id = $routeParams.value_id;
    McommerceSalesPayment.value_id = $routeParams.value_id;
    $scope.value_id = $routeParams.value_id;

    $scope.loadContent = function () {

        McommerceCart.find().success(function (data) {

            $scope.cart = data.cart;

            McommerceSalesPayment.findPaymentMethods().success(function (data) {
                $scope.paymentMethods = data.paymentMethods;

                $scope.paymentMethodId = data.paymentMethods.reduce(function (paymentMethodId, paymentMethod) {
                    if ($scope.cart.paymentMethodId === paymentMethod.id) {
                        paymentMethodId = paymentMethod.id;
                    }

                    return paymentMethodId;
                }, null);

            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.updatePaymentInfos = function () {

        $scope.paymentForm.submitted = true;

        if(!$scope.is_loading && $scope.paymentForm.$valid) {

            $scope.is_loading = true;
            var postParameters = {
                'payment_method_id': $scope.cart.paymentMethodId
            };

            McommerceSalesPayment.updatePaymentInfos(postParameters).success(function (data) {
                $scope.goToConfirmationPage();

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
    };

    $scope.goToConfirmationPage = function () {
        $location.path(Url.get("mcommerce/mobile_sales_confirmation/index", {
            value_id: $routeParams.value_id
        }));
    }

    $scope.header_right_button = {
        action: $scope.updatePaymentInfos,
        title: "Next"
    };

    $scope.loadContent();

});