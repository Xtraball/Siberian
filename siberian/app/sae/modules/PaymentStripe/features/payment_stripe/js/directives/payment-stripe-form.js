/**
 * Directive payment-stripe-form
 */
angular
.module("starter")
.directive("paymentStripeForm", function () {
    return {
        restrict: "E",
        replace: true,
        scope: {
            buttonText: "=?",
            action: "=?",
            paymentIntentEndpoint: "=?",
            setupIntentEndpoint: "=?"
        },
        templateUrl: "features/payment_stripe/assets/templates/l1/payment-stripe-form.html",
        compile: function(element, attrs){
            if (!attrs.buttonText) {
                attrs.buttonText = "Save";
            }

            if (!attrs.action) {
                attrs.action = "create-token";
            }

            if (!attrs.paymentIntentEndpoint) {
                attrs.paymentIntentEndpoint = "";
            }

            if (!attrs.setupIntentEndpoint) {
                attrs.setupIntentEndpoint = "";
            }
        },
        controller: function ($scope, $translate, PaymentStripe) {
            $scope.getButtonText = function () {
                return $translate.instant($scope.buttonText, "payment_stripe");
            };

            $scope.createToken = function () {
                PaymentStripe.createToken();
            };

            $scope.saveAction = function () {
                switch ($scope.action) {
                    case "card-payment":
                        PaymentStripe.cardPayment($scope.paymentIntentEndpoint);
                        break;
                    case "card-setup":
                        PaymentStripe.cardSetup($scope.setupIntentEndpoint);
                        break;
                    case "card-token":
                    default:
                        PaymentStripe.cardToken();
                }
            };
        }
    };
});
