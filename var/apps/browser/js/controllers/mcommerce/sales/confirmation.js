App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-confirmation', {
        url: BASE_PATH+"/mcommerce/mobile_sales_confirmation/index/value_id/:value_id",
        controller: 'MCommerceSalesConfirmationViewController',
        templateUrl: "templates/mcommerce/l1/sales/confirmation.html"
    });

    $stateProvider.state('mcommerce-sales-confirmation-cancel', {
        url: BASE_PATH+"/mcommerce/mobile_sales_confirmation/cancel/value_id/:value_id",
        controller: 'MCommerceSalesConfirmationCancelController',
        templateUrl: "templates/mcommerce/l1/sales/confirmation.html"
    });

    $stateProvider.state('mcommerce-sales-confirmation-payment', {
        url: BASE_PATH+"/mcommerce/mobile_sales_confirmation/confirm/token/:token/PayerID/:payerId/value_id/:value_id",
        controller: 'MCommerceSalesConfirmationConfirmPaymentController',
        templateUrl: "templates/mcommerce/l1/sales/confirmation.html"
    });


}).controller('MCommerceSalesConfirmationViewController', function ($ionicPopup, $location, $rootScope, $scope, $state, $stateParams, $timeout, $translate, $window, Analytics, Application, Dialog, McommerceCart, McommerceSalesPayment) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.page_title = $translate.instant("Review");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.loadContent = function () {
        McommerceCart.find().success(function (data) {
            $scope.cart = data.cart;

            McommerceSalesPayment.findOnlinePaymentUrl().success(function (data) {
                $scope.onlinePaymentUrl = data.url;
                $scope.form_url = data.form_url;

                $scope.right_button = {
                    action: $scope.validate,
                    label: $translate.instant("Validate")
                };
            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function () {
            $scope.is_loading = false;
        });
    };

    $scope.validate = function () {

        if ($scope.onlinePaymentUrl) {

            if(Application.is_webview) {
                $window.location = $scope.onlinePaymentUrl;
            } else {

                var browser = window.open($scope.onlinePaymentUrl, $rootScope.getTargetForLink(), 'location=yes');

                browser.addEventListener('loadstart', function(event) {

                    if(/(mcommerce\/mobile_sales_confirmation\/confirm)/.test(event.url)) {

                        var url = new URL(event.url);
                        var params = url.search.replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
                        if(params.token && params.PayerID) {
                            browser.close();
                            $state.go("mcommerce-sales-confirmation-payment", { token: params.token, PayerID: params.PayerID, value_id: $stateParams.value_id });
                        }

                    } else if(/(mcommerce\/mobile_sales_confirmation\/cancel)/.test(event.url)) {

                        browser.close();

                        Dialog.alert("", $translate.instant("The payment has been cancelled, something wrong happened? Feel free to contact us."), $translate.instant("OK")).then(function() {
                            $state.go("mcommerce-sales-confirmation", {value_id: $stateParams.value_id});
                        });

                    }
                });

            }
        } else if($scope.form_url) {
            $location.path($scope.form_url);
        } else {

            if($scope.is_loading) return;

            $scope.is_loading = true;

            McommerceSalesPayment.validatePayment().success(function(data) {
                var products = [];
                angular.forEach($scope.cart.lines, function(value, key) {
                    var product = value.product;
                    product.category_id = value.category_id;
                    product.quantity = value.qty;

                    products.push(product);
                });
                Analytics.storeProductSold(products);

                $state.go("mcommerce-sales-success", {value_id: $stateParams.value_id});
            }).error(function(data) {
                Dialog.alert("", data.message, $translate.instant("OK"));
            }).finally(function() {
                $scope.is_loading = false;
            });

        }
    };

    $scope.loadContent();

}).controller('MCommerceSalesConfirmationConfirmPaymentController', function ($scope, $state, $stateParams, $timeout, McommerceCart, McommerceSalesPayment) {

    $scope.is_loading = true;

    McommerceSalesPayment.value_id = $stateParams.value_id;

    McommerceSalesPayment.validateOnlinePayment($stateParams.token, $stateParams.payerId).success(function (data) {
        if (data.success) {
            $state.go("mcommerce-sales-success", {value_id: $stateParams.value_id});
        }
    }).error(function (data) {
        if (data && angular.isDefined(data.message)) {
            $scope.confirmation_message = data.message;
        }
        // redirect after 5 seconds
        $timeout(function () {
            $state.go("mcommerce-sales-confirmation", {value_id: $stateParams.value_id});
        }, 5000);
    }).finally(function () {
        $scope.is_loading = false;
    });

}).controller('MCommerceSalesConfirmationCancelController', function ($state, $stateParams, $translate, Dialog) {

    // display cancelation message
    Dialog.alert("", $translate.instant("The payment has been cancelled, something wrong happened? Feel free to contact us."), $translate.instant("OK")).then(function() {
        $state.go("mcommerce-sales-confirmation", {value_id: $stateParams.value_id});
    });

});
