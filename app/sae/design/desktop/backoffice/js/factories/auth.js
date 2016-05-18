
App.factory('Auth', function($http, Url) {

    var factory = {};

    factory.user = {};

    factory.login = function(user) {

        return $http({
            method: 'POST',
            url: Url.get("backoffice/account_login/post"),
            data: user,
            responseType:'json'
        }).success(function(data) {
            factory.user = data.user;
        });
    };

    factory.forgottenPassword = function(email) {

        return $http({
            method: 'POST',
            url: Url.get("backoffice/account_login/forgottenpassword"),
            data: {email: email},
            responseType:'json'
        });
    };

    factory.logout = function () {

        return $http({
            method: 'GET',
            url: Url.get("backoffice/account_login/logout"),
            responseType:'json'
        }).success(function(data) {
            factory.user = {};
        });

    };

    factory.isLoggedIn = function() {
        return !!factory.user.id;
    };

    return factory;
});
