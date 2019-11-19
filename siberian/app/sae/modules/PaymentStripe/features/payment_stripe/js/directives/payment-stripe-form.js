/**
 * Directive payment-stripe-form
 */
angular
.module("starter")
.directive("paymentStripeForm", function () {
    return {
        restrict: "E",
        replace: true,
        templateUrl: "features/payment_stripe/assets/templates/l1/payment-stripe-form.html",
        controller: function ($scope, $translate, PaymentStripe) {
            if ($scope.options.enableVaults) {
                $scope.buttonText = "Save card";
                $scope.action = "card-setup";
            } else {
                $scope.buttonText = "Pay";
                $scope.action = "card-payment";
            }
            $scope.title = "Card details";

            $scope.validateAction = function () {
                switch ($scope.action) {
                    case "card-payment":
                    default:
                        PaymentStripe
                        .handleCardPayment()
                        .then(function (payload) {
                            // Callback to the main paymentHandler
                            $scope._pmOnSelect(payload);
                        });
                        break;
                    case "card-setup":
                        PaymentStripe
                        .handleCardSetup();
                        // No callback here, we just save a new card!
                        break;
                }
            };

            PaymentStripe.initCardForm();
        }
    };
});
