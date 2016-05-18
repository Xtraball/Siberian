App.factory('McommerceSalesPayment', function ($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findPaymentMethods = function () {

        if (!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("mcommerce/mobile_sales_payment/findpaymentmethods", {
                value_id: this.value_id
            }),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.findOnlinePaymentUrl = function () {

        if (!this.value_id) return;

        return $http({
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

        return $http.post(url, data);
    };

    factory.validatePayment = function() {

        if (!this.value_id) return;

        var url = Url.get("mcommerce/mobile_sales_payment/validatepayment", {
            value_id: this.value_id
        });

        return $http.post(url, {validate_payment:1});

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

        return $http({
            method: 'POST',
            data: data,
            url: url,
            responseType:'json'
        });
    };

    return factory;
});