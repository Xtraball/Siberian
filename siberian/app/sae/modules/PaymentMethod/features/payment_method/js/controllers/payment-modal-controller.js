/**
 *
 */
angular
    .module('starter')
    .controller('PaymentModalController', function ($scope, Dialog, PaymentMethod) {
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
                }, function (error) {
                    Dialog.alert('Error', error.message, 'OK', -1, 'payment_method');
                }).then(function () {
                    $scope.isLoading = false;
                });
        };

        $scope.paymentModal = {
            onSelect: function (paymentMethod) {
                $scope.options.onSelect(paymentMethod);
            }
        };

        $scope.fetchGateways();
    });