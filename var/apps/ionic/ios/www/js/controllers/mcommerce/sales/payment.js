App.config(function ($stateProvider) {
    
    $stateProvider.state('mcommerce-sales-payment', {
        url: BASE_PATH+"/mcommerce/mobile_sales_payment/index/value_id/:value_id",
        controller: 'MCommerceSalesPaymentViewController',
        templateUrl: "templates/mcommerce/l1/sales/payment.html",
        cache:false
    });

}).controller('MCommerceSalesPaymentViewController', function ($ionicLoading, $scope, $state, $stateParams, $translate, Dialog, McommerceCart, McommerceSalesPayment, SafePopups) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.page_title = $translate.instant("Payment");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.loadContent = function () {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });
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

                if($scope.paymentMethods.length == 1 && $scope.paymentMethods[0].code == "free") {
                    //Free purchase we can skip the payment method selection
                    $scope.cart.paymentMethodId = $scope.paymentMethods[0].id;
                    $scope.updatePaymentInfos();
                }

            }).finally(function () {
                $scope.is_loading = false;
                $ionicLoading.hide();
            });

        }).error(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.updatePaymentInfos = function () {

        if(!$scope.is_loading) {

            $scope.is_loading = true;
            $ionicLoading.show({
                template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
            });

            var postParameters = {
                'payment_method_id': $scope.cart.paymentMethodId
            };

            McommerceSalesPayment.updatePaymentInfos(postParameters).success(function (data) {
                $scope.goToConfirmationPage();
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

    $scope.goToConfirmationPage = function () {
        if($scope.cart.paymentMethodId) {
            $state.go("mcommerce-sales-confirmation", {value_id: $stateParams.value_id});
        } else {
            SafePopups.show("alert",{
                title: $translate.instant(''),
                template: $translate.instant("Please choose a payment method."),
                buttons: [{
                    text: $translate.instant("OK")
                }]
            })
        }
    };

    $scope.right_button = {
        action: $scope.updatePaymentInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});