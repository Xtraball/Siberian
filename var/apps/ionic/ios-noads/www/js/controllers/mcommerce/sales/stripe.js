App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-stripe', {
        url: BASE_PATH+"/mcommerce/mobile_sales_stripe/index/value_id/:value_id",
        controller: 'MCommerceSalesStripeViewController',
        templateUrl: "templates/mcommerce/l1/sales/stripe.html",
        cache:false
    });

}).controller('MCommerceSalesStripeViewController', function ($ionicLoading, $ionicPopup, $location, $scope, $state, $stateParams, $timeout, $translate, Application, Customer, McommerceStripe, SafePopups) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $ionicLoading.show({
        template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
    });
    $scope.value_id = McommerceStripe.value_id = $stateParams.value_id;
    $scope.card = {};
    $scope.payment = {};
    $scope.payment.save_card = false;
    $scope.payment.use_stored_card = false;

    $scope.loadContent = function () {
        $scope.guest_mode = Customer.guest_mode;
        if(Customer.isLoggedIn()) {
            var cust_id = Customer.id;
        } else {
            var cust_id = null;
        }

        //reset save card param
        $scope.payment.save_card = false;

        McommerceStripe.find(cust_id).success(function (data) {
            Stripe.setPublishableKey(data.publishable_key);
            $scope.cart_total = data.total;
            if(data.card && data.card.exp_year){
                $scope.card = data.card;
                $scope.payment.use_stored_card = true;
            }
        }).finally(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });

    };

    if(typeof Stripe == "undefined") {
        var stripeJS = document.createElement('script');
        stripeJS.type = "text/javascript";
        stripeJS.src = "https://js.stripe.com/v2/";
        stripeJS.onload = function() {
            $scope.loadContent();
        };
        document.body.appendChild(stripeJS);
    } else {
        $scope.loadContent();
    }

    $scope.unloadcard = function () {
        SafePopups.show("confirm",{
            title: $translate.instant('Confirmation'),
            template: $translate.instant("Do you confirm you want to remove your card?")
        }).then(function(res){
            if(res) {
                $scope.is_loading = true;
                $ionicLoading.show({
                    template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
                });
                //we cannot be there without customer
                McommerceStripe.removeCard(Customer.id).success(function (data) {
                    $scope.oldcard = $scope.card;
                    $scope.card = {};
                    $scope.payment.use_stored_card = false;
                }).finally(function () {
                    $scope.is_loading = false;
                    $ionicLoading.hide();
                });
            }
        });
    };

    $scope.process = function () {
        if (!$scope.is_loading) {
            $scope.is_loading = true;
            $ionicLoading.show({
                template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
            });
            if ($scope.payment.use_stored_card) {
                _process();
            } else {
                Stripe.card.createToken($scope.card, function (status, response) {
                    _stripeResponseHandler(status, response);
                });
            }
        }
    };

    var _stripeResponseHandler = function(status, response) {
        $timeout(function() {
            if (response.error) {
                $ionicPopup.show({
                    subTitle: response.error.message,
                    buttons: [{
                        text: $translate.instant("OK")
                    }]
                });
                $scope.is_loading = false;
                $ionicLoading.hide();
            } else {
                $scope.card = {
                    token: response.id,
                    last4: response.card.last4,
                    brand: response.card.brand,
                    exp_month: response.card.exp_month,
                    exp_year: response.card.exp_year,
                    exp: Math.round(+(new Date((new Date(response.card.exp_year, response.card.exp_month, 1)) - 1)) / 1000) | 0
                };

                _process();
            }
        });
    };

    //function to make payment when all is ready
    var _process = function () {
        var data = {
            "token": $scope.card.token,
            "use_stored_card": $scope.payment.use_stored_card,
            "save_card": $scope.payment.save_card,
            "customer_id": Customer.id || null
        };

        McommerceStripe.process(data).success(function (res) {
            if (res) {
                $state.go("mcommerce-sales-success", {value_id: $stateParams.value_id});
            } else {
                SafePopups.show("alert", {
                    title: $translate.instant('Error'),
                    template: "Unexpected error",
                    buttons: [{
                        text: $translate.instant("OK")
                    }]
                });
            }
        }).error(function (err) {
            SafePopups.show("alert", {
                title: $translate.instant('Error'),
                template: "Unexpected error",
                buttons: [{
                    text: $translate.instant("OK")
                }]
            });
        }).finally(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.right_button = {
        action: $scope.process,
        label: $translate.instant("Pay")
    };

});