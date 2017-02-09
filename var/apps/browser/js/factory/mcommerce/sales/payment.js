App.factory('McommerceSalesPayment', function ($rootScope, $sbhttp, Url) {

    var factory = {};

    factory.value_id = null;
    factory.notes = "";

    factory.findPaymentMethods = function () {

        if (!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_payment/findpaymentmethods", {
                value_id: this.value_id
            }),
            cache: false,
            responseType: 'json'
        });
    };

    factory.findOnlinePaymentUrl = function () {

        if (!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_payment/findonlinepaymenturl", {
                value_id: this.value_id
            }),
            cache: false,
            responseType: 'json'
        });
    };

    factory.updatePaymentInfos = function (form) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_payment/update", {
            value_id: this.value_id
        });

        var data = {
            form: form
        };

        return $sbhttp.post(url, data);
    };

    factory.validatePayment = function() {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_payment/validatepayment", {
            value_id: this.value_id
        });

        return $sbhttp.post(url, {
            validate_payment: 1,
            customer_uuid: window.device.uuid,
            notes: factory.notes || "" // TG-459
        });

    };

    factory.validateOnlinePayment = function (token, payerID) {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_payment/validatepayment", {
            value_id: this.value_id
        });

        var data = {
            token: token,
            PayerID: payerID,
            is_ajax: 1
        };

        return $sbhttp({
            method: 'POST',
            data: data,
            url: url,
            responseType:'json'
        });
    };

    return factory;
});