App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_cart/index/value_id/:value_id", {
        controller: 'MCommerceCartViewController',
        templateUrl: BASE_URL + "/mcommerce/mobile_cart/template",
        code: "mcommerce-cart"
    });

}).controller('MCommerceCartViewController', function ($scope, $routeParams, $location, Pictos, McommerceCart, Message, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    McommerceCart.value_id = $routeParams.value_id;
    $scope.value_id = $routeParams.value_id;

    $scope.pictos = {
        trash: Pictos.get("trash", "background"),
        shopping_cart: Pictos.get("shopping_cart", "background")
    };
    
    $scope.loadContent = function () {

        McommerceCart.find().success(function (data) {
            $scope.cart = data.cart;

            var action = data.nb_stores > 1?$scope.goToStoreChoice:$scope.goToOverview;

            if($scope.cart.lines.length > 0) {
                $scope.header_right_button = {
                    action: action,
                    title: "Proceed"
                };
            }

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.goToStoreChoice = function() {
        $scope.is_loading = true;
        $location.path(Url.get("mcommerce/mobile_sales_storechoice/index", {
            value_id: $routeParams.value_id
        }));
    };

    $scope.goToOverview = function () {

        if(!$scope.cart.valid) {
            $scope.message = new Message();
            $scope.message.isError(true)
                .setText($scope.cart.valid_message)
                .show();
        } else if(!$scope.is_loading) {
            $scope.is_loading = true;
            $location.path(Url.get("mcommerce/mobile_sales_customer/index", {
                value_id: $routeParams.value_id
            }));
        }
    };

    $scope.goToCategories = function () {
        $location.path(Url.get("mcommerce/mobile_category/index", {
            value_id: $routeParams.value_id
        }));
    };

    $scope.removeLine = function (line) {
        McommerceCart.deleteLine(line.id).success(function (data) {
            if (data.success) {
                if (angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.setText(data.message)
                        .isError(false)
                        .show();
                }
                // update content
                $scope.loadContent();
            }
        }).error(function (data) {
            if (data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show();
            }
        });
    };

    $scope.changeQuantity = function(qty, params) {
        $scope.is_loading = true;
        params.line.qty = qty;
        McommerceCart.modifyLine(params.line).success(function(data) {

            angular.forEach($scope.cart.lines,function(line,index) {
                if(line.id == data.line.id) {
                    $scope.cart.lines[index] = data.line;
                }
            });

            $scope.cart.formattedSubtotalExclTax = data.cart.formattedSubtotalExclTax;
            $scope.cart.formattedDeliveryCost = data.cart.formattedDeliveryCost;
            $scope.cart.formattedTotalExclTax = data.cart.formattedTotalExclTax;
            $scope.cart.formattedTotalTax = data.cart.formattedTotalTax;
            $scope.cart.formattedTotal = data.cart.formattedTotal;
            $scope.cart.deliveryCost = data.cart.deliveryCost;
            $scope.cart.valid = data.cart.valid;

        }).error(function (data) {
            if (data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show();
            }
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});