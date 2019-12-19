/**
 * Directive payment-stripe-form
 */
angular
.module('starter')
.directive('paymentStripeCards', function (PaymentMethod) {
    return {
        restrict: 'E',
        replace: true,
        scope: {
            actions: '=?'
        },
        templateUrl: 'features/payment_stripe/assets/templates/l1/payment-stripe-cards.html',
        compile: function (element, attrs) {
            if (!attrs.actions) {
                attrs.actions = [
                    PaymentMethod.ACTION_PAY,
                    PaymentMethod.ACTION_DELETE
                ];
            }
        },
        controller: function ($scope, $rootScope, $pwaRequest, $q, $timeout, Dialog, Loader, PaymentStripe) {
            $scope.isLoading = true;

            $scope.fetchVaults = function () {
                $scope.isLoading = true;

                PaymentStripe
                .fetchVaults()
                .then(function (payload) {
                    $timeout(function () {
                        $scope.cards = payload.vaults;
                    });
                }, function (error) {
                    Dialog.alert('Error', error.message, 'OK', -1, 'payment_stripe');
                }).then(function () {
                    $scope.isLoading = false;
                });
            };

            $scope.lineActionTrigger = function (card) {
                Loader.show();
                if ($scope.actions.length > 0) {
                    // first action = line action
                    var firstAction = $scope.actions[0];
                    $scope
                    .doAction(firstAction, card)
                    .then(function (payload) {
                        // Callback the main payment handler!
                        switch (firstAction) {
                            case PaymentMethod.ACTION_PAY:
                            case PaymentMethod.ACTION_AUTHORIZE:
                                if (typeof $scope.$parent.paymentModal.onSelect === 'function') {
                                    $scope.$parent.paymentModal.onSelect(payload.intentPayload.paymentId);
                                }
                                break;
                            default:
                                // Do nothing yet!
                        }

                    }, function (error) {
                        // Sorry!
                    }).then(function () {
                        Loader.hide();
                    });
                }
            };

            $scope.doAction = function (action, card) {
                var defer = $q.defer();
                switch (action) {
                    default:
                        Dialog
                            .alert('Error', "This action doesn't exists.", 'OK', -1)
                            .then(function () {
                                defer.reject('Unkown action');
                            });
                        break;
                    case PaymentMethod.ACTION_PAY:
                        PaymentStripe
                            .handleCardPayment({
                                card: card,
                                amount: $scope.$parent.options.payment.amount
                            })
                            .then(function (success) {
                                defer.resolve(success);
                            }, function (error) {
                                defer.reject(error);
                            });
                        break;
                    case PaymentMethod.ACTION_AUTHORIZE:
                        PaymentStripe
                            .handleCardAuthorization({
                                card: card,
                                amount: $scope.$parent.options.payment.amount
                            })
                            .then(function (success) {
                                defer.resolve(success);
                            }, function (error) {
                                defer.reject(error);
                            });
                        break;
                    case PaymentMethod.ACTION_DELETE:
                        Loader.hide();
                        Dialog
                            .confirm(
                                'Confirmation',
                                'Are you sure you want to delete this payment method?',
                                ['YES','NO'],
                                '',
                                'payment_stripe')
                            .then(function (result) {
                                if (result) {
                                    PaymentStripe
                                        .deletePaymentMethod(card)
                                        .then(function (success) {
                                            defer.resolve(success);
                                        }, function (error) {
                                            defer.reject(error);
                                        });
                                }
                            });
                        break;
                }

                return defer.promise;
            };

            $scope.brand = function (brand) {
                var _brand = (brand === undefined) ? '' : brand.toLowerCase();
                switch (_brand) {
                    case 'visa':
                        return './features/payment_stripe/assets/templates/images/003-cc-visa.svg';
                    case 'mastercard':
                        return './features/payment_stripe/assets/templates/images/004-cc-mastercard.svg';
                    case 'american express':
                        return './features/payment_stripe/assets/templates/images/005-cc-amex.png';
                }
                return './features/payment_stripe/assets/templates/images/006-cc.svg';
            };

            $scope.actionIcon = function (action) {
                switch (action) {
                    default:
                    case PaymentMethod.ACTION_PAY:
                    case PaymentMethod.ACTION_AUTHORIZE:
                        return 'icon ion-android-arrow-forward';
                    case PaymentMethod.ACTION_DELETE:
                        return 'icon ion-trash-a assertive';
                }
            };

            // Specific refresh for self-contained pages!
            $rootScope.$on('paymentStripeCards.refresh', function () {
                $scope.fetchVaults();
            });

            // Generic refresh from any other page!
            $rootScope.$on('paymentMethod.refresh', function () {
                $scope.fetchVaults();
            });

            $scope.fetchVaults();
        }
    };
});
