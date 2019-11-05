/* global
    App, angular, BASE_PATH
 */

angular.module('starter')
    .controller('MCommerceSalesConfirmationViewController',
        function ($ionicPopup, Loader, $location, $rootScope, $scope, $state, $stateParams, $timeout, $translate,
                  $window, Analytics, Application, Customer, Dialog, McommerceCart, McommerceSalesPayment) {
    angular.extend($scope, {
        is_loading: true,
        page_title: $translate.instant('Review'),
        value_id: $stateParams.value_id
    });

    Loader.show();
    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;

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
                            .then(function (onlinePaymentData) {
                                $scope.onlinePaymentUrl = onlinePaymentData.url;
                                $scope.form_url = onlinePaymentData.form_url;

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
        // Save notes!
        McommerceSalesPayment.notes = $scope.notes || '';
        sessionStorage.setItem('mcommerce-notes', $scope.notes || '');

        if ($scope.onlinePaymentUrl) {
            if (!isNativeApp) {
                $window.location = $scope.onlinePaymentUrl;
            } else {
                var browser = $window.open($scope.onlinePaymentUrl, $rootScope.getTargetForLink(), 'location=yes');
                var nextState = 'mcommerce-sales-error';
                var stepNext = function () {
                    browser.close();
                    $state.go(nextState, {
                        value_id: $stateParams.value_id
                    });
                };

                browser.addEventListener('loadstart', function (event) {
                    switch (true) {
                        case /(mcommerce\/mobile_sales_success)/.test(event.url):
                            nextState = 'mcommerce-sales-success';
                            stepNext();
                            break;
                        case /(mcommerce\/mobile_sales_cancel)/.test(event.url):
                            nextState = 'mcommerce-sales-confirmation-cancel';
                            stepNext();
                            break;
                        case /(mcommerce\/mobile_sales_error)/.test(event.url):
                            nextState = 'mcommerce-sales-error';
                            stepNext();
                            break;
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
});

angular.module('starter')
    .controller('MCommerceSalesConfirmationCancelController', function ($state, $stateParams, $translate, Dialog) {
    // Display cancelation message!
    Dialog.alert('', 'The payment has been cancelled, something wrong happened? Feel free to contact us.', 'OK')
        .then(function () {
            $state.go('mcommerce-sales-confirmation', {
                value_id: $stateParams.value_id
            });
        });
});
