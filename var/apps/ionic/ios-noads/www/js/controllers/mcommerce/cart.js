App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-cart-view', {
        url: BASE_PATH+"/mcommerce/mobile_cart/index/value_id/:value_id",
        controller: 'MCommerceCartViewController',
        templateUrl: "templates/mcommerce/l1/cart.html",
        cache:false
    })

}).controller('MCommerceCartViewController', function ($scope, $state, $http, $ionicLoading, $stateParams, $translate, Dialog, McommerceCart) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });
    // counter of pending tip calls
    var updateTipTimoutFn = null;
    $scope.is_loading = true;

    McommerceCart.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("Cart");
    
    $scope.loadContent = function () {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner><br/><br/>" + $translate.instant("Updating price") + "..."
        });
        $scope.is_loading = true;
        McommerceCart.find().success(function(data) {
            if(data.cart.tip === 0) {
                data.cart.tip = "";
            }

            data.cart.discount_code = $scope.cart ? $scope.cart.discount_code : "";
            $scope.cart = data.cart;
            $scope.nb_stores = data.nb_stores;

            if($scope.cart.lines.length > 0) {
                $scope.right_button = {
                    action: $scope.proceed,
                    label: $translate.instant("Proceed")
                };
            }

        }).finally(function () {
            $ionicLoading.hide();
            $scope.is_loading = false;
        });
    };

    $scope.updateTip = function(){
        var update = function () {
            $ionicLoading.show({
                template: "<ion-spinner class=\"spinner-custom\"></ion-spinner><br/><br/>" + $translate.instant("Updating price") + "..."
            });
            $scope.is_loading = true;
            McommerceCart.addTip($scope.cart).success(function (data) {
                $ionicLoading.hide();
                $scope.is_loading = false;
                if (data.success) {
                    if (angular.isDefined(data.message)) {
                        Dialog.alert("", data.message, $translate.instant("OK"));
                        return;
                    }
                    $scope.loadContent();
                }
            }).error(function (data) {
                $ionicLoading.hide();
                $scope.is_loading = false;
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert("", data.message, $translate.instant("OK"));
                }
            });
        }
        if(updateTipTimoutFn) {
            clearTimeout(updateTipTimoutFn);
        }
        //wait 100ms before update
        updateTipTimoutFn = setTimeout(function(){
            update();
        },600);
    };

    $scope.proceed = function() {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });

        var gotToNext = function() {
            if(!$scope.cart.valid) {
                $scope.cartIdInvalid();
            } else if($scope.nb_stores > 1) {
                $scope.goToStoreChoice();
            } else {
                $scope.goToOverview();
            }
        }

        if($scope.cart && $scope.cart.discount_code) {
            McommerceCart.adddiscount($scope.cart.discount_code).success(function(data){
                if(data && data.success) {
                    gotToNext();
                } else {
                    if(data && data.message) {
                        Dialog.alert("", data.message, $translate.instant("OK"));
                    } else {
                        Dialog.alert("", $translate.instant("Unexpected Error"), $translate.instant("OK"));
                    }
                }
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert("", data.message, $translate.instant("OK"));
                }
            }).finally(function(){
                $ionicLoading.hide();
            });
        } else {
            gotToNext();
        }
    };

    $scope.cartIdInvalid = function() {
        Dialog.alert("", $scope.cart.valid_message, $translate.instant("OK"));
    };

    $scope.goToStoreChoice = function() {
        $state.go("mcommerce-sales-store", {value_id: $scope.value_id});
    };

    $scope.goToOverview = function () {
        if(!$scope.is_loading) {
            $state.go("mcommerce-sales-customer", {value_id: $scope.value_id});
        }
    };

    $scope.goToCategories = function () {
        $state.go("mcommerce-category-list", {value_id: $scope.value_id});
    };

    $scope.removeLine = function (line) {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner><br/><br/>" + $translate.instant("Updating price") + "..."
        });
        $scope.is_loading = true;
        McommerceCart.deleteLine(line.id).success(function (data) {
            if (data.success) {
                if (angular.isDefined(data.message)) {
                    Dialog.alert("", data.message, $translate.instant("OK"));
                    return;
                }
                // update content
                $scope.loadContent();
            }
        }).error(function (data) {
            if (data && angular.isDefined(data.message)) {
                Dialog.alert("", data.message, $translate.instant("OK"));
            }
        });
    };

    $scope.changeQuantity = function(qty, params) {
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner><br/><br/>" + $translate.instant("Updating price") + "..."
        });
        $scope.is_loading = true;
        params.line.qty = qty;
        McommerceCart.modifyLine(params.line).success(function(data) {
            $ionicLoading.hide();
            $scope.is_loading = false;
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
            $ionicLoading.hide();
            $scope.is_loading = false;
            if (data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show();
            }
        });
    };

    $scope.loadContent();

});