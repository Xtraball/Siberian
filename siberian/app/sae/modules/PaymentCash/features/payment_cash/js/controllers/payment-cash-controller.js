/**
 * PaymentCashController
 */
angular
    .module('starter')
    .controller('PaymentCashController', function ($scope, Dialog, PaymentCash) {
        angular.extend($scope, {
            isLoading: false
        });

        $scope.onSelect = function () {
            try {
                Dialog
                    .confirm('Confirmation', 'Are you sure you want to pay with cash?', ['Yes, continue', 'No'], 'text-center', 'payment_cash')
                    .then(function (result) {
                        if (result) {
                            PaymentCash
                                .fetchPayment($scope.$parent.options)
                                .then(function (result) {
                                    $scope.$parent.options.onSuccess({
                                        paymentId: result.paymentId,
                                        shortName: 'cash'
                                    });
                                });
                        }
                    });
            } catch (e) {
                console.error('Something wrong occurred, please review your Cash configuration.', e);
            }
        };
    });