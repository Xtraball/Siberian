angular
.module("starter")
.controller("PaymentModalController", function ($scope, Dialog, PaymentMethod) {
    angular.extend($scope, {
        isLoading: true,
        paymentGateways: []
    });

    $scope.closeModal = function () {
        PaymentMethod.closeModal();
    };

    $scope.methodIsAllowed = function (methods) {
        var isAllowed = false;
        methods.forEach(function (method) {
            if ($scope.options.methods.indexOf(method) >= 0) {
                isAllowed = true;
            }
        });
        return isAllowed;
    };

    $scope.fetchGateways = function () {
        PaymentMethod
        .fetchGateways()
        .then(function (payload) {
            $scope.paymentGateways = payload.gateways;
            $scope.isLoading = false;
        }, function (error) {
            Dialog.alert("Error", "There is no configured payment method.", "OK", -1, "payment_method");
            $scope.isLoading = false;
        });
    };

    $scope._pmOnSelect = function () {

    };

    $scope.fetchGateways();
});