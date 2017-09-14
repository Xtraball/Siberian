/* global
    App, angular, BASE_PATH
 */

angular.module('starter').controller('MCommerceSalesConfirmationViewController', function ($ionicPopup, Loader, $location, $rootScope,
                                                                    $scope, $state, $stateParams, $timeout, $translate,
                                                                    $window, Analytics, Application, Customer, Dialog,
                                                                    McommerceCart, McommerceSalesPayment) {
    $scope.is_loading = true;
    Loader.show();

    $scope.page_title = $translate.instant('Review');

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.loadContent = function () {
        $scope.guest_mode = Customer.guest_mode;
        McommerceCart.compute()
            .then(function (computation) {
                McommerceCart.find()
                    .then(function (data) {
                        $scope.cart = data.cart;
                        $scope.cart.discount_message = computation.message;
                        $scope.cart.discount = computation.discount;

                        McommerceSalesPayment.findOnlinePaymentUrl()
                            .then(function (data) {
                                $scope.onlinePaymentUrl = data.url;
                                $scope.form_url = data.form_url;

                                $scope.right_button = {
                                    action: $scope.validate,
                                    label: $translate.instant('Validate')
                                };
                            }).then(function () {
                                Loader.hide();
                                if (computation &&
                                    angular.isDefined(computation.message) &&
                                    computation.show_message) {
                                    Dialog.alert('', computation.message, 'OK');
                                }
                                $scope.is_loading = false;
                            });
                    }, function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });
            }, function () {
                Loader.hide();
                $scope.is_loading = false;
            });
    };

    $scope.validate = function () {
        // TG-459
        McommerceSalesPayment.notes = $scope.notes || '';
        sessionStorage.setItem('mcommerce-notes', $scope.notes || '');

        if ($scope.onlinePaymentUrl) {
            if (Application.is_webview) {
                $window.location = $scope.onlinePaymentUrl;
            } else {
                /** @todo Use LinkService but force inAppBrowser with listeners */
                var browser = $window.open($scope.onlinePaymentUrl, $rootScope.getTargetForLink(), 'location=yes');

                browser.addEventListener('loadstart', function (event) {
                    if (/(mcommerce\/mobile_sales_confirmation\/confirm)/.test(event.url)) {
                        var url = new URL(event.url);
                        var params = url.search.replace(/(^\?)/, '').split('&').map(function (n) {
                            return n = n.split('='), this[n[0]] = n[1], this;
                        }.bind({}))[0];
                        if (params.token && params.payer_id) {
                            browser.close();
                            $state.go('mcommerce-sales-confirmation-payment', {
                                token: params.token,
                                payer_id: params.payer_id,
                                value_id: $stateParams.value_id
                            });
                        }
                    } else if (/(mcommerce\/mobile_sales_confirmation\/cancel)/.test(event.url)) {
                        browser.close();

                        Dialog.alert('', 'The payment has been cancelled, something wrong happened? Feel free to contact us.', 'OK')
                            .then(function () {
                                $state.go('mcommerce-sales-confirmation', {
                                    value_id: $stateParams.value_id
                                });
                            });
                    }
                });
            }
        } else if ($scope.form_url) {
            $location.path($scope.form_url);
        } else {
            if ($scope.is_loading) {
                return;
            }

            $scope.is_loading = true;
            Loader.show();

            McommerceSalesPayment.validatePayment()
                .then(function (data) {
                    var products = [];
                    angular.forEach($scope.cart.lines, function (value, key) {
                        var product = value.product;
                        product.category_id = value.category_id;
                        product.quantity = value.qty;

                        products.push(product);
                    });
                    Analytics.storeProductSold(products);
                    // end of non online payment!
                    $state.go('mcommerce-sales-success', {
                        value_id: $stateParams.value_id
                    });
                }, function (data) {
                    Dialog.alert('', data.message, 'OK');
                }).then(function () {
                    $scope.is_loading = false;
                    Loader.hide();
                });
        }
    };

    $scope.loadContent();
}).controller('MCommerceSalesConfirmationConfirmPaymentController', function ($ionicLoading, $scope, $state,
                                                                              $stateParams, $timeout, McommerceCart,
                                                                              McommerceSalesPayment) {
    $scope.is_loading = true;
    $ionicLoading.show({
        template: '<ion-spinner class="spinner-custom"></ion-spinner>'
    });

    McommerceSalesPayment.value_id = $stateParams.value_id;

    McommerceSalesPayment.validateOnlinePayment($stateParams.token, $stateParams.payer_id)
        .then(function (data) {
            if (data.success) {
                // end of non online payment!
                $state.go('mcommerce-sales-success', {
                    value_id: $stateParams.value_id
                });
            }
        }, function (data) {
            if (data && angular.isDefined(data.message)) {
                $scope.confirmation_message = data.message;
            }
            // redirect after 5 seconds!
            $timeout(function () {
                $state.go('mcommerce-sales-confirmation', {
                    value_id: $stateParams.value_id
                });
            }, 5000);
        }).then(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
}).controller('MCommerceSalesConfirmationCancelController', function ($state, $stateParams, $translate, Dialog) {
    // display cancelation message!
    Dialog.alert('', 'The payment has been cancelled, something wrong happened? Feel free to contact us.', 'OK')
        .then(function () {
            $state.go('mcommerce-sales-confirmation', {
                value_id: $stateParams.value_id
            });
        });
});
