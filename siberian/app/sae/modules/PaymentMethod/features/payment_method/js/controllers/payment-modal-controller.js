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

    $scope.methodIsAllowed = function (paymentMethod) {
        return $scope.options.methods.indexOf(paymentMethod) >= 0;
    };

    $scope.fetchGateways = function () {
        PaymentMethod
        .fetchGateways()
        .then(function (payload) {
            $scope.paymentGateways = payload.gateways;
            $scope.isLoading = false;
        }, function (error) {
            Dialog.alert("Error", error.message, "OK", -1, "payment_method");
            $scope.isLoading = false;
        });
    };

    $scope._pmOnSelect = function () {
        switch ($scope.options.paymentType) {
            case PaymentMethod.PAYMENT:
                break;
            case PaymentMethod.AUTHORIZATION:

                break;
        }
    };

    $scope.fetchGateways();
});