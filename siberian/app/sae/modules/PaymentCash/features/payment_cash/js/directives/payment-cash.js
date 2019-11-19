/**
 * Directive payment-cash
 */
angular
.module("starter")
.directive("paymentCash", function () {
    return {
        restrict: "E",
        replace: true,
        scope: {
            options: "=?"
        },
        templateUrl: "features/payment_cash/assets/templates/l1/payment-cash.html",
        compile: function (element, attrs) {
            if (!attrs.options) {
                attrs.options = {};
            }
        },
        controller: function ($scope) {
            // Nope!
        }
    };
});
