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
        // Bypass
        return true;
        
        methods.forEach(function (method) {
            if ($scope.options.methods.indexOf(method) >= 0) {
                return true;
            }
        });
        return false;
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

    $scope.fetchGateways();
});