App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-customer', {
        url: BASE_PATH + "/mcommerce/mobile_sales_customer/index/value_id/:value_id",
        controller: 'MCommerceSalesCustomerViewController',
        templateUrl: "templates/mcommerce/l1/sales/customer.html"
    })

}).controller('MCommerceSalesCustomerViewController', function ($state, $stateParams, $scope, $translate, Dialog, McommerceCart, McommerceSalesCustomer, Customer, AUTH_EVENTS) {

    $scope.$on("connectionStateChange", function (event, args) {
        if (args.isOnline == true) {
            $scope.loadContent();
        }
    });

    Customer.onStatusChange("category", []);

    $scope.login = function () {
        Customer.display_account_form = false;
        Customer.loginModal($scope);
    };

    $scope.signup = function () {
        Customer.display_account_form = true;
        Customer.loginModal($scope);
    };

    $scope.$on(AUTH_EVENTS.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(AUTH_EVENTS.logoutSuccess, function () {
        $scope.is_logged_in = false;
    });

    $scope.is_loading = true;
    $scope.is_logged_in = Customer.isLoggedIn();

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesCustomer.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("My information");

    $scope.loadContent = function () {
        McommerceSalesCustomer.find().success(function (data) {
            $scope.customer = data.customer;
            if ($scope.customer && $scope.customer.hasOwnProperty("metadatas") && $scope.customer.metadatas.birthday) {
                $scope.customer.metadatas.birthday = new Date($scope.customer.metadatas.birthday);
            }
            $scope.settings = data.settings;
        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.goToDeliveryPage = function () {
        $state.go("mcommerce-sales-delivery", {value_id: $stateParams.value_id});
    };

    $scope.updateCustomerInfos = function () {
        $scope.is_loading = true;

        // Associate the customer to the cart and validate the extra fields
        McommerceSalesCustomer.updateCustomerInfos({'customer': $scope.customer}).success(function (data) {
            $scope.customer = data.customer;

            // Save Customer info
            Customer.save($scope.customer).success(function (data) {
                if (angular.isDefined(data.message)) {
                    Dialog.alert("", data.message, $translate.instant("OK"));
                }
                $scope.goToDeliveryPage();
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
                }
            }).finally(function () {
                $scope.is_loading = false;
            });

        }).error(function (data) {
            if (data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }
        }).finally(function () {
            $scope.is_loading = false;
        });

    };

    $scope.right_button = {
        action: $scope.updateCustomerInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});