/*global
 App, angular, BASE_PATH
 */
angular.module('starter').controller('MCommerceSalesCustomerViewController', function (Loader, $state, $stateParams,
                                                                                       $scope, $translate, $rootScope,
                                                                                       McommerceCart,
                                                                                       McommerceSalesCustomer, Customer,
                                                                                       Dialog, SB) {
    Customer.onStatusChange('category', []);

    $scope.hasguestmode = false;

    $scope.customer_login = function () {
        Customer.display_account_form = false;
        Customer.loginModal($scope);
    };

    $scope.customer_signup = function () {
        Customer.display_account_form = true;
        Customer.loginModal($scope);
    };

    $scope.customer_guestmode = function () {
        Loader.show();
        var currentTs = Date.now();
        var guestmail = 'guest' + currentTs + (parseInt(Math.random() * 1000, 10)) + '@guest.com';
        Customer.register({
                civility: 'm',
                firstname: 'Guest',
                lastname: 'Guest',
                email: guestmail,
                password: parseInt(Math.random() * 10000000000, 10),
                privacy_policy: true
            }).then(function () {
                $scope.is_logged_in = true;
                Customer.guest_mode = true;
                $scope.loadContent();
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.is_logged_in = false;
    });

    $scope.is_loading = true;
    Loader.show();
    $scope.is_logged_in = Customer.isLoggedIn();

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesCustomer.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant('My information');

    $scope.loadContent = function () {
        McommerceSalesCustomer
            .hasGuestMode()
            .then(function (dataGuestMode) {
                // Check if had guest mode!
                if (dataGuestMode.success && dataGuestMode.activated) {
                    $scope.hasguestmode = true;
                }
                // Getting user!
                McommerceSalesCustomer
                    .find()
                    .then(function (data) {
                        $scope.customer = data.customer;
                        // Fix birthday!
                        if ($scope.customer && $scope.customer.hasOwnProperty('metadatas') && $scope.customer.metadatas.birthday) {
                            $scope.customer.metadatas.birthday = new Date($scope.customer.metadatas.birthday);
                        }
                        $scope.settings = data.settings;
                    }).then(function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK');
                }
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.goToDeliveryPage = function () {
        $state.go('mcommerce-sales-delivery', {
            value_id: $stateParams.value_id
        });
    };

    $scope.updateCustomerInfos = function () {
        $rootScope.loginFeature = true;
        $rootScope.loginFeatureBack = false;

        $scope.is_loading = true;
        Loader.show();

        // Associate the customer to the cart and validate the extra fields!
        McommerceSalesCustomer
            .updateCustomerInfos({
                customer: $scope.customer
            })
            .then(function (data) {
                $scope.customer = data.customer;

                // Save Customer info
                Customer
                    .save($scope.customer)
                    .then(function (data) {
                        if (angular.isDefined(data.message)) {
                        }
                        $scope.goToDeliveryPage();
                    }, function (data) {
                        if (data && angular.isDefined(data.message)) {
                            Dialog.alert('Error', data.message, 'OK');
                        }
                    }).then(function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });
            }, function (data) {
                $scope.is_loading = false;
                Loader.hide();
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK');
                }
            });
    };

    $scope.right_button = {
        action: $scope.updateCustomerInfos,
        label: $translate.instant('Next')
    };

    $scope.loadContent();
});
