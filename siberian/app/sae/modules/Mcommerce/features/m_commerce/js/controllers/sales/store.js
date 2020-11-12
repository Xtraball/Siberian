/**
 * MCommerceSalesStoreChoiceController
 */
angular
    .module('starter')
    .controller('MCommerceSalesStoreChoiceController', function (Loader, $scope, $state, $stateParams, $timeout,
                                                                 $translate, Dialog, McommerceSalesStorechoice) {

        $scope.value_id = $stateParams.value_id;
        McommerceSalesStorechoice.value_id = $stateParams.value_id;
        $scope.selected_store = {
            id: -1
        };

        $scope.loadContent = function () {
            $scope.is_loading = true;
            McommerceSalesStorechoice
                .find()
                .then(function (data) {
                    $scope.stores = data.stores;
                    $scope.cart_amount = data.cart_amount;
                    $timeout(function () {
                        $scope.selected_store.id = data.store_id;
                    });
                }).then(function () {
                    $scope.is_loading = false;
                });
        };

        $scope.chooseStore = function () {
            if ($scope.selected_store.id !== -1) {
                Loader.show($translate.instant('Updating...', 'm_commerce'));
                $scope.min_amount = 0;
                angular.forEach($scope.stores, function (store) {
                    if (store.id == $scope.selected_store.id) {
                        $scope.min_amount = store.min_amount;
                        $scope.error_message = store.error_message;
                    }
                });

                if ($scope.min_amount <= $scope.cart_amount) {
                    McommerceSalesStorechoice
                        .update($scope.selected_store.id)
                        .then(function (data) {
                            Loader.hide();
                            if (data.store_id) {
                                $state.go("mcommerce-sales-customer", {
                                    value_id: $scope.value_id
                                });
                            }
                        }).then(function () {
                        Loader.hide();
                    });
                } else {
                    Dialog.alert("", $scope.error_message, "OK", -1, 'm_commerce');
                    Loader.hide();
                }
            }
        };

        $scope.right_button = {
            action: $scope.chooseStore,
            label: $translate.instant('Proceed', 'm_commerce')
        };

        $scope.loadContent();
    });
