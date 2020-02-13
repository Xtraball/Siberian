/**
 * PaymentStripe service
 */
angular
.module("starter")
.service("PaymentStripe", function (Application, $rootScope, $injector, $translate, $pwaRequest, $q) {
    var service = {
        card: null,
        stripe: null,
        settings: null,
        isReadyPromise: $q.defer(),
        publishableKey: null
    };

    service.onStart = function () {
        if (typeof Stripe === "undefined") {
            var stripeJS = document.createElement("script");
            stripeJS.type = "text/javascript";
            stripeJS.src = "https://js.stripe.com/v3/";
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
            var cardElementParent = document.getElementById("card_element");
            try {
                cardElementParent.firstChild.remove();
            } catch (e) {
                // Silent!
            }

            var elements = service.stripe.elements();
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

            service.card = elements.create("card", {
                hidePostalCode: true,
                style: style
            });

            var saveElement = document.getElementById("save_element");
            var displayError = document.getElementById("card_errors");
            var displayErrorParent = document.getElementById("card_errors_parent");

            saveElement.setAttribute("disabled", "disabled");

            service.card.removeEventListener("change");
            service.card.addEventListener("change", function (event) {
                if (event.error) {
                    displayErrorParent.classList.remove("ng-hide");
                    displayError.textContent = event.error.message;
                    saveElement.setAttribute("disabled", "disabled");
                } else {
                    displayErrorParent.classList.add("ng-hide");
                    displayError.textContent = "";
                    saveElement.removeAttribute("disabled");
                }
            });

            service.card.mount("#card_element");
        });
    };


    service.handleCardAuthorization = function (options) {
        var deferred = $q.defer();

        try {
            var displayError = document.getElementById("card_errors");
            var displayErrorParent = document.getElementById("card_error_parent");

            // We will fetch the setupIntent
            service
            .fetchPaymentIntent(options)
            .then(function (intentPayload) {
                // Card doesn't need to be auth'd! Continue without 3DS2/SCA
                if (intentPayload.paymentIntent.status === "requires_capture") {
                    deferred.resolve({
                        intentPayload: intentPayload,
                    });
                } else {
                    service
                    .stripe
                    .handleCardAction(intentPayload.paymentIntent.client_secret)
                    .then(function (result) {
                        if (result.error) {
                            // Inform the customer that there was an error.
                            displayErrorParent.classList.remove("ng-hide");
                            displayError.textContent = $translate.instant(result.error.message);

                            service
                            .authorizationError(result.error.message)
                            .then(function (payload) {
                                deferred.reject(payload);
                            });
                        } else {
                            // Sending the success token!
                            displayErrorParent.classList.add("ng-hide");

                            service
                            .authorizationSuccess(result)
                            .then(function (authorizationPayload) {
                                deferred.resolve({
                                    intentPayload: intentPayload,
                                    authorizationPayload: authorizationPayload
                                });

                                $rootScope.$broadcast("paymentStripeCards.refresh");
                            });
                        }
                    });
                }

            }, function (error) {
                throw new Error(error.message);
            });

        } catch (e) {
            service
            .setupError(e.message)
            .then(function (payload) {
                deferred.reject(payload);
            });
        }

        return deferred.promise;
    };

    service.authorizationError = function (message) {
        return $pwaRequest.post("/paymentstripe/mobile_handler/authorization-error",
            {
                data: {
                    message: message
                }
            });
    };

    service.authorizationSuccess = function (payload) {
        return $pwaRequest.post("/paymentstripe/mobile_handler/authorization-success",
            {
                data: {
                    payload: payload
                }
            });
    };

    service.handleCardPayment = function (options) {
        var deferred = $q.defer();

        try {
            var displayError = document.getElementById("card_errors");
            var displayErrorParent = document.getElementById("card_errors_parent");

            service
            .stripe
            .handleCardPayment(service.card)
            .then(function (result) {
                if (result.error) {
                    // Inform the customer that there was an error.
                    displayErrorParent.classList.remove("ng-hide");
                    displayError.textContent = $translate.instant(result.error.message);

                    service
                    .paymentError(result.error.message)
                    .then(function (payload) {
                        deferred.reject(payload);
                    });
                } else {
                    // Sending the success token!
                    displayErrorParent.classList.add("ng-hide");

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
            var displayError = document.getElementById("card_errors");
            var displayErrorParent = document.getElementById("card_errors_parent");

            // We will fetch the setupIntent
            service
            .fetchSetupIntent()
            .then(function (payload) {
                service
                .stripe
                .handleCardSetup(payload.setupIntent.client_secret, service.card)
                .then(function (result) {
                    if (result.error) {
                        // Inform the customer that there was an error.
                        displayErrorParent.classList.remove("ng-hide");
                        displayError.textContent = $translate.instant(result.error.message);

                        service
                        .setupError(result.error.message)
                        .then(function (payload) {
                            deferred.reject(payload);
                        });
                    } else {
                        // Sending the success token!
                        displayErrorParent.classList.add("ng-hide");

                        service
                        .setupSuccess(result)
                        .then(function (payload) {
                            deferred.resolve(payload);

                            $rootScope.$broadcast("paymentStripeCards.refresh");
                        });
                    }
                });
            }, function (error) {
                throw new Error(error.message);
            });

        } catch (e) {
            service
            .setupError(e.message)
            .then(function (payload) {
                deferred.reject(payload);
            });
        }

        return deferred.promise;
    };

    service.setupError = function (message) {
        return $pwaRequest.post("/paymentstripe/mobile_handler/setup-error",
            {
                data: {
                    message: message
                }
            });
    };

    service.setupSuccess = function (payload) {
        return $pwaRequest.post("/paymentstripe/mobile_handler/setup-success",
            {
                data: {
                    payload: payload
                }
            });
    };

    service.deletePaymentMethod = function (card) {
        return $pwaRequest.post("/paymentstripe/mobile_cards/delete-payment-method",
            {
                data: {
                    card: card
                }
            });
    };

    service.fetchSettings = function () {
        return $pwaRequest.post("/paymentstripe/mobile_cards/fetch-settings");
    };

    service.fetchVaults = function () {
        return $pwaRequest.post("/paymentstripe/mobile_cards/fetch-vaults");
    };

    service.fetchSetupIntent = function () {
        return $pwaRequest.post("/paymentstripe/mobile_cards/fetch-setup-intent");
    };

    service.fetchPaymentIntent = function (options) {
        return $pwaRequest.post("/paymentstripe/mobile_cards/fetch-payment-intent",
            {
                data: options
            });
    };

    service.clearForm = function () {
        // Clear form on success!
        service.card.clear();
        service.card.blur();
    };

    return service;
});
