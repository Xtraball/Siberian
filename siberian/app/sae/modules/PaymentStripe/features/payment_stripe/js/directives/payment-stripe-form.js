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
        },
        templateUrl: "features/payment_stripe/assets/templates/l1/payment-stripe-form.html",
        compile: function(element, attrs){
            if (!attrs.buttonText) {
                attrs.buttonText = "Save";
            }

            if (!attrs.action) {
                attrs.action = "card-payment";
            }
        },
        controller: function ($scope, $translate, PaymentStripe) {
            $scope.getButtonText = function () {
                return $translate.instant($scope.buttonText, "payment_stripe");
            };

            $scope.saveAction = function () {
                switch ($scope.action) {
                    case "card-payment":
                    default:
                        PaymentStripe.handleCardPayment();
                        break;
                    case "card-setup":
                        PaymentStripe.handleCardSetup();
                        break;
                }
            };

            PaymentStripe.initCardForm();
        }
    };
});
