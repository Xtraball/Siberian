App.factory('Customer', function($http, $ionicModal, $rootScope, $templateCache, $window, httpCache, Url, AUTH_EVENTS, CACHE_EVENTS) {

    var factory = {};

    factory.id = null;
    factory.can_access_locked_features = false;
    factory.events = [];
    factory.modal = null;

    factory.onStatusChange = function(id, urls) {
        factory.events[id] = urls;
    };

    factory.flushData = function() {

        for(var i in factory.events) {

            if(angular.isArray(factory.events[i])) {
                var data = factory.events[i];
                for(var j = 0; j < data.length; j++) {
                    httpCache.remove(data[j]);
                }
            }

        }

    };

    factory.loginModal = function(scope) {
        $ionicModal.fromTemplateUrl('templates/customer/account/l1/login.html', {
            scope: scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            factory.modal = modal;
            factory.modal.show();
        });
    };

    factory.login = function(data) {
        data.device_uid = device.uuid;

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_login/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.saveCredentials(data.token);

            factory.id = data.customer_id;
            factory.can_access_locked_features = data.can_access_locked_features;
            factory.flushData();
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
        });
    };

    factory.loginWithFacebook = function(token) {
        var data = {
            device_id: device.uuid,
            token: token
        };

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_login/loginwithfacebook"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.saveCredentials(data.token);

            factory.id = data.customer_id;
            factory.can_access_locked_features = data.can_access_locked_features;
            factory.flushData();
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
        });
    };

    factory.register = function(data) {
        data.device_uid = device.uuid;

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_register/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.saveCredentials(data.token);

            factory.id = data.customer_id;
            factory.can_access_locked_features = data.can_access_locked_features;
            factory.flushData();
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
        });
    };

    factory.getAvatarUrl = function(customer_id, options) {
        options = angular.isObject(options) ? options : {};
        var url = Url.get("/customer/mobile_account/avatar", angular.extend({}, options, {customer: customer_id})) + "?" +(+new Date());
        return url;
    };

    factory.save = function(data) {

        if(!factory.isLoggedIn()) {
            return factory.register(data);
        }

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_edit/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            if(data.clearCache) {
                $rootScope.$broadcast(CACHE_EVENTS.clearSocialGaming);
            }
        });
    };

    factory.forgottenpassword = function(email) {

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_forgottenpassword/post"),
            data: {email: email},
            responseType:'json'
        });
    };

    factory.logout = function() {

        return $http({
            method: 'GET',
            url: Url.get("customer/mobile_account_login/logout"),
            responseType:'json'
        }).success(function() {
            factory.clearCredentials();

            factory.id = null;
            factory.can_access_locked_features = false;
            factory.flushData();
            $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
        });
    };

    factory.find = function() {
        return $http({
            method: 'GET',
            url: Url.get("customer/mobile_account_edit/find"),
            responseType:'json'
        });
    };

    factory.isLoggedIn = function() {
        return !!this.id;
    };

    factory.saveCredentials = function (token) {
        $window.localStorage.setItem("sb-auth-token", token);
    };

    factory.clearCredentials = function () {
        $window.localStorage.removeItem('sb-auth-token');
    };

    return factory;
});
