angular.module('starter').factory('McommerceSalesPayment', function ($pwaRequest, $session) {
    var factory = {
        value_id: null,
        notes: ''
    };

    factory.findPaymentMethods = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::findPaymentMethods] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_payment/findpaymentmethods', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.findOnlinePaymentUrl = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::findOnlinePaymentUrl] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_payment/findonlinepaymenturl', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.updatePaymentInfos = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::updatePaymentInfos] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/update', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form
            }
        });
    };

    factory.validatePayment = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::validatePayment] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/validatepayment', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                validate_payment: 1,
                customer_uuid: $session.getDeviceUid(),
                notes: factory.notes || '' // TG-459
            }
        });
    };

    factory.validateOnlinePayment = function (token, payer_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::validateOnlinePayment] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/validatepayment', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                token: token,
                PayerID: payer_id,
                payer_id: payer_id,
                is_ajax: 1
            }
        });
    };

    return factory;
});
