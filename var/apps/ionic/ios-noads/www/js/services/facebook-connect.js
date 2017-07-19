/*global
 angular, APP_KEY, DEVICE_TYPE, DOMAIN
 */

/**
 * FacebookConnect for users (login)
 */
angular.module("starter").service('FacebookConnect', function($cordovaOauth, $rootScope, $timeout, $window,
                                                              Customer, Dialog, SB) {

    var self = this;

    self.app_id = null;
    self.version = "v2.7";
    self.is_initialized = false;
    self.is_logged_in = false;
    self.access_token = null;
    self.permissions = null;
    self.fb_login = null;

    self.login = function() {
        if($rootScope.isNotAvailableInOverview()) {
            return;
        }

        if(DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
            var scope = (self.permissions) ? self.permissions.join(",") : "",
                redirect_uri = encodeURIComponent(DOMAIN + "/" + APP_KEY + "?login_fb=true"),
                facebook_uri = "https://graph.facebook.com/oauth/authorize?client_id=" +
                    self.app_id+"&scope=" + scope + "&response_type=token&redirect_uri=" + redirect_uri;

            $window.location = facebook_uri;
        } else {
            $cordovaOauth.facebook(self.app_id, self.permissions)
                .then(function(result) {
                    Customer.loginWithFacebook(result.access_token)
                        .then(function() {
                            Customer.login_modal.hide();
                        });
                }, function(error) {
                    Dialog.alert("Login error", error, "OK", -1)
                        .then(function() {
                            Customer.login_modal.hide();
                        });
                });
        }

    };

    self.logout = function () {
        self.is_logged_in = false;
        self.access_token = null;
    };

    $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $timeout(function () {
            self.logout();
        });
    });

    return self;
});