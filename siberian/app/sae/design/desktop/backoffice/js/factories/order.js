
App.factory('Order', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_order_list/load"),
            cache: true,
            responseType:'json'
        });
    };
    factory.loadViewData = function() {
        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_order_view/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_order_list/findall"),
            cache: true,
            responseType:'json'
        });
    };

    factory.find = function() {

        return $http({
            method: 'GET',
            url: Url.get("sales/backoffice_order_view/find"),
            cache: true,
            responseType:'json'
        });
    };

    return factory;
});
