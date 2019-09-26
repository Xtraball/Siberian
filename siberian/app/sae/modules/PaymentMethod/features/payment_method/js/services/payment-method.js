/**
 * PaymentMethod service
 */
angular
.module("starter")
.service("PaymentMethod", function (Modal) {
    var service = {
        modal: null
    };

    service.onStart = function () {};

    service.openModal = function ($scope, options) {
        Modal.fromTemplateUrl("./features/payment_method/assets/templates/l1/payment-modal.html", {
            scope: angular.extend($scope, {
                options: options
            })
        }).then(function(modal) {
            service.modal = modal;
            service.modal.show();
        });
    };

    service.closeModal = function () {
        service.modal.hide();
    };

    return service;
});
