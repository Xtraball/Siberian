/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("MCommerceSalesStoreChoiceController", function (Loader, $scope, $state, $stateParams,
                                                               $translate, Dialog, McommerceSalesStorechoice) {

    $scope.value_id = $stateParams.value_id;
    McommerceSalesStorechoice.value_id = $stateParams.value_id;
    $scope.selected_store = {id:null};

    $scope.loadContent = function () {
        $scope.is_loading = true;
        Loader.show();
        McommerceSalesStorechoice
            .find()
            .then(function (data) {
                $scope.stores = data.stores;
                $scope.cart_amount = data.cart_amount;
                $scope.selected_store.id = data.store_id;
                if($scope.selected_store.id) {
                    $scope.chooseStore();
                }
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
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
                Loader.show();
                McommerceSalesStorechoice
                    .update($scope.selected_store.id)
                    .then(function (data) {
                        if (data.store_id) {
                            $scope.showNextButton();
                        }
                    }).then(function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });

            } else {
                Dialog.alert("", $scope.error_message, "OK");
                $scope.hideNextButton();
            }
        }
    };

    $scope.goToOverview = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-sales-customer", {
                value_id: $scope.value_id
            });
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