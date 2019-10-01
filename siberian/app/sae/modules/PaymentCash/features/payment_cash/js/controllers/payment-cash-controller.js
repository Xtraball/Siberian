angular
.module("starter")
.controller("PaymentCashController", function ($scope) {
    angular.extend($scope, {
        isLoading: false
    });
});