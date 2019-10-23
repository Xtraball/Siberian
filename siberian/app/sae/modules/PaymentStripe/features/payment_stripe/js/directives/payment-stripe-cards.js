/**
 * Directive payment-stripe-form
 */
angular
.module("starter")
.directive("paymentStripeCards", function () {
    return {
        restrict: "E",
        replace: true,
        scope: {
            lineAction: "=?",
            actions: "=?"
        },
        templateUrl: "features/payment_stripe/assets/templates/l1/payment-stripe-cards.html",
        compile: function(element, attrs){
            if (!attrs.actions) {
                attrs.actions = [

                ];
            }

            if (!attrs.lineAction) {
                attrs.lineAction = null;
            }
        },
        controller: function ($scope, $rootScope, $pwaRequest, Dialog, PaymentStripe) {
            $scope.isLoading = true;

            $scope.fetchVaults = function () {
                $scope.isLoading = true;

                PaymentStripe
                .fetchVaults()
                .then(function (payload) {
                    $scope.cards = payload.vaults;
                    $scope.isLoading = false;
                }, function (error) {
                    $scope.isLoading = false;
                    Dialog.alert("Error", error.message, "OK", -1, "payment_stripe");
                });
            };

            $scope.lineActionTrigger = function (card) {
                if (typeof $scope.lineAction === "function") {
                    $scope.lineAction(card);
                }

                // Callback the main payment handler!
                if (typeof $scope.$parent.paymentModal.onSelect === "function") {
                    $scope.$parent.paymentModal.onSelect({
                        method: "\\PaymentStripe\\Model\\Stripe",
                        id: card.id,
                        card: card
                    });
                }
            };

            $scope.actionCallback = function (action, card) {
                if (typeof action.callback === "function") {
                    action.callback(card);
                }
            };

            $scope.brand = function (brand) {
                switch (brand.toLowerCase()) {
                    case "visa":
                        return "./features/payment_stripe/assets/templates/images/003-cc-visa.svg";
                    case "mastercard":
                        return "./features/payment_stripe/assets/templates/images/004-cc-mastercard.svg";
                    case "american express":
                        return "./features/payment_stripe/assets/templates/images/005-cc-amex.png";
                }
                return "./features/payment_stripe/assets/templates/images/006-cc.svg";
            };

            $rootScope.$on("paymentStripeCards.refresh", function () {
                $scope.fetchVaults();
            });

            $scope.fetchVaults();
        }
    };
});
