App.factory('Customer', function($sbhttp, $ionicModal, $rootScope, $templateCache, $window, httpCache, Application, Url, AUTH_EVENTS, CACHE_EVENTS) {
    var factory = {};

    var _id = null;

    Object.defineProperty(factory, "id", {
        get: function() {
            return _id;
        },
        set: function(value) {
            var _broadcast_events = (value != _id);
            _id = value;
            if(_broadcast_events) {
                var loggedIn = factory.isLoggedIn();
                $rootScope.$broadcast(AUTH_EVENTS.loginStatusChanged, loggedIn);
                $rootScope.$broadcast(AUTH_EVENTS[loggedIn ? "loginSuccess" : "logoutSuccess"]);
            }
            return _id;
        }
    });

    Object.defineProperty($rootScope, "customer_id", {
        get: function() {
            return factory.id;
        }
    }); // symbolic link to bypass dependency injection for Application service


    factory.can_access_locked_features = false;
    factory.events = [];
    factory.modal = null;
    factory.display_account_form = false;

    factory.onStatusChange = function(id, urls) {
        factory.events[id] = urls;
    };

    factory.flushData = function() {

        for(var i in factory.events) {

            if(angular.isArray(factory.events[i])) {
                var data = factory.events[i];
                for(var j = 0; j < data.length; j++) {
                    if (typeof data[j] != "undefined") {
                        httpCache.remove(data[j]);
                    }
                }
            }

        }

    };

    factory.loginModal = function(scope) {
        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        if(typeof scope == "undefined") {
            scope = $rootScope;
        }
        scope.privacy_policy = Application.privacy_policy;

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

        return $sbhttp({
            method: 'POST',
            url: Url.get("customer/mobile_account_login/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.saveCredentials(data.token);

            factory.can_access_locked_features = data.can_access_locked_features;
            factory.id = data.customer_id;
            factory.flushData();
        });
    };

    factory.loginWithFacebook = function(token) {
        var data = {
            device_id: device.uuid,
            token: token
        };

        return $sbhttp({
            method: 'POST',
            url: Url.get("customer/mobile_account_login/loginwithfacebook"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.saveCredentials(data.token);

            factory.can_access_locked_features = data.can_access_locked_features;
            factory.id = data.customer_id;
            factory.flushData();
        });
    };

    factory.register = function(data) {
        data.device_uid = device.uuid;

        return $sbhttp({
            method: 'POST',
            url: Url.get("customer/mobile_account_register/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.saveCredentials(data.token);

            factory.can_access_locked_features = data.can_access_locked_features;
            factory.id = data.customer_id;
            factory.flushData();
        });
    };

    factory.getAvatarUrl = function(customer_id, options) {
        options = angular.isObject(options) ? options : {};
        var url = Url.get("/customer/mobile_account/avatar", angular.extend({}, options, {customer: customer_id})) + ($rootScope.isOffline ? "" : "?" +(+new Date()));
        return url;
    };

    factory.save = function(data) {

        if(!factory.isLoggedIn()) {
            return factory.register(data);
        }

        return $sbhttp({
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

        return $sbhttp({
            method: 'POST',
            url: Url.get("customer/mobile_account_forgottenpassword/post"),
            data: {email: email},
            responseType:'json'
        });
    };

    factory.logout = function() {

        return $sbhttp({
            method: 'GET',
            url: Url.get("customer/mobile_account_login/logout"),
            responseType:'json'
        }).success(function() {
            factory.clearCredentials();

            factory.can_access_locked_features = false;
            factory.id = null;
            factory.flushData();
        });
    };

    factory.removeCard = function() {

        return $sbhttp({
            method: 'POST',
            url: Url.get("mcommerce/mobile_sales_stripe/removecard"),
            data: {customer_id: factory.id},
            responseType:'json'
        });
    };

    factory.find = function() {
        return $sbhttp({
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
