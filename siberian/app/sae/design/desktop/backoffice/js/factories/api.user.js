
App.factory('ApiUser', function($http, Url) {

    var factory = {};

    factory.loadListData = function() {
        return $http({
            method: 'GET',
            url: Url.get("api/backoffice_user_list/load"),
            cache: true,
            responseType:'json'
        });
    };
    factory.loadEditData = function() {
        return $http({
            method: 'GET',
            url: Url.get("api/backoffice_user_edit/load"),
            cache: true,
            responseType:'json'
        });
    };

    factory.findAll = function() {

        return $http({
            method: 'GET',
            url: Url.get("api/backoffice_user_list/findall"),
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
            url: Url.get("api/backoffice_user_edit/find", param),
            cache: false,
            responseType:'json'
        });
    };

    factory.save = function(user) {

        return $http({
            method: 'POST',
            data: user,
            url: Url.get("api/backoffice_user_edit/save"),
            cache: false,
            responseType:'json'
        });
    };

    factory.delete = function(user_id) {

        return $http({
            method: 'POST',
            data: {user_id: user_id},
            url: Url.get("api/backoffice_user_list/delete"),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
