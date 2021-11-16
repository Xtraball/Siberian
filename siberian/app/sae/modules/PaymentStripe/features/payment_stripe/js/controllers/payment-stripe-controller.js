/**
 * PaymentStripeController
 */
angular
    .module('starter')
    .controller('PaymentStripeController', function ($scope) {
        angular.extend($scope, {
            showForm: false,
            showPaymentForm: false
        });

        $scope.lineActionTrigger = function () {
            // Callback the main payment handler!
            if (typeof $scope.$parent.paymentModal.onSelect === 'function') {
                $scope.$parent.paymentModal.onSelect({
                    method: '\\PaymentCash\\Model\\Stripe',
                    type: 'credit-card',
                    id: 'stripe'
                });
            }

            $scope.showPaymentForm = true;
        };

        $scope.toggleForm = function () {
            $scope.showForm = !$scope.showForm;
        };
    });

