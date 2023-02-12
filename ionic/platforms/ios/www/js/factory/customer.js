/**
 * Customer
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.20.30
 */
angular
    .module('starter')
    .factory('Customer', function ($pwaRequest, $rootScope, $session, $timeout, $injector, $translate, $q,
                                   Application, Loader, Modal, Dialog, Url, SB, TelInput) {

        var factory = {
            customer: null,
            modal: null,
            login_modal: null,
            can_access_locked_features: false,
            display_account_form: false,
            login_modal_hidden_subscriber: null,
            is_logged_in: false,
            loginScope: null,
            itis: {}
        };

        factory.uuidv4 = function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        };

        /**
         *
         * @param customer
         */
        factory.populate = function (customer) {
            factory.customer = customer;
            factory.is_logged_in = customer.isLoggedIn;
            factory.id = customer.id;
            factory.can_access_locked_features = customer.can_access_locked_features;

            if (factory.is_logged_in) {
                $rootScope.$broadcast(SB.EVENTS.AUTH.loginSuccess);
            }

            factory.saveCredentials(customer.token);
        };

        /**
         *
         */
        factory.hideModal = function () {
            factory.login_modal.hide();
        };

        factory.getAvatarUrl = function () {
            // Fallback for previous modules!
            if (factory.customer &&
                factory.customer.image &&
                factory.customer.image.length > 0) {
                return IMAGE_URL + 'images/customer' + factory.customer.image;
            }
            return 'data:image/jpeg;base64,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';
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
            TelInput.isLoaded().then(function () {
                if ($rootScope.isNotAvailableOffline()) {
                    return false;
                }

                // Event handlers to spread accross the application!
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
            });
        };

        // Binder to close the login modal!
        factory.closeModal = function () {
            factory.login_modal.hide();
            factory.login_modal.remove();
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

        factory.register = function (customer) {
            var localCustomer = angular.extend({}, customer, {
                device_uid: $session.getDeviceUid()
            });

            var promise = $pwaRequest.post('customer/mobile_account_register/post', {
                data: localCustomer,
                cache: false
            });

            promise
                .then(function (success) {
                    factory.populate(success.customer);

                    return success;
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

        factory.save = function (customer) {
            if (!factory.isLoggedIn()) {
                return factory.register(customer);
            }

            var promise = $pwaRequest.post('customer/mobile_account_edit/post', {
                data: customer,
                cache: false
            });

            // Handling success, to update the customer object!
            promise
                .then(function (success) {
                    factory.populate(success.customer);

                    return success;
                });

            return promise;
        };

        factory.saveSettings = function (settings) {
            var localSettings = settings;

            localSettings.deviceType = DEVICE_TYPE;
            localSettings.deviceUid = $session.getDeviceUid();

            return $pwaRequest.post('customer/mobile_account_edit/save-settings', {
                data: localSettings,
                cache: false
            });
        };

        factory.sendTestPush = function (playerId) {
            return $pwaRequest.post('push2/mobile_player/test-push', {
                data: {
                    playerId: playerId
                },
                cache: false
            });
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
            Loader.show($translate.instant('Signing out...', 'customer'));

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

        factory.initIti = function (selector, country, options) {
            var localCountry;
            // Double tap, just in case
            try {
                localCountry = country.toLowerCase();
            } catch (e) {
                localCountry = CURRENT_LANGUAGE;
            }

            if (intlTelInput_countries.indexOf(localCountry) === -1) {
                localCountry = "gb";
            }

            var localOptions = (typeof options === 'undefined') ? {} : options;
            var itiOptions = angular.extend({}, {
                allowDropdown: true,
                nationalMode: true,
                formatOnDisplay: true,
                placeholderNumberType: 'MOBILE',
                separateDialCode: false,
                initialCountry: localCountry,
                autoPlaceholder: 'off',
                dropdownContainer: document.body
            }, localOptions);

            var input = document.getElementById(selector);
            if (factory.itis.hasOwnProperty(selector)) {
                factory.itis[selector].destroy();
                delete factory.itis[selector];
            }

            factory.itis[selector] = window.intlTelInput(input, itiOptions);
        };

        factory.getIti = function (selector) {
            if (factory.itis.hasOwnProperty(selector)) {
                return factory.itis[selector];
            }
            return null;
        };

        factory.find = function () {
            return $pwaRequest.get('customer/mobile_account_edit/find');
        };

        factory.deleteAccount = function () {
            return $pwaRequest.get('customer/mobile_account_login/delete-account');
        };

        factory.getIpInfo = function () {
            var defer = $q.defer();
            function ipSsuccess () {
                defer.resolve(JSON.parse(this.responseText));
            }
            function ipFail () {
                defer.reject();
            }

            var token = (Application.ipinfo_key.length) ?
                '?token=' + Application.ipinfo_key : '';

            var oReq = new XMLHttpRequest();
            oReq.onload = ipSsuccess;
            oReq.onerror = ipFail;
            oReq.open('get', 'https://ipinfo.io/json' + token, true);
            oReq.send();

            return defer.promise;
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

        // Hooks key-array
        factory.hooks = {
            'customer.before.login': [],
            'customer.after.login': [],
            'customer.before.update': [],
            'customer.after.update': [],
            'customer.before.register': [],
            'customer.after.register': []
        };

        factory.registerHook = function (key, callback) {
            if (!factory.hooks.hasOwnProperty(key)) {
                console.error('[Customer.addHook] invalid hook key.');
                return;
            }

            if (typeof callback !== 'function') {
                console.error('[Customer.addHook] callback must be a function.');
                return;
            }

            factory.hooks[key].push(callback);
        };

        factory.nextHook = function (payload, _hooks, deferred) {
            if (_hooks.length === 0) {
                deferred.resolve('All done!');
                return;
            }
            var _currentHook = _hooks.shift();
            var _tmpQ = $q.defer();
            var _untouchedPayload = angular.copy(payload);
            try {
                _currentHook(payload, _tmpQ);
                _tmpQ.promise.then(function (success) {
                    // Continue
                    factory.nextHook(payload, _hooks, deferred);
                }, function (error) {
                    deferred.reject('An error occured please try again!<br />' + error.toString());
                })
            } catch (e) {
                // We also revert the payload to before
                payload = _untouchedPayload;
                // Something went wrong with the hook
                deferred.reject('An unknown error occured please try again!');
            }
        };

        factory.runHooks = function (hooksList, payload) {
            var deferred = $q.defer();
            var _hooks = [];

            for (var key in hooksList) {
                var index = hooksList[key];
                if (factory.hooks.hasOwnProperty(index)) {
                    _hooks = _hooks.concat(angular.copy(factory.hooks[index]));
                }
            }

            // Sorting hooks by priority
            _hooks.sort(function (a, b) {
                if (a.hasOwnProperty('priority') && b.hasOwnProperty('priority')) {
                    return a['priority'] - b['priority'];
                }
                return 0;
            });

            factory.nextHook(payload, _hooks, deferred);

            return deferred.promise;
        };

        /**
         * Clears out the customer object!
         * @returns {*|$scope.customer|Customer.customer|null}
         */
        factory.pristineCustomer = function () {
            factory.is_logged_in = false;
            factory.customer.id = '';
            factory.customer.civility = '';
            factory.customer.firstname = '';
            factory.customer.lastname = '';
            factory.customer.nickname = '';
            factory.customer.email = '';
            factory.customer.image = '';
            factory.customer.mobile = '';
            factory.customer.intl_mobile = '';
            factory.customer.birthdate = '';
            factory.customer.change_password = false;
            factory.customer.password = '';
            factory.customer.repeat_password = '';
            factory.customer.privacy_policy = false;
            factory.customer.is_custom_image = false;
            factory.customer.show_in_social_gaming = false;
            factory.customer.metadatas = {};
            factory.customer.communication_agreement = false;
            factory.customer.is_logged_in = false;
            factory.customer.isLoggedIn = false;
            factory.customer.can_access_locked_features = false;
            factory.customer.extendedFields = factory.customer.extendedFieldsPristine;

            return factory.customer;
        };

        factory.clearCredentials = function () {
            factory.pristineCustomer();

            $rootScope.$broadcast(SB.EVENTS.AUTH.logoutSuccess);

            $session.clear();
        };

        return factory
    });
