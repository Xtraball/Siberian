App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-customer', {
        url: BASE_PATH + "/mcommerce/mobile_sales_customer/index/value_id/:value_id",
        controller: 'MCommerceSalesCustomerViewController',
        templateUrl: "templates/mcommerce/l1/sales/customer.html",
        cache:false
    })

}).controller('MCommerceSalesCustomerViewController', function ($ionicLoading, $state, $stateParams, $scope, $translate, Dialog, McommerceCart, McommerceSalesCustomer, Customer, SafePopups, AUTH_EVENTS) {

    $scope.$on("connectionStateChange", function (event, args) {
        if (args.isOnline == true) {
            $scope.loadContent();
        }
    });

    Customer.onStatusChange("category", []);

    $scope.hasguestmode = false;

    $scope.login = function () {
        Customer.display_account_form = false;
        Customer.loginModal($scope);
    };

    $scope.signup = function () {
        Customer.display_account_form = true;
        Customer.loginModal($scope);
    };

    $scope.guestmode = function () {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });
        var currentTs = Date.now();
        var guestmail = "guest" + currentTs + (parseInt(Math.random() * 1000)) + "@guest.com";
        Customer.register({
            "civility": "m",
            "firstname": "Guest",
            "lastname": "Guest",
            "email": guestmail,
            "password": parseInt(Math.random() * 10000000000),
            "privacy_policy": true
        }).success(function(){
            $scope.is_logged_in = true;
            Customer.guest_mode = true;
            $scope.loadContent();
        }).finally(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.$on(AUTH_EVENTS.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(AUTH_EVENTS.logoutSuccess, function () {
        $scope.is_logged_in = false;
    });

    $scope.is_loading = true;
    $ionicLoading.show({
        template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
    });
    $scope.is_logged_in = Customer.isLoggedIn();

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesCustomer.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("My information");

    $scope.loadContent = function () {
        McommerceSalesCustomer.hasGuestMode().success(function (dataGuestMode) {
            //check if had guest mode
            if(dataGuestMode.success && dataGuestMode.activated) {
                $scope.hasguestmode = true;
            }
            //getting user
            McommerceSalesCustomer.find().success(function (data) {
                $scope.customer = data.customer;
                //fix birthday ?
                if ($scope.customer && $scope.customer.hasOwnProperty("metadatas") && $scope.customer.metadatas.birthday) {
                    $scope.customer.metadatas.birthday = new Date($scope.customer.metadatas.birthday);
                }
                $scope.settings = data.settings;
            }).finally(function () {
                $scope.is_loading = false;
                $ionicLoading.hide();
            });
        }).error(function (data) {
            if (data && angular.isDefined(data.message)) {
                SafePopups.show("alert", {
                    title: $translate.instant('Error'),
                    template: data.message,
                    buttons: [{
                        text: $translate.instant("OK")
                    }]
                });
            }
        }).finally(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.goToDeliveryPage = function () {
        $state.go("mcommerce-sales-delivery", {value_id: $stateParams.value_id});
    };

    $scope.updateCustomerInfos = function () {
        $scope.is_loading = true;
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });

        // Associate the customer to the cart and validate the extra fields
        McommerceSalesCustomer.updateCustomerInfos({'customer': $scope.customer}).success(function (data) {
            $scope.customer = data.customer;

            // Save Customer info
            Customer.save($scope.customer).success(function (data) {
                if (angular.isDefined(data.message)) {
                }
                $scope.goToDeliveryPage();
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    SafePopups.show("alert", {
                        title: $translate.instant('Error'),
                        template: data.message,
                        buttons: [{
                            text: $translate.instant("OK")
                        }]
                    });
                }
            }).finally(function () {
                $scope.is_loading = false;
                $ionicLoading.hide();
            });

        }).error(function (data) {
            $scope.is_loading = false;
            $ionicLoading.hide();
            if (data && angular.isDefined(data.message)) {
                SafePopups.show("alert", {
                    title: $translate.instant('Error'),
                    template: data.message,
                    buttons: [{
                        text: $translate.instant("OK")
                    }]
                });
            }
        });

    };

    $scope.right_button = {
        action: $scope.updateCustomerInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});