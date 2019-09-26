angular
.module("starter")
.controller("PaymentModalController", function ($scope, PaymentMethod) {
    angular.extend($scope, {
        paymentGateways: []
    });

    $scope.closeModal = function () {
        PaymentMethod.closeModal();
    };

    $scope.fetchGateways = function () {
        $scope.paymentGateways = [];
    };

    window.printOptions = function () {
        console.log($scope.options);
    };

});