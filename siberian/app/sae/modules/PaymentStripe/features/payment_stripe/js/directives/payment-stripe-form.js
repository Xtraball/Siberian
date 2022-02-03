/**
 * @directive paymentStripeForm <payment-stripe-form>
 */
angular
    .module('starter')
    .directive('paymentStripeForm', function () {
        return {
            restrict: 'E',
            replace: true,
            templateUrl: 'features/payment_stripe/assets/templates/l1/payment-stripe-form.html',
            controller: function ($scope, $rootScope, $translate, PaymentStripe, PaymentMethod) {

                // Default is direct pay!
                $scope.buttonText = 'Pay';
                $scope.action = 'card-payment';

                // Authorize
                if (PaymentMethod.AUTHORIZATION === $scope.options.type) {
                    $scope.buttonText = 'Save';
                    $scope.action = 'card-setup';
                }

                $scope.title = 'Card details';

                $scope.validateAction = function () {
                    switch ($scope.action) {
                        case 'card-payment':
                        default:
                            PaymentStripe
                                .handleCardPayment()
                                .then(function (payload) {

                                });
                            break;
                        case 'card-authorize':
                            PaymentStripe
                                .handleCardAuthorization()
                                .then(function (payload) {

                                });
                            // No callback here, we just save a new card!
                            break;
                        case 'card-setup':
                            PaymentStripe
                                .handleCardSetup()
                                .then(function (payload) {
                                    // Refresh cards list
                                    $rootScope.$broadcast('paymentStripeCards.refresh');
                                });
                                // No callback here, we just save a new card!
                            break;
                    }
                };

                $scope.init = function () {
                    PaymentStripe.initCardForm();
                };

                $scope.init();
            }
        };
    });
