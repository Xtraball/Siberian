/**
 * PaymentStripe service
 */
angular
.module("starter")
.service("PaymentStripe", function (Application, $injector, $translate, $pwaRequest, $q) {
    var service = {
        card: null,
        stripe: null,
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

        if (publishableKey &&
            publishableKey.length <= 0) {
            deferred.reject("publishableKey is required.");
            throw new Error("publishableKey is required.");
        }

        // Creating a new instance of the Stripe service!
        if (service.publishableKey !== publishableKey) {
            service.publishableKey = publishableKey;
            service.stripe = Stripe(service.publishableKey);
            try {
                service.card.destroy();
            } catch (e) {
                // Silent!
            }
        }

        deferred.resolve(service.publishableKey);

        return deferred.promise;
    };

    service.cardForm = function (successCallback, errorCallback) {
        if (typeof successCallback !== "function" ||
            typeof errorCallback !== "function") {
            throw new Error("successCallback & errorCallback must be functions.");
        }

        service.successCallback = successCallback;
        service.errorCallback = errorCallback;

        return service
        .isReady()
        .then(function () {
            var cardElementParent = document.getElementById("card-element");
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

            var saveElement = document.getElementById("save-element");
            var displayError = document.getElementById("card-errors");
            var displayErrorParent = document.getElementById("card-errors-parent");

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

            service.card.mount("#card-element");
        });
    };

    service.cardToken = function () {
        var deferred = $q.defer();

        try {
            var displayError = document.getElementById("card-errors");
            var displayErrorParent = document.getElementById("card-errors-parent");

            service
            .stripe
            .createToken(service.card)
            .then(function (result) {
                if (result.error) {
                    // Inform the customer that there was an error.
                    displayErrorParent.classList.remove("ng-hide");
                    displayError.textContent = $translate.instant(result.error.message);

                    deferred.reject(result.error.message);
                    service.errorCallback(result.error.message);
                } else {
                    // Sending the success token!
                    displayErrorParent.classList.add("ng-hide");

                    deferred.resolve(result);
                    service.successCallback(result);
                }
            });
        } catch (e) {
            deferred.reject(e.message);
            service.errorCallback(e.message);
        }

        return deferred.promise;
    };

    service.cardPayment = function (paymentIntentEndpoint) {
        var deferred = $q.defer();

        try {
            var displayError = document.getElementById("card-errors");
            var displayErrorParent = document.getElementById("card-errors-parent");

            service
            .stripe
            .handleCardPayment(service.card)
            .then(function (result) {
                if (result.error) {
                    // Inform the customer that there was an error.
                    displayErrorParent.classList.remove("ng-hide");
                    displayError.textContent = $translate.instant(result.error.message);

                    deferred.reject(result.error.message);
                    service.errorCallback(result.error.message);
                } else {
                    // Sending the success token!
                    displayErrorParent.classList.add("ng-hide");

                    deferred.resolve(result);
                    service.successCallback(result);


                }
            });
        } catch (e) {
            deferred.reject(e.message);
            service.errorCallback(e.message);
        }

        return deferred.promise;
    };

    service.cardSetup = function (setupIntentEndpoint) {
        var deferred = $q.defer();

        try {
            var displayError = document.getElementById("card-errors");
            var displayErrorParent = document.getElementById("card-errors-parent");

            // We will fetch the setupIntent
            service
            .fetchIntent(setupIntentEndpoint)
            .then(function (payload) {
                service
                .stripe
                .handleCardSetup(payload.setupIntent.client_secret, service.card)
                .then(function (result) {
                    if (result.error) {
                        // Inform the customer that there was an error.
                        displayErrorParent.classList.remove("ng-hide");
                        displayError.textContent = $translate.instant(result.error.message);

                        deferred.reject(result.error.message);
                        service.errorCallback(result.error.message);
                    } else {
                        // Sending the success token!
                        displayErrorParent.classList.add("ng-hide");

                        deferred.resolve(result);
                        service.successCallback(result);
                    }
                });
            }, function (error) {
                throw new Error(error.message);
            });


        } catch (e) {
            deferred.reject(e.message);
            service.errorCallback(e.message);
        }

        return deferred.promise;
    };

    service.fetchIntent = function (endpoint) {
        if (!angular.isDefined(endpoint)) {
            throw new Error($translate.instant("fetchIntent(): endpoint is required."));
        }

        return $pwaRequest.post(endpoint);
    };

    service.clearForm = function () {
        // Clear form on success!
        service.card.clear();
        service.card.blur();
    };

    return service;
});
