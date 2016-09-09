"use strict";

/**
 * FacebookConnect for users (login)
 */
App.service('FacebookConnect', function($cordovaOauth, $rootScope, $timeout, $window, Application, Customer, AUTH_EVENTS) {

    var self = this;

    self.app_id = null;
    self.version = "v2.7";
    self.is_initialized = false;
    self.is_logged_in = false;
    self.access_token = null;
    self.permissions = null;
    self.fb_login = null;

    self.login = function() {
        $cordovaOauth.facebook(self.app_id, self.permissions).then(function(result) {
            Customer.loginWithFacebook(result.access_token);
            Customer.modal.hide();
        }, function(error) {
            Customer.modal.hide();
        });
    };

    self.logout = function () {
        self.is_logged_in = false;
        self.access_token = null;
    };

    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function () {
        $timeout(function () {
            self.logout();
        });
    });

    return self;
});