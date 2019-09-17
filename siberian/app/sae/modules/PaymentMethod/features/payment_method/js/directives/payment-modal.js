/**
 * Directive payment-modal
 */
angular
.module("starter")
.directive("paymentModal", function () {
    return {
        restrict: "E",
        replace: true,
        scope: {
            title: "=?",
            options: "=?"
        },
        templateUrl: "features/payment_method/assets/templates/l1/payment-modal.html",
        compile: function (element, attrs) {
            if (!attrs.title) {
                attrs.title = "Select a payment method";
            }
        },
        controller: function ($scope) {

        }
    };
});
