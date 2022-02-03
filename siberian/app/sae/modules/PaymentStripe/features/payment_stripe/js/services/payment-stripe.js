/**
 * PaymentStripe service
 */
angular
    .module('starter')
    .service('PaymentStripe', function (Application, Loader, Dialog, $rootScope, $injector, $translate, $pwaRequest, $q) {
        var service = {
            card: null,
            elements: null,
            stripe: null,
            settings: null,
            isReadyPromise: $q.defer(),
            publishableKey: null,
            setupIntent: null,
            paymentId: null
        };

        service.onStart = function () {
            if (typeof Stripe === 'undefined') {
                var stripeJS = document.createElement('script');
                stripeJS.type = 'text/javascript';
                stripeJS.src = 'https://js.stripe.com/v3/';
                stripeJS.onload = function () {
                    service.isReadyPromise.resolve(Stripe);

                    // When Stripe is ready, we can load the key!
                    service
                        .fetchSettings()
                        .then(function (payload) {
                            service.settings = payload.settings;

                            service.setPublishableKey(service.settings.publishable_key);
                        }, function (error) {
                            //
                            console.error(error.message);
                        });
                };
                document.body.appendChild(stripeJS);
            } else {
                service.isReadyPromise.resolve(Stripe);
            }
        };

        service.isReady = function () {
            // Force rejection if publishable key is missing!
            if (!angular.isDefined(service.publishableKey)) {
                return $q.reject($translate.instant("Stripe publishable key is required."));
            }

            return service.isReadyPromise.promise;
        };

        service.setPublishableKey = function (publishableKey) {
            var deferred = $q.defer();

            if (publishableKey !== undefined &&
                publishableKey.length <= 0) {
                deferred.reject("publishableKey is required.");
                throw new Error("publishableKey is required.");
            }

            // Creating a new instance of the Stripe service!
            if (service.publishableKey !== publishableKey ||
                !service.stripe) {
                service.publishableKey = publishableKey;

                try {
                    service.stripe = Stripe(service.publishableKey);
                } catch (e) {
                    // Silent!
                    console.warn('[Stripe]: ' + e.message);
                }

                try {
                    service.card.destroy();
                } catch (e) {
                    // Silent!
                }
            }

            // Ensute stripe instance exists!
            if (!service.stripe) {
                try {
                    service.stripe = Stripe(service.publishableKey);
                    service.card.destroy();
                } catch (e) {
                    // Silent!
                }
            }

            deferred.resolve(service.publishableKey);

            return deferred.promise;
        };

        service.initCardForm = function () {
            return service
                .isReady()
                .then(function () {
                    var cardElementParent = document.getElementById('card_element');
                    try {
                        cardElementParent.firstChild.remove();
                    } catch (e) {
                        // Silent!
                    }

                    service.elements = service.stripe.elements();
                    var style = {
                        base: {
                            color: "#32325d",
                            fontFamily: "'Helvetica Neue', Helvetica, sans-serif",
                            fontSmoothing: "antialiased",
                            fontSize: "16px",
                            "::placeholder": {
                                color: "#aab7c4"
                            }
                        },
                        invalid: {
                            color: "#fa755a",
                            iconColor: "#fa755a"
                        }
                    };

                    service.card = service.elements.create('card', {
                        hidePostalCode: true,
                        style: style
                    });

                    var saveElement = document.getElementById('save_element');
                    var displayError = document.getElementById('card_errors');
                    var displayErrorParent = document.getElementById('card_errors_parent');

                    saveElement.setAttribute('disabled', 'disabled');

                    service.card.removeEventListener('change');
                    service.card.addEventListener('change', function (event) {
                        if (!event.complete &&
                            (event.error !== undefined)) {
                            displayErrorParent.classList.remove('ng-hide');
                            try {
                                displayError.textContent = event.error.message;
                            } catch (e) {
                                console.log('unknown error: ', event);
                            }
                            saveElement.setAttribute('disabled', 'disabled');
                        } else {
                            displayErrorParent.classList.add('ng-hide');
                            displayError.textContent = '';
                            saveElement.removeAttribute('disabled');
                        }
                    });

                    service.card.mount("#card_element");
                });
        };


        service.handleCardAuthorization = function (card, options) {
            var deferred = $q.defer();

            try {
                var authorizeMessage = options.labels.authorizeLoaderMessage ?
                    options.labels.authorizeLoaderMessage :
                    $translate.instant('Authorizing payment...', 'payment_stripe');

                Loader.show(authorizeMessage);

                service
                    .fetchPaymentIntent(card, options)
                    .then(function (fpiSuccess) {

                        // Saving the current paymentId!
                        service.paymentId = fpiSuccess.paymentId;

                        // If requires "requires_confirmation" or "requires_action"

                        console.log('[REMOVE ME]', fpiSuccess, ['requires_confirmation', 'requires_action'].indexOf(fpiSuccess.pi_status));

                        if (['requires_confirmation', 'requires_action'].indexOf(fpiSuccess.pi_status) !== -1) {
                            service
                                .stripe
                                .confirmCardPayment(fpiSuccess.client_secret)
                                .then(function (ccpResponse) {
                                    if (ccpResponse.error) {
                                        Loader.hide();
                                        Dialog.alert('Error', ccpResponse.error.message, 'OK', -1, 'payment_stripe');

                                        service
                                            .authorizationError(fpiSuccess.pi_id, ccpResponse.error)
                                            .then(function (aeSuccess) {
                                                deferred.reject(aeSuccess);
                                            }, function (aeError) {
                                                deferred.reject(aeError);
                                            });

                                        return;
                                    }
                                    if (ccpResponse.paymentIntent &&
                                        ccpResponse.paymentIntent.status === 'requires_capture') {
                                        // Continue to save card infos!
                                        service
                                            .authorizationSuccess(fpiSuccess.pi_id)
                                            .then(function (asSuccess) {
                                                Loader.hide();
                                                deferred.resolve(asSuccess);
                                            }, function (asError) {
                                                Loader.hide();
                                                deferred.resolve(asError);
                                            });
                                    }
                                });
                        } else {
                            // Continue to save card infos!
                            service
                                .authorizationSuccess(fpiSuccess.pi_id)
                                .then(function (asSuccess) {
                                    Loader.hide();
                                    deferred.resolve(asSuccess);
                                }, function (asError) {
                                    Loader.hide();
                                    deferred.resolve(asError);
                                });
                        }

                    }, function (fpiError) {
                        Loader.hide();
                        Dialog.alert('Error', fpiError.message, 'OK', -1, 'payment_stripe');

                        deferred.reject(fpiError);
                    });

            } catch (tcError) {
                console.log('[tcError]', tcError);
                deferred.reject(tcError);
            }

            return deferred.promise;
        };

        service.authorizationError = function (paymentIntentId, error) {
            return $pwaRequest.post('/paymentstripe/mobile_handler/authorization-error',
                {
                    data: {
                        paymentIntentId: paymentIntentId,
                        error: error
                    }
                });
        };

        service.authorizationSuccess = function (paymentIntentId) {
            return $pwaRequest.post('/paymentstripe/mobile_handler/authorization-success',
                {
                    data: {
                        paymentIntentId: paymentIntentId
                    }
                });
        };

        service.handleCardPayment = function (card, options) {
            var deferred = $q.defer();

            try {

                var payMessage = options.labels.payLoaderMessage ?
                    options.labels.payLoaderMessage :
                    $translate.instant('Processing payment...', 'payment_stripe');

                Loader.show(payMessage);

                var displayError = document.getElementById('card_errors');
                var displayErrorParent = document.getElementById('card_errors_parent');

                service
                    .stripe
                    .handleCardPayment(service.card)
                    .then(function (result) {
                        if (result.error) {
                            // Inform the customer that there was an error.
                            displayErrorParent.classList.remove('ng-hide');
                            displayError.textContent = $translate.instant(result.error.message);

                            service
                                .paymentError(result.error.message)
                                .then(function (payload) {
                                    deferred.reject(payload);
                                });
                        } else {
                            // Sending the success token!
                            displayErrorParent.classList.add('ng-hide');
                            displayError.textContent = '';

                            service
                                .paymentSuccess(result)
                                .then(function (payload) {
                                    deferred.reject(payload);
                                });
                        }
                    });
            } catch (e) {
                service
                    .paymentError(e.message)
                    .then(function (payload) {
                        deferred.reject(payload);
                    });
            }

            return deferred.promise;
        };

        service.paymentError = function (message) {
            return $pwaRequest.post("/paymentstripe/mobile_handler/payment-error",
                {
                    data: {
                        message: message
                    }
                });
        };

        service.paymentSuccess = function (payload) {
            return $pwaRequest.post("/paymentstripe/mobile_handler/payment-success",
                {
                    data: {
                        payload: payload
                    }
                });
        };

        service.handleCardSetup = function () {
            var deferred = $q.defer();

            try {
                Loader.show($translate.instant('Verifying information...', 'payment_stripe'));

                var displayError = document.getElementById('card_errors');
                var displayErrorParent = document.getElementById('card_errors_parent');

                service
                    .fetchSetupIntent()
                    .then(function (payload) {
                        service.setupIntent = payload.setupIntent;
                        service
                            .stripe
                            .confirmCardSetup(service.setupIntent.client_secret, {
                                payment_method: {
                                    card: service.card
                                },
                            })
                            .then(function (result) {
                                Loader.hide();
                                service.card.clear();
                                if (result.error) {
                                    displayErrorParent.classList.remove('ng-hide');
                                    displayError.textContent = $translate.instant(result.error.message);

                                    deferred.reject(result.error.message);

                                    return;
                                }
                                if (result.setupIntent &&
                                    result.setupIntent.status === 'succeeded') {
                                    displayErrorParent.classList.add('ng-hide');
                                    displayError.textContent = '';

                                    // Continue to save card infos!
                                    Loader.show();
                                    service
                                        .setupSuccess(result)
                                        .then(function (success) {
                                            deferred.resolve();
                                        }, function (error) {
                                            deferred.reject(error);
                                        })
                                }
                            });
                    })

            } catch (e) {
                console.log('error', e);
            }

            return deferred.promise;
        };

        service.setupError = function (message) {
            return $pwaRequest.post('/paymentstripe/mobile_handler/setup-error',
                {
                    data: {
                        message: message
                    }
                });
        };

        service.setupSuccess = function (payload) {
            return $pwaRequest.post('/paymentstripe/mobile_handler/setup-success',
                {
                    data: {
                        payload: payload
                    }
                });
        };

        service.deletePaymentMethod = function (card) {
            return $pwaRequest.post('/paymentstripe/mobile_cards/delete-payment-method',
                {
                    data: {
                        card: card
                    }
                });
        };

        service.fetchSettings = function () {
            return $pwaRequest.post('/paymentstripe/mobile_cards/fetch-settings');
        };

        service.fetchVaults = function () {
            return $pwaRequest.post('/paymentstripe/mobile_cards/fetch-vaults');
        };

        service.fetchSetupIntent = function () {
            return $pwaRequest.post('/paymentstripe/mobile_cards/fetch-setup-intent');
        };

        service.fetchPaymentIntent = function (card, options) {
            return $pwaRequest.post('/paymentstripe/mobile_cards/fetch-payment-intent',
                {
                    data: {
                        card: card,
                        options: options
                    }
                });
        };

        service.clearForm = function () {
            // Clear form on success!
            service.card.clear();
            service.card.blur();
        };

        return service;
    });
