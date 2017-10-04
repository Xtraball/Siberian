/**
 * FacebookConnect for users (login)
 */
angular.module('starter').service('FacebookConnect', function ($cordovaOauth, $rootScope, $timeout, $window,
                                                              Customer, Dialog, SB, Loader) {
    var _this = this;

    _this.app_id = null;
    _this.version = 'v2.7';
    _this.is_initialized = false;
    _this.is_logged_in = false;
    _this.access_token = null;
    _this.permissions = null;
    _this.fb_login = null;

    _this.login = function () {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
            var scope = (_this.permissions) ? _this.permissions.join(',') : '',
                redirectUri = encodeURIComponent(DOMAIN + '/' + APP_KEY + '?login_fb=true');

            $window.location = 'https://graph.facebook.com/oauth/authorize?client_id=' +
                _this.app_id+'&scope=' + scope + '&response_type=token&redirect_uri=' + redirectUri;
        } else {
            Loader.show();
            $cordovaOauth.facebook(_this.app_id, _this.permissions)
                .then(function (result) {
                    Customer.loginWithFacebook(result.access_token)
                        .then(function () {
                            Customer.login_modal.hide();
                        }).finally(function () {
                            Loader.hide();
                        });
                }, function (error) {
                    Dialog.alert('Login error', error, 'OK', -1)
                        .then(function () {
                            Customer.login_modal.hide();
                            Loader.hide();
                        });
                });
        }
    };

    _this.logout = function () {
        _this.is_logged_in = false;
        _this.access_token = null;
    };

    $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $timeout(function () {
            _this.logout();
        });
    });

    return _this;
});
