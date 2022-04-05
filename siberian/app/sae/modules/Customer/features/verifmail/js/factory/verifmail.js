/**
 * Verifmail
 *
 * @version 1.0.0
 */
angular
    .module('starter')
    .factory('Verifmail', function (Application, Customer, Dialog, Loader,
                                    $session, $pwaRequest, $translate, $interval) {
        var factory = {
            cached_validations: {}
        };

        factory.init = function () {
            var deferred = $q.defer();
            return deferred.promise;
        };

        factory.sendEmail = function (email) {
            return $pwaRequest.post('/customer/mobile_verifmail/send', {
                data: {
                    email: email
                }
            });
        };

        factory.checkEmail = function (email) {
            return $pwaRequest.post('/customer/mobile_verifmail/live-check', {
                data: {
                    email: email
                }
            });
        };

        factory.onStart = function () {
            $session
                .getItem('verifmail_cached_validations')
                .then(function (value) {
                    if ((value !== null) && (value !== undefined)) {
                        factory.cached_validations = value;
                    }

                    Application.loaded.then(function () {
                        // Enable only if email validation is active!
                        if (Application.myAccount.settings.email_validation) {
                            Customer.registerHook('customer.before.register', function (payload, promise) {
                                try {
                                    if (factory.cached_validations.hasOwnProperty(payload.customer.email) &&
                                        factory.cached_validations[payload.customer.email] === true) {
                                        promise.resolve('valid');
                                        return;
                                    }

                                    if (Loader.isOpen()) {
                                        Loader.hide();
                                    }

                                    Dialog
                                        .confirm('Email validation', 'To continue we must validate your e-mail, we will send you an e-mail with an activation link!', ['YES', 'NO'], '', 'customer')
                                        .then(function (confirm) {
                                            if (confirm) {
                                                Loader.show($translate.instant('Sending e-mail...', 'customer'));

                                                var trText = $translate.instant('Waiting validation', 'customer');

                                                factory
                                                    .sendEmail(payload.customer.email)
                                                    .then(function (success) {

                                                        Loader.hide();
                                                        Dialog
                                                            .alert('Email', 'Please check your inbox and click on the link to confirm your address!', 'OK', -1, 'customer')
                                                            .then(function () {

                                                                Loader.show(null, {
                                                                    callbackFn: function () {
                                                                        // Cancel verification just in case we allow user to do so!
                                                                        Loader.hide();
                                                                        $interval.cancel(cancelVerify);
                                                                        promise.reject($translate.instant('You cancelled the e-mail verification!', 'customer'));
                                                                    },
                                                                    withTimeout: false,
                                                                    template: "<ion-spinner class=\"spinner-custom\"></ion-spinner><br /><span>" + $translate.instant(trText) + "</span>"
                                                                });

                                                                var start = Math.floor(Date.now()/1000) + 120;
                                                                var repeat = 0;
                                                                var inprogress = false;
                                                                var cancelVerify = $interval(function () {
                                                                    if (inprogress) {
                                                                        // Just skip if already in progress
                                                                        return;
                                                                    }
                                                                    inprogress = true;
                                                                    repeat++;
                                                                    factory
                                                                        .checkEmail(payload.customer.email)
                                                                        .then(function (success) {
                                                                                if (success.status === 'valid') {
                                                                                    // saving the currenct valid status in session
                                                                                    factory.cached_validations[payload.customer.email] = true;
                                                                                    // then in session
                                                                                    $session.setItem('verifmail_cached_validations', factory.cached_validations);

                                                                                    promise.resolve('valid');
                                                                                    $interval.cancel(cancelVerify);
                                                                                }
                                                                                // else continue up to 60 seconds

                                                                                inprogress = false;
                                                                            },
                                                                            function (error) {
                                                                                promise.reject($translate.instant(error.message, 'customer'));
                                                                                $interval.cancel(cancelVerify);

                                                                                inprogress = false;
                                                                            });

                                                                    // We waited 2 minutes...
                                                                    if (Math.floor(Date.now()/1000) > start) {
                                                                        promise.reject($translate.instant('Request timed out, please try again!', 'customer'));
                                                                        $interval.cancel(cancelVerify);
                                                                    }
                                                                }, 2500)
                                                            });
                                                    }, function (error) {
                                                        Loader.hide();
                                                        promise.reject($translate.instant(error.message, 'customer'));
                                                    });

                                            } else {
                                                // We failed, so we just revert to the previous state!
                                                Loader.hide();
                                                Dialog
                                                    .alert('Error', 'You must validate your email in order to register!', 'OK', -1, 'customer')
                                                    .then(function () {
                                                        promise.reject();
                                                    });
                                            }
                                        });
                                } catch (e) {
                                    // We just place it here to re-throw, because errors are glopped inside promises.
                                    promise.reject($translate.instant(e.message, 'customer'));
                                }
                            })
                        }
                    });
                });
        };

        return factory;
    });