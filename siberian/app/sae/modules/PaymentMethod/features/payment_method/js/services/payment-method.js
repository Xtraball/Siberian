/**
 * PaymentMethod service
 */
angular
.module('starter')
.service('PaymentMethod', function (Modal, $pwaRequest) {
    var service = {
        modal: null,
        PAYMENT: 'payment',
        AUTHORIZATION: 'authorization',
        SETUP: 'setup',
        ACTION_PAY: 'pay',
        ACTION_AUTHORIZE: 'authorize',
        ACTION_DELETE: 'delete',
        onStart: function () {
            // Right now there is nothing to do onStart!
        },
        openModal: function ($scope, options) {
            Modal.fromTemplateUrl('./features/payment_method/assets/templates/l1/payment-modal.html', {
                scope: angular.extend($scope, {
                    options: options
                }),
                animation: 'slide-in-right-left'
            }).then(function (modal) {
                service.modal = modal;
                service.modal.show();
            });
        },
        closeModal: function () {
            service.modal.remove();
        },
        fetchGateways: function () {
            return $pwaRequest.get('/paymentmethod/mobile_gateway/fetch-all');
        }
    };
    return service;
});
