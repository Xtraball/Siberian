/**
 * Customer
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.14
 */
angular
    .module('starter')
    .factory('Customer', function ($pwaRequest, $rootScope, $session, $timeout, $injector, Application, Loader,
                                   Modal, Dialog, Url, SB) {
    var factory = {
        customer: null,
        modal: null,
        login_modal: null,
        can_access_locked_features: false,
        display_account_form: false,
        login_modal_hidden_subscriber: null,
        is_logged_in: false,
        facebook_login_enabled: false,
        loginScope: null
    };

    /**
     * Populate Application service on load
     *
     * @param customer
     */
    factory.populate = function (customer) {
        factory.customer = customer;
        factory.is_logged_in = customer.isLoggedIn;
        factory.id = customer.id;
        factory.can_access_locked_features = customer.can_access_locked_features;
        factory.can_connect_with_facebook = customer.can_connect_with_facebook;

        if (factory.is_logged_in) {
            $rootScope.$broadcast(SB.EVENTS.AUTH.loginSuccess);
        }

        factory.saveCredentials(customer.token);
    };

    /**
     * Disable facebook login if no API is set.
     *
     * @param facebook
     */
    factory.setFacebookLogin = function (facebook) {
        factory.facebook_login_enabled = !(facebook.id === null || facebook.id === '');
    };

    /**
     *
     */
    factory.hideModal = function () {
        factory.login_modal.hide();
    };

    /**
     * This is the general method to open a login modal, this should be the only one.
     *
     * @param scope
     * @param loginCallback
     * @param logoutCallback
     * @param registerCallback
     *
     * @return Promise|boolean
     */
    factory.loginModal = function (scope, loginCallback, logoutCallback, registerCallback) {
        if ($rootScope.isNotAvailableOffline()) {
            return false;
        }

        var modalScope = $rootScope.$new(true);

        modalScope.$on('modal.shown', function () {
            var loginSuccessSubscriber = modalScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
                if (typeof loginCallback === 'function') {
                    loginCallback();
                }
                factory.login_modal.hide();
            });

            var logoutSuccessSubscriber = modalScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
                if (typeof logoutCallback === 'function') {
                    logoutCallback();
                }
                factory.login_modal.hide();
            });

            var registerSubscriber = modalScope.$on(SB.EVENTS.AUTH.registerSuccess, function () {
                if (typeof registerCallback === 'function') {
                    registerCallback();
                }
                factory.login_modal.hide();
            });

            // Listening for modal.hidden dynamically!
            factory.login_modal_hidden_subscriber = modalScope.$on('modal.hidden', function () {
                // Un-subscribe from modal.hidden RIGHT NOW, otherwise we will create a loop with the automated clean-up!
                factory.login_modal_hidden_subscriber();

                // CLean-up callback listeners!
                loginSuccessSubscriber();
                logoutSuccessSubscriber();
                registerSubscriber();
            });
        });

        // Layout options!
        var layout = 'templates/customer/account/l1/my-account.html';

        return Modal
            .fromTemplateUrl(layout, {
                scope: modalScope,
                animation: 'slide-in-up'
            }).then(function (modal) {
                factory.login_modal = modal;
                factory.login_modal.show();

                return modal;
            });
    };

    factory.closeModal = function () {
        factory.login_modal.hide();
    };

    factory.login = function (data) {
        var localData = angular.extend({}, data, {
            device_uid: $session.getDeviceUid()
        });

        Loader.show();

        var promise = $pwaRequest.post('customer/mobile_account_login/post', {
            data: localData,
            cache: false
        });

        promise
            .then(function (result) {
                factory.populate(result.customer);

                return result;
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK', -1);
            }).then(function (result) {
                Loader.hide();

                return result;
            });

        return promise;
    };

    factory.loginWithFacebook = function (token) {
        var data = {
            device_uid: device.uuid,
            token: token
        };

        var promise = $pwaRequest.post('customer/mobile_account_login/loginwithfacebook', {
            data: data,
            cache: false
        });

        promise
            .then(function (result) {
                factory.populate(result.customer);

                return result;
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK', -1);

                return error;
            });

        return promise;
    };

    factory.register = function (data) {
        var localData = angular.extend({}, data, {
            device_uid: $session.getDeviceUid()
        });

        Loader.show();

        var promise = $pwaRequest.post('customer/mobile_account_register/post', {
            data: localData,
            cache: false
        });

        promise
            .then(function (result) {
                factory.populate(result.customer);

                return result;
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK', -1);

                return error;
            }).then(function (result) {
                Loader.hide();

                return result;
            });

        return promise;
    };

    factory.forgotPassword = function (email) {
        try {
            Loader.show();
            factory.forgottenpassword(email)
                .then(function (data) {
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert('', data.message, 'OK', -1);

                        if (data.success) {
                            $rootScope.$broadcast('displayLogin');
                        }
                    }
                }, function (data) {
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert('Error', data.message, 'OK', -1);
                    }
                }).then(function () {
                    Loader.hide();
                });
        } catch (e) {
            Loader.hide();
        }
    };

    factory.getAvatarUrl = function (customerId, options) {
        var myOptions = angular.isObject(options) ? options : {};
        return Url.get(
            '/customer/mobile_account/avatar', angular.extend({}, myOptions, { customer: customerId })) +
            ($rootScope.isOffline ? '' : '?' +(+new Date()));
    };

    factory.save = function (data) {
        if (!factory.isLoggedIn()) {
            return factory.register(data);
        }

        Loader.show();

        var promise = $pwaRequest.post('customer/mobile_account_edit/post', {
            data: data,
            cache: false
        });

        promise
            .then(function (result) {
                factory.populate(result.customer);

                return result;
            }, function (error) {
                Dialog.alert('Error', error.message, 'OK', -1);

                return error;
            }).then(function (result) {
                Loader.hide();

                return result;
            });

        return promise;
    };

    factory.forgottenpassword = function (email) {
        Loader.show();

        var promise = $pwaRequest.post('customer/mobile_account_forgottenpassword/post', {
                data: {
                    email: email
                },
                cache: false
            });

        promise.then(function (data) {
                Loader.hide();

                return data;
            });

        return promise;
    };

    factory.logout = function () {
        Loader.show();

        var promise = $pwaRequest.get('customer/mobile_account_login/logout', {
            cache: false
        });

        promise.then(function (result) {
            factory.clearCredentials();

            return result;
        }).then(function (result) {
            Loader.hide();

            return result;
        });

        return promise;
    };

    factory.removeCard = function () {
        Loader.show();

        var promise = $pwaRequest.post('mcommerce/mobile_sales_stripe/removecard', {
                data: {
                    customer_id: factory.id
                },
                cache: false
            });

        promise.then(function () {
                Loader.hide();
            });

        return promise;
    };

    factory.find = function () {
        return $pwaRequest.get('customer/mobile_account_edit/find');
    };

    factory.isLoggedIn = function () {
        return factory.is_logged_in;
    };

    /**
     * Request a new token for GDPR Data
     */
    factory.requestToken = function () {
        Loader.show();

        var promise = $pwaRequest.post('customer/mobile_account/request-token', {
            cache: false
        });

        promise
            .then(function (data) {
                if (angular.isDefined(data.message)) {
                    Dialog.alert('', data.message, 'OK', -1);
                }

                return data;
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK', -1);
                }

                return data;
            }).then(function () {
                Loader.hide();
            });

        return promise;
    };

    factory.saveCredentials = function (uuid) {
        $session.setId(uuid);
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
