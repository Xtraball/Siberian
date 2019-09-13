/**
 * Directive payment-stripe
 */
angular
.module("starter")
.directive("paymentStripe", function () {
    return {
        restrict: "E",
        replace: true,
        scope: {
            options: "=?",
            cardsOptions: "=?",
            formOptions: "=?",
            formIsOpen: "=?"
        },
        templateUrl: "features/payment_stripe/assets/templates/l1/payment-stripe.html",
        compile: function (element, attrs) {
            if (!attrs.options) {
                attrs.options = {
                    title: "Add a credit card"
                };
            }

            if (!attrs.formOptions) {
                attrs.formOptions = {
                    title: "Credit card information",
                    action: "card-setup",
                    buttonText: "Save"
                };
            }

            if (!attrs.cardsOptions) {
                attrs.cardsOptions = {
                    title: "Saved cards",
                    lineAction: function () {},
                    actions: []
                };
            }

            if (!attrs.formIsOpen) {
                attrs.formIsOpen = false;
            }
        },
        controller: function ($scope) {
            $scope.showForm = $scope.formIsOpen;

            $scope.toggleForm = function () {
                $scope.showForm = !$scope.showForm;
            };
        }
    };
});
