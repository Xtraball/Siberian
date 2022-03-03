/**
 * PaymentStripeController
 */
angular
    .module('starter')
    .controller('PaymentStripeController', function ($scope, PaymentStripe) {
        angular.extend($scope, {
            showForm: false,
            showPaymentForm: false
        });

        $scope.onSelect = function () {
            $scope.showPaymentForm = !$scope.showPaymentForm;

            try {
                $scope.options.onSelect({
                    paymentId: PaymentStripe.paymentId,
                    shortName: 'stripe'
                });
            } catch (e) {
                console.error('Something wrong occurred, please review your Stripe configuration.', e);
            }
        };

        $scope.toggleForm = function () {
            $scope.showForm = !$scope.showForm;
        };
    });

