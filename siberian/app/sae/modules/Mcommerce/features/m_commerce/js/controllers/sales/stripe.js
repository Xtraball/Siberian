/*global
 App, BASE_PATH, Stripe
 */

angular
    .module("starter")
    .controller("MCommerceSalesStripeViewController", function (Loader, $scope, $state, $stateParams, $timeout,
                                                                $translate, Customer, McommerceStripe, Dialog) {

    $scope.is_loading = true;
    Loader.show();
    $scope.value_id = $stateParams.value_id;
    McommerceStripe.value_id = $stateParams.value_id;
    $scope.card = {};
    $scope.payment = {};
    $scope.payment.save_card = false;
    $scope.payment.use_stored_card = false;
    $scope.payment.hasStoredCard = false;
    $scope.isProcessing = false;

    $scope.cardElement = null;

    $scope.creditCardBrand = function (brand) {
        var _brand = (brand === undefined) ? "default" : brand;
        switch (_brand.toLowerCase()) {
            case "visa":
                return "./features/m_commerce/assets/templates/images/011-cc-visa.svg";
            case "mastercard":
                return "./features/m_commerce/assets/templates/images/012-cc-mastercard.svg";
            case "american express":
                return "./features/m_commerce/assets/templates/images/013-cc-amex.png";
        }
        return "./features/m_commerce/assets/templates/images/014-cc.svg";
    };

    $scope.loadContent = function () {
        $scope.guest_mode = Customer.guest_mode;
        var cust_id = null;
        if (Customer.isLoggedIn()) {
            cust_id = Customer.id;
        }

        //reset save card param
        $scope.payment.save_card = false;

        McommerceStripe
        .find(cust_id)
        .then(function (data) {
            // M-Commerce stripe instance! (fallback)
            McommerceStripe.StripeInstance = Stripe(data.publishable_key);

            $scope.cart_total = data.total;

            // Load previously saved card!
            if (data.card && data.card.exp_year) {
                $scope.card = data.card;
                $scope.payment.hasStoredCard = true;
            }
        }).then(function () {
            $scope.is_loading = false;
            Loader.hide();

            $timeout(function () {
                $scope.mountCard();
            }, 200);
        });

    };

    $scope.mountCard = function () {
        var cardElementParent = document.getElementById("mcommerce_card_element");
        try {
            cardElementParent.firstChild.remove();
        } catch (e) {
            // Silent!
        }

        var elements = McommerceStripe.StripeInstance.elements();
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

        $scope.cardElement = elements.create("card", {
            hidePostalCode: true,
            style: style
        });

        var saveElement = document.getElementById("mcommerce_save_element");
        var displayError = document.getElementById("mcommerce_card_errors");
        var displayErrorParent = document.getElementById("mcommerce_card_errors_parent");

        saveElement.setAttribute("disabled", "disabled");

        $scope.cardElement.removeEventListener("change");
        $scope.cardElement.addEventListener("change", function (event) {
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

        $scope.cardElement.mount("#mcommerce_card_element");
    };

    $scope.validateCard = function () {
        $scope.process();
    };

    $scope.deleteVault = function () {
        Dialog
        .confirm(
            "Confirmation",
            "Do you confirm you want to remove your card?",
            ["Yes", "No"],
            "m_commerce")
        .then(function (result) {
            if (result) {
                $scope.is_loading = true;
                Loader.show();
                //we cannot be there without customer
                McommerceStripe
                .removeCard(Customer.id)
                .then(function (data) {
                    $scope.oldcard = $scope.card;
                    $scope.card = {};
                    $scope.payment.use_stored_card = false;
                    $scope.payment.hasStoredCard = false;
                }).then(function () {
                    $scope.is_loading = false;
                    Loader.hide();
                });
            }
        });

    };

    $scope.useCard = function () {
        if (!$scope.isProcessing) {
            $scope.isProcessing = true;
            $scope.payment.use_stored_card = true;
            $scope.process();
        }
    };

    $scope.process = function () {
        if (!$scope.is_loading) {
            $scope.is_loading = true;
            Loader.show();
            if ($scope.payment.use_stored_card) {
                $scope._process();
            } else {
                McommerceStripe.StripeInstance
                .createToken($scope.cardElement)
                .then(function (result) {
                    _stripeResponseHandler(result);
                });
            }
        }
    };

    var _stripeResponseHandler = function (result) {
        if (result.error) {
            Dialog.alert("", result.error.message, "OK");
            $scope.is_loading = false;
            $scope.isProcessing = false;
            Loader.hide();
        } else {
            $scope.card = {
                token: result.token.id,
                last4: result.token.card.last4,
                brand: result.token.card.brand,
                exp_month: result.token.card.exp_month,
                exp_year: result.token.card.exp_year,
                exp: Math.round(+(new Date((new Date(result.token.card.exp_year, result.token.card.exp_month, 1)) - 1)) / 1000) | 0
            };

            $scope._process();
        }
    };

    $scope._process = function () {
        var data = {
            "token": $scope.card.token,
            "use_stored_card": $scope.payment.use_stored_card,
            "save_card": $scope.payment.save_card,
            "customer_id": Customer.id || null
        };

        McommerceStripe
            .process(data)
            .then(function (res) {
                if (res) {
                    $state.go("mcommerce-sales-success", {value_id: $stateParams.value_id});
                } else {
                    Dialog.alert("Error", "Unexpected error", "OK");
                }
            }, function (err) {
                Dialog.alert("Error", "Unexpected error", "OK");
            }).then(function () {
                $scope.is_loading = false;
                $scope.isProcessing = false;
                Loader.hide();
            });
    };

    $scope.right_button = {
        action: $scope.process,
        label: $translate.instant('Pay', 'm_commerce')
    };

    $scope.loadContent();

});