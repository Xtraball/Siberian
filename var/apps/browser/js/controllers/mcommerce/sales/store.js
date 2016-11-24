App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-sales-store', {
        url: BASE_PATH+"/mcommerce/mobile_sales_storechoice/index/value_id/:value_id",
        controller: 'MCommerceSalesStoreChoiceController',
        templateUrl: "templates/mcommerce/l1/sales/store.html",
        cache:false
    });

}).controller('MCommerceSalesStoreChoiceController', function ($ionicLoading, $location, $scope, $state, $stateParams, $translate, Dialog, McommerceSalesStorechoice) {

    $scope.value_id = McommerceSalesStorechoice.value_id = $stateParams.value_id;
    $scope.selected_store = {id:null};

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.loadContent = function () {
        $scope.is_loading = true;
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });
        McommerceSalesStorechoice.find().success(function (data) {
            $scope.stores = data.stores;
            $scope.cart_amount = data.cart_amount;
            $scope.selected_store.id = data.store_id;
            if($scope.selected_store.id) {
                $scope.chooseStore();
            }
        }).finally(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
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
                $scope.is_loading = true;
                $ionicLoading.show({
                    template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
                });
                McommerceSalesStorechoice.update($scope.selected_store.id).success(function (data) {
                    if (data.store_id) {
                        $scope.showNextButton();
                    }
                }).finally(function () {
                    $scope.is_loading = false;
                    $ionicLoading.hide();
                });
            } else {
                Dialog.alert("", $scope.error_message, $translate.instant("OK"));
                $scope.hideNextButton();
            }
        }
    };

    $scope.goToOverview = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-sales-customer", {value_id: $scope.value_id});
        }
    };

    $scope.showNextButton = function() {
        $scope.right_button = {
            action: $scope.goToOverview,
            label: $translate.instant("Proceed")
        };
    };

    $scope.hideNextButton = function() {
        $scope.right_button = {
            action: null,
            label: ""
        };
    };

    $scope.loadContent();

});