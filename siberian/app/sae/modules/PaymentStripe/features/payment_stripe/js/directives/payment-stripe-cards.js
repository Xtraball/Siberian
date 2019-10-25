/**
 * Directive payment-stripe-form
 */
angular
.module("starter")
.directive("paymentStripeCards", function (PaymentMethod) {
    return {
        restrict: "E",
        replace: true,
        scope: {
            actions: "=?"
        },
        templateUrl: "features/payment_stripe/assets/templates/l1/payment-stripe-cards.html",
        compile: function (element, attrs) {
            if (!attrs.actions) {
                attrs.actions = [
                    PaymentMethod.ACTION_PAY,
                    PaymentMethod.ACTION_DELETE
                ];
            }
        },
        controller: function ($scope, $rootScope, $pwaRequest, $q, Dialog, PaymentStripe) {
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

            $scope.lineActionTrigger = function (id) {
                if ($scope.actions.length > 0) {
                    // first action = line action
                    var firstAction = $scope.actions[0];
                    $scope.doAction(firstAction);
                }

                // Callback the main payment handler!
                if (typeof $scope.$parent.paymentModal.onSelect === "function") {
                    $scope.$parent.paymentModal.onSelect({
                        method_class: "\\PaymentStripe\\Model\\Stripe",
                        method_id: id
                    });
                }
            };

            $scope.doAction = function (action) {
                var defer = $q.defer();
                switch (action) {
                    default:
                        Dialog
                            .alert("Error", "This action doesn't exists.", "OK", -1)
                            .then(function () {
                                defer.reject("Unkown action");
                            });
                        break;
                    case PaymentMethod.ACTION_PAY:
                        PaymentStripe
                            .handleCardPayment({
                                amount: $scope.options.payment.amount
                            })
                            .then(function (success) {

                            }, function (error) {

                            });
                        break;
                    case PaymentMethod.ACTION_AUTHORIZE:
                        PaymentStripe
                            .handleCardAuthorization()
                            .then(function (success) {

                            }, function (error) {

                            });
                        break;
                    case PaymentMethod.ACTION_DELETE:
                        PaymentStripe
                            .deletePaymentMethod()
                            .then(function (success) {

                            }, function (error) {

                            });
                        break;
                }

                return defer.promise;
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
