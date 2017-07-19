/*global
    App, angular, ionic, device
 */

/**
 * Customer
 *
 * @author Xtraball SAS
 *
 * @todo remove dupes all over the code
 */
angular.module("starter").factory("Customer", function($sbhttp, $pwaRequest, $rootScope, $session, $timeout, $injector,
                                                       Loader, Modal, Dialog, Url, SB) {

    var factory = {
        events                          : [],
        customer                        : null,
        modal                           : null,
        login_modal                     : null,
        can_access_locked_features      : false,
        display_account_form            : false,
        login_modal_hidden_subscriber   : null,
        is_logged_in                    : false,
        facebook_login_enabled          : false
    };

    /**
     * Populate Application service on load
     *
     * @param data
     */
    factory.populate = function(customer) {
        factory.customer                       = customer;
        factory.is_logged_in                   = customer.is_logged_in;
        factory.id                             = customer.id;
        factory.can_access_locked_features     = customer.can_access_locked_features;
        factory.can_connect_with_facebook      = customer.can_connect_with_facebook;

        if(factory.is_logged_in) {
            $rootScope.$broadcast(SB.EVENTS.AUTH.loginSuccess);
        }

        factory.saveCredentials(customer.token);
    };

    /**
     * Disable facebook login if no API is set.
     *
     * @param facebook
     */
    factory.setFacebookLogin = function(facebook) {
        factory.facebook_login_enabled = !(facebook.id === null || facebook.id === "");
    };

    /**
     * @deprecated
     * @param id
     * @param urls
     */
    factory.onStatusChange = function(id, urls) {
        factory.events[id] = urls;
    };

    /**
     *
     */
    factory.hideModal = function() {
        factory.login_modal.hide();
    };

    /**
     * This is the general method to open a login modal, this should be the only one.
     *
     * @param scope
     * @param login_callback
     * @param logout_callback
     * @param register_callback
     */
    factory.loginModal = function(scope, login_callback, logout_callback, register_callback) {

        if($rootScope.isNotAvailableOffline()) {
            return;
        }

        if(scope === undefined) {
            scope = $rootScope;
        }

        scope.card_design = false;

        scope.$on("modal.shown", function() {

            var loginSuccessSubscriber = scope.$on(SB.EVENTS.AUTH.loginSuccess, function() {
                if(typeof login_callback === "function") {
                    login_callback();
                }

                $timeout(function() {
                    factory.login_modal.hide();
                }, 600);

            });

            var logoutSuccessSubscriber = scope.$on(SB.EVENTS.AUTH.logoutSuccess, function() {
                if(typeof logout_callback === "function") {
                    logout_callback();
                }

                $timeout(function() {
                    factory.login_modal.hide();
                }, 600);
            });

            var registerSubscriber = scope.$on(SB.EVENTS.AUTH.registerSuccess, function() {
                if(typeof register_callback === "function") {
                    register_callback();
                }

                $timeout(function() {
                    factory.login_modal.hide();
                }, 600);
            });

            /** Listening for modal.hidden dynamically */
            factory.login_modal_hidden_subscriber = scope.$on("modal.hidden", function() {

                /** Un-subscribe from modal.hidden RIGHT NOW, otherwise we will create a loop with the automated clean-up */
                factory.login_modal_hidden_subscriber();

                /** CLean-up callback listeners */
                loginSuccessSubscriber();
                logoutSuccessSubscriber();
                registerSubscriber();
            });
        });

        var login_promise = Modal
            .fromTemplateUrl("templates/customer/account/l1/login.html", {
                scope: angular.extend(scope, {
                    _pcustomer_close: function() {
                        factory.login_modal.hide();
                    },
                    _pcustomer_login: function(data) {
                        factory.login(data);
                    },
                    _pcustomer_logout: function() {
                        factory.logout();
                    },
                    _pcustomer_login_fb: function() {
                        factory.facebookConnect();
                    },
                    _pcustomer_check_update: function() {
                        $rootScope.checkForUpdate();
                    },
                    _pcustomer_register: function(data) {
                        factory.register(data);
                    },
                    _pcustomer_get_avatar: function() {
                        factory.getAvatarUrl();
                    },
                    _pcustomer_save: function() {
                        factory.save();
                    },
                    _pcustomer_forgotten_password: function() {
                        factory.forgottenpassword();
                    },
                    _pcustomer_remove_card: function() {
                        factory.removeCard();
                    },
                    _facebook_enabled: factory.facebook_login_enabled
                }),
                animation: 'slide-in-up'
            }).then(function(modal) {
                factory.login_modal = modal;
                factory.login_modal.show();

                return modal;
            });

        return login_promise;

    };


    factory.login = function(data) {
        angular.extend({}, data, {
            device_uid: $session.getDeviceUid()
        });

        Loader.show();

        var promise = $pwaRequest.post("customer/mobile_account_login/post", {
                data: data,
                cache: false
            });

        promise.then(function(data) {
            factory.populate(data.customer);

            return data;
        }, function(error) {

            Dialog.alert("Error", error.message, "OK", -1);

        }).then(function(data) {
            Loader.hide();

            return data;
        });

        return promise;
    };

    factory.facebookConnect = function() {
        if($rootScope.isNotAvailableInOverview()) {
            return;
        }
        var FacebookConnect = $injector.get('FacebookConnect');
        FacebookConnect.login();
    };

    factory.loginWithFacebook = function(token) {

        var data = {
            device_id: device.uuid,
            token: token
        };

        var promise = $pwaRequest.post("customer/mobile_account_login/loginwithfacebook", {
                data: data,
                cache: false
            });

        promise.then(function(data) {
                factory.populate(data.customer);

                return data;
            }, function(error) {

                Dialog.alert("Error", error.message, "OK", -1);

                return error;

            });

        return promise;
    };

    factory.register = function(data) {
        angular.extend({}, data, {
            device_uid: $session.getDeviceUid()
        });

        Loader.show();

        var promise = $pwaRequest.post("customer/mobile_account_register/post", {
                data: data,
                cache: false
            });

        promise.then(function(data) {
                factory.populate(data.customer);

                return data;
            }, function(error) {

                Dialog.alert("Error", error.message, "OK", -1);

                return error;

            }).then(function(data) {
                Loader.hide();

                return data;
            });

        return promise;
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

        Loader.show();

        var promise = $pwaRequest.post("customer/mobile_account_edit/post", {
                data: data,
                cache: false
            });

        promise.then(function(data) {

                factory.populate(data.customer);

                return data;
            }, function(error) {

                Dialog.alert("Error", error.message, "OK", -1);

                return error;

            }).then(function(data) {
                Loader.hide();

                return data;
            });

        return promise;
    };

    factory.forgottenpassword = function(email) {

        Loader.show();

        var promise = $pwaRequest.post("customer/mobile_account_forgottenpassword/post", {
                data: {
                    email: email
                },
                cache: false
            });

        promise.then(function(data) {
                Loader.hide();

                return data;
            });

        return promise;
    };

    factory.logout = function() {

        Loader.show();

        var promise = $pwaRequest.get("customer/mobile_account_login/logout", {
                cache: false
            });

        promise.then(function(result) {

                factory.clearCredentials();

                return result;
            }).then(function(result) {
                Loader.hide();

                return result;
            });

        return promise;
    };

    factory.removeCard = function() {

        Loader.show();

        var promise = $pwaRequest.post("mcommerce/mobile_sales_stripe/removecard", {
                data: {
                    customer_id: factory.id
                },
                cache: false
            });

        promise.then(function() {
                Loader.hide();
            });

        return promise;
    };

    factory.find = function() {
        return $pwaRequest.get("customer/mobile_account_edit/find");
    };

    factory.isLoggedIn = function() {
        return factory.is_logged_in;
    };

    factory.saveCredentials = function (token) {
        $session.setId(token);
    };

    factory.clearCredentials = function () {

        factory.customer = null;
        factory.can_access_locked_features = false;
        factory.is_logged_in = false;

        $rootScope.$broadcast(SB.EVENTS.AUTH.logoutSuccess);

        $session.clear();
    };

    return factory;
});
