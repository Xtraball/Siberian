
App.factory('Subscription', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("subscription/backoffice_list/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.loadEditData = function(subscription_id) {
        return $http({
            method: 'GET',
            url: Url.get("subscription/backoffice_edit/load", {id: subscription_id}),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("subscription/backoffice_list/findall"),
            cache: false,
            responseType:'json'
        });
    };

    factory.find = function(subscription_id) {

        return $http({
            method: 'GET',
            url: Url.get("subscription/backoffice_edit/find", {id: subscription_id}),
            cache: false,
            responseType:'json'
        });
    };

    factory.save = function(subscription) {

        return $http({
            method: 'POST',
            data: subscription,
            url: Url.get("subscription/backoffice_edit/save"),
            responseType:'json'
        });
    };

    return factory;
});
