angular
.module("starter")
.controller("PaymentStripeController", function ($scope) {
    angular.extend($scope, {
        showForm: false
    });

    $scope.toggleForm = function () {
        $scope.showForm = !$scope.showForm;
    };
});

