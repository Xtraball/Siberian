/* global
    App
 */
angular.module('starter').factory('McommerceSalesCustomer', function ($pwaRequest) {
    var factory = {
        value_id: null
    };

    factory.updateCustomerInfos = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::updateCustomerInfos] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_customer/update', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form,
                option_value_id: this.value_id
            }
        });
    };

    factory.find = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::find] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/find', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.hasGuestMode = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::hasGuestMode] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/hasguestmode', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.getOrderHistory = function (offset) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::getOrderHistory] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/getorders', {
            urlParams: {
                value_id: this.value_id,
                offset: offset
            },
            cache: false
        });
    };

    factory.getOrderDetails = function (order_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::getOrderDetails] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/getorderdetails', {
            urlParams: {
                value_id: this.value_id,
                order_id: order_id
            },
            cache: false
        });
    };


    return factory;
});
