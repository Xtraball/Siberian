App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_sales_storechoice/index/value_id/:value_id", {
        controller: 'MCommerceSalesStoreChoiceController',
        templateUrl: BASE_URL + "/mcommerce/mobile_sales_storechoice/template",
        code: "mcommerce-sales-storechoice"
    });

}).controller('MCommerceSalesStoreChoiceController', function ($scope, $location, $routeParams, Message, McommerceSalesStorechoice, Url) {

    $scope.value_id = McommerceSalesStorechoice.value_id = $routeParams.value_id;
    $scope.selected_store = {id:null};

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.loadContent = function () {

        McommerceSalesStorechoice.find().success(function (data) {
            $scope.stores = data.stores;
            $scope.cart_amount = data.cart_amount;
            $scope.selected_store.id = data.store_id;
            if($scope.selected_store.id) {
                $scope.chooseStore();
            }
        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.chooseStore = function() {
        if($scope.selected_store.id) {
            $scope.min_amount = 0;
            angular.forEach($scope.stores,function(store) {
                if(store.id == $scope.selected_store.id) {
                    $scope.min_amount = store.min_amount;
                    $scope.error_message = store.error_message;
                }
            });

            if($scope.min_amount <= $scope.cart_amount) {
                $scope.is_loading = false;
                McommerceSalesStorechoice.update($scope.selected_store.id).success(function (data) {
                    if (data.store_id) {
                        $scope.showNextButton();
                    }
                }).finally(function () {
                    $scope.is_loading = false;
                });
            } else {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText($scope.error_message)
                    .show();
                $scope.hideNextButton();
            }
        }
    };

    $scope.goToOverview = function () {

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            $location.path(Url.get("mcommerce/mobile_sales_customer/index", {
                value_id: $scope.value_id
            }));
        }
    };

    $scope.showNextButton = function() {
        $scope.header_right_button = {
            action: $scope.goToOverview,
            title: "Proceed"
        };
    };

    $scope.hideNextButton = function() {
        $scope.header_right_button = {hide_arrow: true};
    };

    $scope.loadContent();

});