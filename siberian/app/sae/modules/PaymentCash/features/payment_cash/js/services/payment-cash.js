/**
 * PaymentCash service
 */
angular
    .module('starter')
    .service('PaymentCash', function ($pwaRequest) {
        var service = {};

        service.fetchPayment = function (options) {
            console.log(options);
            return $pwaRequest.post('/paymentcash/mobile_cash/fetch-payment',
                {
                    urlParams: {
                        value_id: options.valueId
                    },
                    data: {
                        options: options
                    }
                });
        };

        return service;
    });
