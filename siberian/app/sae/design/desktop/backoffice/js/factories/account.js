
App.factory('Account', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/account_list/load"),
            cache: true,
            responseType:'json'
        });
    };
    factory.loadViewData = function() {
        return $http({
            method: 'GET',
            url: Url.get("backoffice/account_view/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/account_list/findall"),
            cache: false,
            responseType:'json'
        });
    };

    factory.find = function(user_id) {

        var param = {};
        if(user_id) {
            param = {user_id: user_id};
        }
        return $http({
            method: 'GET',
            url: Url.get("backoffice/account_view/find", param),
            cache: true,
            responseType:'json'
        });
    };

    factory.save = function(user) {

        return $http({
            method: 'POST',
            data: user,
            url: Url.get("backoffice/account_view/save"),
            cache: false,
            responseType:'json'
        });
    };

    factory.delete = function(user_id) {
        return $http({
            method: 'POST',
            data: {user_id: user_id},
            url: Url.get("backoffice/account_list/delete"),
            cache: false,
            responseType:'json'
        });

    };

    return factory;
});
