App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-stripe', {
        url: BASE_PATH+"/mcommerce/mobile_sales_stripe/index/value_id/:value_id",
        controller: 'MCommerceSalesStripeViewController',
        templateUrl: "templates/mcommerce/l1/sales/stripe.html"
    });

}).controller('MCommerceSalesStripeViewController', function ($ionicPopup, $location, $scope, $state, $stateParams, $timeout, $translate, McommerceStripe) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = McommerceStripe.value_id = $stateParams.value_id;
    $scope.card = {};
    $scope.stripe_token = null;

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

    $scope.loadContent = function () {

        McommerceStripe.find().success(function (data) {
            Stripe.setPublishableKey(data.publishable_key);
            $scope.cart_total = data.total;
        }).finally(function () {
            $scope.is_loading = false;
        });

    };

    $scope.process = function () {
        if(!$scope.is_loading) {
            $scope.is_loading = true;
            Stripe.card.createToken($scope.card, function (status, response) {
                $scope.stripeResponseHandler(status, response);
            });
        }
    };

    $scope.stripeResponseHandler = function(status, response) {

        $timeout(function() {
            if (response.error) {
                $ionicPopup.show({
                    subTitle: response.error.message,
                    buttons: [{
                        text: $translate.instant("OK")
                    }]
                });
                $scope.is_loading = false;
            } else {
                McommerceStripe.process(response.id).success(function () {
                    $state.go("mcommerce-sales-success", {value_id: $stateParams.value_id});
                }).error(function() {
                    $state.go("mcommerce-sales-error", {value_id: $stateParams.value_id});
                }).finally(function () {
                    $scope.card = {};
                    $scope.is_loading = false;
                });
            }
        });
    };

    $scope.right_button = {
        action: $scope.process,
        label: $translate.instant("Pay")
    };

});