App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + '/mcommerce/mobile_sales_confirmation/index/value_id/:value_id', {
        controller: 'MCommerceSalesConfirmationViewController',
        templateUrl: BASE_URL + '/mcommerce/mobile_sales_confirmation/template',
        code: 'mcommerce-sales-confirmation'
    });

    $routeProvider.when(BASE_URL + '/mcommerce/mobile_sales_confirmation/cancel/value_id/:value_id', {
        controller: 'MCommerceSalesConfirmationCancelController',
        templateUrl: BASE_URL + '/mcommerce/mobile_sales_confirmation/template',
        code: 'mcommerce-sales-confirmation'
    });

    $routeProvider.when(BASE_URL + '/mcommerce/mobile_sales_confirmation/confirm/token/:token/PayerID/:payerId/value_id/:value_id', {
        controller: 'MCommerceSalesConfirmationConfirmPaymentController',
        templateUrl: BASE_URL + '/mcommerce/mobile_sales_confirmation/template',
        code: 'mcommerce-sales-confirmation'
    });

}).controller('MCommerceSalesConfirmationViewController', function ($rootScope, $scope, $timeout, $routeParams, $route, $location, $window, Pictos, McommerceCart, McommerceSalesPayment, Message, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.pictos = {
        shopping_cart: Pictos.get("shopping_cart", "background")
    };

    $scope.is_loading = true;

    McommerceCart.value_id = $routeParams.value_id;
    McommerceSalesPayment.value_id = $routeParams.value_id;
    $scope.value_id = $routeParams.value_id;

    $scope.loadContent = function () {
        McommerceCart.find().success(function (data) {
            $scope.cart = data.cart;

            McommerceSalesPayment.findOnlinePaymentUrl().success(function (data) {
                $scope.onlinePaymentUrl = data.url;
                $scope.form_url = data.form_url;

                $scope.header_right_button = {
                    action: $scope.validate,
                    title: 'Validate'
                };
            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.validate = function () {
        if ($scope.onlinePaymentUrl !== null) {
            $window.location.href = $scope.onlinePaymentUrl;
        } else if($scope.form_url) {
            $location.path(Url.get($scope.form_url));
        } else {

            if($scope.is_loading) return;

            $scope.is_loading = true;

            McommerceSalesPayment.validatePayment().success(function(data) {

                $rootScope.message = new Message();
                $rootScope.message.isError(false)
                    .setText(data.message)
                    .show()
                ;

                $location.path(Url.get('mcommerce/mobile_sales_success/index', {
                    value_id: $routeParams.value_id
                }));
            }).error(function(data) {

                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show()
                ;

            }).finally(function() {
                $scope.is_loading = false;
            });

        }
    };

    $scope.loadContent();

}).controller('MCommerceSalesConfirmationConfirmPaymentController', function ($scope, $timeout, $routeParams, $route, $location, $window, McommerceCart, McommerceSalesPayment, Message, Url) {

    $scope.is_loading = true;

    McommerceSalesPayment.value_id = $routeParams.value_id;

    McommerceSalesPayment.validateOnlinePayment($routeParams.token, $routeParams.payerId).success(function (data) {

        if (data.success) {
            $location.path(Url.get("mcommerce/mobile_sales_success/index", {
                value_id: $routeParams.value_id
            })).replace();
        }
    }).error(function (data) {
        if (data && angular.isDefined(data.message)) {
            $scope.confirmation_message = data.message;
        }
        // redirect after 5 seconds
        $timeout(function () {
            $location.path(Url.get("mcommerce/mobile_sales_confirmation/index", {
                value_id: $routeParams.value_id
            })).replace();
        }, 5000);
    }).finally(function () {
        $scope.is_loading = false;
    });



}).controller('MCommerceSalesConfirmationCancelController', function ($scope, $timeout, $routeParams, $route, $location, $window, McommerceCart, McommerceSalesPayment, Message, Url) {

    // display cancelation message
    $scope.message = new Message();
    $scope.message.isError(true)
        .setText('The payment has been cancelled, something wrong happened? Feel free to contact us.')
        .show();

    // redirect after 5 seconds
    $timeout(function () {
        $location.path(Url.get("mcommerce/mobile_sales_confirmation/index", {
            value_id: $routeParams.value_id,
        })).replace();
    }, 5000);

});