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
            cache: false,
            refresh: true
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
            cache: false,
            refresh: true
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

        var formData = {
            validate_payment: 1,
            customer_uuid: $session.getDeviceUid(),
            notes: factory.notes || ''
        };

        // fix(mcommerce): very old system, check to remove it from the validatepaymentAction controller in the future!
        if (IS_NATIVE_APP) {
            formData.is_ajax = true;
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/validatepayment', {
            urlParams: {
                value_id: this.value_id
            },
            data: formData
        });
    }

    return factory;
});
