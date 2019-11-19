angular
.module("starter")
.controller("PaymentCashController", function ($scope) {
    angular.extend($scope, {
        isLoading: false
    });

    $scope.lineActionTrigger = function () {
        // Callback the main payment handler!
        if (typeof $scope.$parent.paymentModal.onSelect === "function") {
            $scope.$parent.paymentModal.onSelect({
                method: "\\PaymentCash\\Model\\Cash",
                id: "cash"
            });
        }
    };
});