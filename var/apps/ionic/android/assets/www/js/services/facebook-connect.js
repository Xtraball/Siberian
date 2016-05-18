"use strict";

/**
 * FacebookConnect for users (login)
 */
App.service('FacebookConnect', function($cordovaOauth, $rootScope, $timeout, $window, Application, Customer, AUTH_EVENTS) {

    var self = this;

    self.app_id = null;
    self.version = "v2.5";
    self.is_initialized = false;
    self.is_logged_in = false;
    self.access_token = null;
    self.permissions = null;
    self.fb_login = null;

    self.login = function() {
        if(Application.is_webview) {
            var scope = (self.permissions) ? self.permissions.join(",") : "";

            var redirect_uri = encodeURIComponent(DOMAIN+"/_redirect_fb_");
            var facebook_uri = "https://graph.facebook.com/oauth/authorize?client_id="+self.app_id+"&scope="+scope+"&response_type=token&redirect_uri="+redirect_uri;

            self.fb_login = $window.open(facebook_uri, $rootScope.getTargetForLink(), "location=no");

            self.fb_login.addEventListener("load", function(result) {
                self.check_result(result);
            });

            self.fb_login.addEventListener("onload", function(result) {
                self.check_result(result);
            });

            self.fb_login.addEventListener("exit", function(result) {
                self.fb_login = null; /** destroy the popup */
                Customer.modal.hide();
            });
        }
        else {
            $cordovaOauth.facebook(self.app_id, self.permissions).then(function(result) {
                Customer.loginWithFacebook(result.access_token);
                Customer.modal.hide();
            }, function(error) {
                Customer.modal.hide();
            });
        }
    };

    self.check_result = function(result) {
        var url = "";
        if(result && result.srcElement && result.srcElement.URL) {
            url = result.srcElement.URL;
        }
        else if(result && result.url && result.url.href) {
            url = result.url.href;
        }
        else {
            url = result.url;
        }

        if(url.indexOf("_redirect_fb_") != -1) {
            self.fb_login.close();
            self.fb_login = null; /** destroy the popup */
            /** Get the CODE to connect via server-to-server for an infinite access token. */
            var short_token = url.match(/access_token=(.*)&/);
            Customer.loginWithFacebook(short_token[1]);
            Customer.modal.hide();
        }
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