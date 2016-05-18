
App.factory('Invoice', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_invoice_list/load"),
            cache: true,
            responseType:'json'
        });
    };
    factory.loadViewData = function() {
        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_invoice_view/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_invoice_list/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.find = function(invoice_id) {

        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_invoice_view/find", {invoice_id: invoice_id}),
            cache: true,
            responseType:'json'
        });
    };

    return factory;
});
