/*global
 App, angular, BASE_PATH, _
 */

angular.module('starter').controller('MCommerceCartViewController', function ($scope, $state, Loader, $stateParams,
                                                                              $translate, $timeout, Dialog,
                                                                              McommerceCart, Customer) {
    // Counter of pending tip calls!
    var updateTipTimoutFn = null;
    $scope.is_loading = true;

    $scope.points_data = {
        use_points: false,
        nb_points_used: null
    };

    McommerceCart.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant('Cart');

    $scope.loadContent = function () {
        Loader.show('Updating price');

        $scope.is_loading = true;

        McommerceCart.compute()
            .then(function (computation) {
                $scope.computation = computation;
            }).then(function () {
                $scope.computation = angular.isObject($scope.computation) ? $scope.computation : {};

                McommerceCart.find()
                    .then(function (data) {
                        if (data.cart.tip === 0) {
                            data.cart.tip = '';
                        }

                        if (
                            angular.isObject($scope.cart) &&
                                (!angular.isString(data.cart.discount_code) ||
                                 data.cart.discount_code.trim().length < 1)
                        ) {
                            data.cart.discount_code = $scope.cart.discount_code;
                        }

                        $scope.cart = data.cart;

                        $scope.cart.discount_message = $scope.computation.message;
                        $scope.cart.discount = $scope.computation.discount;

                        $scope.nb_stores = data.nb_stores;

                        if ($scope.cart.lines.length > 0) {
                            $scope.right_button = {
                                action: $scope.proceed,
                                label: $translate.instant('Proceed')
                            };
                        }
                    }).then(function () {
                        Customer.find()
                            .then(function (data) {
                                $scope.cart.customer_fidelity_points = (data.metadatas && data.metadatas.fidelity_points) ?
                                    data.metadatas.fidelity_points.points : null;
                                if (!$scope.points_data.use_points) {
                                    $scope.points_data.nb_points_used = $scope.cart.customer_fidelity_points;
                                }
                                $scope.updateEstimatedDiscount();
                            })
                            .then(function () {
                                Loader.hide();
                                $scope.is_loading = false;
                            });
                    });
            });
    };

    $scope.updateEstimatedDiscount = function () {
        if ($scope.points_data.nb_points_used > 0) {
            $scope.cart.estimated_fidelity_discount =
                (Math.round($scope.points_data.nb_points_used * $scope.cart.fidelity_rate * 100)/100) +
                ' ' + $scope.cart.currency_code;
        }
    };

    $scope.useFidelityChange = function () {
        if ($scope.points_data.use_points) {
            $scope.cart.discount_code = null;
            $scope.updateTipAndDiscount();
        }
    };

    $scope.updateTipAndDiscount = function () {
        var update = function () {
            Loader.show('Updating price');

            $scope.is_loading = true;
            McommerceCart.adddiscount($scope.cart.discount_code, true)
                .then(function () {
                    McommerceCart.addTip($scope.cart)
                        .then(function (data) {
                            Loader.hide();
                            $scope.is_loading = false;
                            if (data.success) {
                                if (angular.isDefined(data.message)) {
                                    Dialog.alert('', data.message, 'OK');
                                    return;
                                }
                            }
                        }, function (data) {
                            if (data && angular.isDefined(data.message)) {
                                Dialog.alert('', data.message, 'OK');
                            }
                        }).then(function () {
                            $scope.loadContent();
                        });
                });
        };

        if (updateTipTimoutFn) {
            clearTimeout(updateTipTimoutFn);
        }

        // Wait 100ms before update!
        updateTipTimoutFn = setTimeout(function () {
            update();
        }, 600);
    };

    $scope.proceed = function () {
        Loader.show();

        var gotToNext = function () {
            if (!$scope.cart.valid) {
                $scope.cartIdInvalid();
            } else if ($scope.nb_stores > 1) {
                $scope.goToStoreChoice();
            } else {
                $scope.goToOverview();
            }
        };

        if ($scope.cart && $scope.cart.discount_code) {
            McommerceCart.adddiscount($scope.cart.discount_code, true)
                .then(function (data) {
                    if (data && data.success) {
                        gotToNext();
                    } else {
                        if (data && data.message) {
                            Dialog.alert('', data.message, 'OK');
                        } else {
                            Dialog.alert('', 'Unexpected Error', 'OK');
                        }
                    }
                }, function (resp) {
                    var data = resp.data;
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert('', data.message, 'OK');
                    }
                }).then(function () {
                    Loader.hide();
                });
        } else if ($scope.points_data.use_points) {
            if ($scope.points_data.nb_points_used > 0) {
                if ($scope.points_data.nb_points_used <= $scope.cart.customer_fidelity_points) {
                    McommerceCart.useFidelityPoints($scope.points_data.nb_points_used)
                        .then(function (data) {
                            gotToNext();
                        }, function (data) {
                            Dialog.alert('', data.message, 'OK');
                        }).then(function () {
                            Loader.hide();
                        });
                } else {
                    Dialog.alert('', "You don't have enough points", 'OK');
                }
            }
        } else {
            McommerceCart.removeAllDiscount()
                .then(function (data) {
                    gotToNext();
                }, function (data) {
                    Dialog.alert('', data.message, 'OK');
                }).then(function () {
                Loader.hide();
                });
        }
    };

    $scope.cartIdInvalid = function () {
        Dialog.alert('', $scope.cart.valid_message, 'OK');
    };

    $scope.goToStoreChoice = function () {
        $state.go('mcommerce-sales-store', {
            value_id: $scope.value_id
        });
    };

    $scope.goToOverview = function () {
        if (!$scope.is_loading) {
            $state.go('mcommerce-sales-customer', {
                value_id: $scope.value_id
            });
        }
    };

    $scope.goToCategories = function () {
        $state.go('mcommerce-category-list', {
            value_id: $scope.value_id
        });
    };

    $scope.removeLine = function (line) {
        Loader.show('Updating price');

        $scope.is_loading = true;
        McommerceCart.deleteLine(line.id)
            .then(function (data) {
                if (data.success) {
                    if (angular.isDefined(data.message)) {
                        Dialog.alert('', data.message, 'OK');
                        return;
                    }
                    // update content
                    $scope.loadContent();
                }
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('', data.message, 'OK');
                }
            });
    };

    $scope.changeQuantity = function (qty, params) {
        Loader.show('Updating price');

        $scope.is_loading = true;

        var localLine = angular.copy(params.line);
        localLine.qty = angular.copy(qty);

        return McommerceCart.modifyLine(localLine)
            .then(function (data) {
                $scope.cart.formattedSubtotalExclTax = data.cart.formattedSubtotalExclTax;
                $scope.cart.formattedDeliveryCost = data.cart.formattedDeliveryCost;
                $scope.cart.formattedTotalExclTax = data.cart.formattedTotalExclTax;
                $scope.cart.formattedTotalTax = data.cart.formattedTotalTax;
                $scope.cart.formattedTotal = data.cart.formattedTotal;
                $scope.cart.deliveryCost = data.cart.deliveryCost;
                $scope.cart.valid = data.cart.valid;

                return data;
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show();
                }
            }).then(function (data) {
                var scopeLineIndex = _.findIndex($scope.cart.lines, function (line) {
                    return line.id == data.line.id;
                });

                $timeout(function () {
                    $scope.cart.lines[scopeLineIndex] = data.line;
                    $scope.cart.lines[scopeLineIndex].qty = data.line.qty;

                    Loader.hide();
                    $scope.is_loading = false;
                }, 500);

                return data;
            }).catch(function () {
                Loader.hide();
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();
});
;/*global
 App, BASE_PATH
 */

angular.module("starter").controller("MCommerceListController", function(Loader, $location, $scope, $state, $stateParams, McommerceCategory,
                                                  Customer) {

    $scope.is_loading = true;
    Loader.show();

    $scope.factory = McommerceCategory;
    $scope.collection = [];
    $scope.collection_is_empty = true;

    McommerceCategory.value_id = $stateParams.value_id;
    McommerceCategory.category_id = $stateParams.category_id;
    $scope.value_id = $stateParams.value_id;

    $scope.use_button_header = false;
    if(Customer.isLoggedIn() && !$stateParams.category_id) {
        $scope.use_button_header = true;
    }

    $scope.loadContent = function() {

        McommerceCategory.findAll()
            .then(function(data) {

                $scope.show_search = data.show_search;
                $scope.collection = data.collection;
                $scope.collection_is_empty = $scope.collection.length > 0;

                $scope.cover = data.cover;
                $scope.page_title = data.page_title;

            }).then(function() {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.openCart = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-cart-view", {
                value_id: $scope.value_id
            });
        }
    };

    $scope.openHistory = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-sales-history", {
                value_id: $scope.value_id
            });
        }
    };

    if(!$scope.use_button_header) {
        $scope.right_button = {
            action: $scope.openCart,
            icon: "ion-ios-cart"
        };
    }

    $scope.showItem = function(item) {
        $location.path(item.url);
    };

    $scope.loadContent();

}).controller("MCommerceRedirectController", function($ionicHistory, $scope, $state, $stateParams, HomepageLayout) {

    $scope.value_id = $stateParams.value_id;
    $scope.layout = HomepageLayout;

    $state.go("home").then(function() {
        if($scope.layout.properties.options.autoSelectFirst) {
            $ionicHistory.nextViewOptions({
                historyRoot: true,
                disableAnimate: false
            });
        }
        $state.go('mcommerce-category-list', {
            value_id: $scope.value_id
        });
    });

});;/*global
 App, angular, BASE_PATH, DOMAIN
 */

angular.module("starter").controller("MCommerceProductViewController", function ($cordovaSocialSharing, Loader,
                                                          $log, $state, $stateParams, $scope, $translate, Analytics,
                                                          Application, Dialog, McommerceCategory,
                                                          McommerceCart, McommerceProduct, $rootScope) {
    McommerceProduct.value_id   = $stateParams.value_id;
    McommerceCart.value_id      = $stateParams.value_id;
    $scope.value_id             = $stateParams.value_id;

    $scope.product_id = $stateParams.product_id;

    $scope.social_sharing_active = false;

    $scope.product_quantity = 1;

    $scope.selected_format = {id:null};
    $scope.list_qty = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Loader.show();

        McommerceProduct.find($scope.product_id)
            .then(function (data) {
                $scope.product = data.product;

                Analytics.storeProductOpening($scope.product);

                $scope.social_sharing_active = ($scope.product.social_sharing_active && $rootScope.isNativeApp);

                $scope.share = function () {

                    // Fix for $cordovaSocialSharing issue that opens dialog twice
                    if($scope.is_sharing) {
                        return;
                    }

                    $scope.is_sharing = true;

                    var app_name = Application.app_name;
                    var link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;
                    var subject = "";
                    var file = ($scope.product.picture[0] && $scope.product.picture[0].url)  ? $scope.product.picture[0].url : "";
                    var content = $scope.product.name;
                    var message = $translate.instant("Hi. I just found: $1 in the $2 app.").replace("$1", content).replace("$2", app_name);
                    $cordovaSocialSharing
                        .share(message, subject, file, link) // Share via native share sheet
                        .then(function (result) {
                            $log.debug("MCommerce::product.js", "social sharing success");
                            $scope.is_sharing = false;
                        }, function (err) {
                            $log.debug("MCommerce::product.js", err);
                            $scope.is_sharing = false;
                        });
                };

                if($scope.product.formatGroups.length > 0) {
                    $scope.selected_format.id = $scope.product.formatGroups[0].id;
                }

                $scope.page_title = data.page_title;

            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.addProduct = function () {

        $scope.is_loading = true;
        Loader.show();

        var errors = [];

        var postParameters = {
            'product_id': $scope.product_id,
            'category_id': McommerceCategory.category_id,
            'qty': $scope.product_quantity,
            'options': $scope.product.optionsGroups.reduce(function (options, optionsGroup) {
                if(optionsGroup.required &&  !optionsGroup.selectedOptionId) {
                    errors.push($translate.instant("Option $1 is required.").replace("$1", optionsGroup.title));
                }

                if(optionsGroup.selectedQuantity <= 0 || !optionsGroup.selectedQuantity) {
                    errors.push($translate.instant("Quantity for option $1 is required.").replace("$1", optionsGroup.title));
                }
                options[optionsGroup.id] = {
                    'option_id': optionsGroup.selectedOptionId,
                    'qty': optionsGroup.selectedQuantity
                };
                return options;
            }, {}),
            'choices': $scope.product.choicesGroups.reduce(function (choices, choicesGroup) {

                var selected = [];

                choicesGroup.options.forEach(function(e, i){
                    if(e.selected){
                        selected.push(e.id);
                    }
                });

                if(choicesGroup.required &&  !selected.length) {
                    errors.push($translate.instant("Option $1 is required.").replace("$1", choicesGroup.title));
                }

                choices[choicesGroup.id] = {
                    'selected_options': selected
                };

                return choices;

            }, {}),
            'selected_format': $scope.selected_format.id
        };

        if(errors.length <= 0) {
            McommerceCart.addProduct(postParameters)
                .then(function (data) {
                    if (data.success) {
                        $scope.is_loading = false;
                        Loader.hide();
                        $scope.openCart();
                    }
                }, function (data) {
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert("", data.message, $translate.instant("OK"));
                    }
                    $scope.is_loading = false;
                    //Don't forget to "reset" selection
                    $scope.product_quantity = 1;
                    $scope.selected_format = {id:null};
                    $scope.product.optionsGroups.forEach(function(option) {
                        option.selectedOptionId = null;
                        option.selectedQuantity = 1;
                    });
                    $scope.product.choicesGroups.forEach(function(choice) {
                        choice.options.forEach(function(option) {
                            option.selected = false;
                        });
                    });
                    Loader.hide();
                });
        } else {
            var message = errors.join("<br/>");
            Dialog.alert("", message, "OK");

            $scope.is_loading = false;
            Loader.hide();
        }

    };

    $scope.openCart = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-cart-view", {value_id: $scope.value_id});
        }

    };

    $scope.changeQuantity = function(qty) {
        if(qty) {
            $scope.product_quantity = qty;
        }
    };

    $scope.right_button = {
        action: $scope.openCart,
        icon: "ion-ios-cart"
    };

    $scope.loadContent();

});;/* global
    App, angular, BASE_PATH
 */

angular.module('starter').controller('MCommerceSalesConfirmationViewController', function ($ionicPopup, Loader, $location, $rootScope,
                                                                    $scope, $state, $stateParams, $timeout, $translate,
                                                                    $window, Analytics, Application, Customer, Dialog,
                                                                    McommerceCart, McommerceSalesPayment) {
    $scope.is_loading = true;
    Loader.show();

    $scope.page_title = $translate.instant('Review');

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.loadContent = function () {
        $scope.guest_mode = Customer.guest_mode;
        McommerceCart.compute()
            .then(function (computation) {
                McommerceCart.find()
                    .then(function (data) {
                        $scope.cart = data.cart;
                        $scope.cart.discount_message = computation.message;
                        $scope.cart.discount = computation.discount;

                        McommerceSalesPayment.findOnlinePaymentUrl()
                            .then(function (data) {
                                $scope.onlinePaymentUrl = data.url;
                                $scope.form_url = data.form_url;

                                $scope.right_button = {
                                    action: $scope.validate,
                                    label: $translate.instant('Validate')
                                };
                            }).then(function () {
                                Loader.hide();
                                if (computation &&
                                    angular.isDefined(computation.message) &&
                                    computation.show_message) {
                                    Dialog.alert('', computation.message, 'OK');
                                }
                                $scope.is_loading = false;
                            });
                    }, function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });
            }, function () {
                Loader.hide();
                $scope.is_loading = false;
            });
    };

    $scope.validate = function () {
        // TG-459
        McommerceSalesPayment.notes = $scope.notes || '';
        sessionStorage.setItem('mcommerce-notes', $scope.notes || '');

        if ($scope.onlinePaymentUrl) {
            if (Application.is_webview) {
                $window.location = $scope.onlinePaymentUrl;
            } else {
                /** @todo Use LinkService but force inAppBrowser with listeners */
                var browser = $window.open($scope.onlinePaymentUrl, $rootScope.getTargetForLink(), 'location=yes');

                browser.addEventListener('loadstart', function (event) {
                    if (/(mcommerce\/mobile_sales_confirmation\/confirm)/.test(event.url)) {
                        var url = new URL(event.url);
                        var params = url.search.replace(/(^\?)/, '').split('&').map(function (n) {
                            return n = n.split('='), this[n[0]] = n[1], this;
                        }.bind({}))[0];
                        if (params.token && params.payer_id) {
                            browser.close();
                            $state.go('mcommerce-sales-confirmation-payment', {
                                token: params.token,
                                payer_id: params.payer_id,
                                value_id: $stateParams.value_id
                            });
                        }
                    } else if (/(mcommerce\/mobile_sales_confirmation\/cancel)/.test(event.url)) {
                        browser.close();

                        Dialog.alert('', 'The payment has been cancelled, something wrong happened? Feel free to contact us.', 'OK')
                            .then(function () {
                                $state.go('mcommerce-sales-confirmation', {
                                    value_id: $stateParams.value_id
                                });
                            });
                    }
                });
            }
        } else if ($scope.form_url) {
            $location.path($scope.form_url);
        } else {
            if ($scope.is_loading) {
                return;
            }

            $scope.is_loading = true;
            Loader.show();

            McommerceSalesPayment.validatePayment()
                .then(function (data) {
                    var products = [];
                    angular.forEach($scope.cart.lines, function (value, key) {
                        var product = value.product;
                        product.category_id = value.category_id;
                        product.quantity = value.qty;

                        products.push(product);
                    });
                    Analytics.storeProductSold(products);
                    // end of non online payment!
                    $state.go('mcommerce-sales-success', {
                        value_id: $stateParams.value_id
                    });
                }, function (data) {
                    Dialog.alert('', data.message, 'OK');
                }).then(function () {
                    $scope.is_loading = false;
                    Loader.hide();
                });
        }
    };

    $scope.loadContent();
}).controller('MCommerceSalesConfirmationConfirmPaymentController', function ($ionicLoading, $scope, $state,
                                                                              $stateParams, $timeout, McommerceCart,
                                                                              McommerceSalesPayment) {
    $scope.is_loading = true;
    $ionicLoading.show({
        template: '<ion-spinner class="spinner-custom"></ion-spinner>'
    });

    McommerceSalesPayment.value_id = $stateParams.value_id;

    McommerceSalesPayment.validateOnlinePayment($stateParams.token, $stateParams.payer_id)
        .then(function (data) {
            if (data.success) {
                // end of non online payment!
                $state.go('mcommerce-sales-success', {
                    value_id: $stateParams.value_id
                });
            }
        }, function (data) {
            if (data && angular.isDefined(data.message)) {
                $scope.confirmation_message = data.message;
            }
            // redirect after 5 seconds!
            $timeout(function () {
                $state.go('mcommerce-sales-confirmation', {
                    value_id: $stateParams.value_id
                });
            }, 5000);
        }).then(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
}).controller('MCommerceSalesConfirmationCancelController', function ($state, $stateParams, $translate, Dialog) {
    // display cancelation message!
    Dialog.alert('', 'The payment has been cancelled, something wrong happened? Feel free to contact us.', 'OK')
        .then(function () {
            $state.go('mcommerce-sales-confirmation', {
                value_id: $stateParams.value_id
            });
        });
});
;/*global
 App, angular, BASE_PATH
 */
angular.module('starter').controller('MCommerceSalesCustomerViewController', function (Loader, $state, $stateParams,
                                                                                       $scope, $translate, $rootScope,
                                                                                       McommerceCart,
                                                                                       McommerceSalesCustomer, Customer,
                                                                                       Dialog, SB) {
    Customer.onStatusChange('category', []);

    $scope.hasguestmode = false;

    $scope.customer_login = function () {
        Customer.display_account_form = false;
        Customer.loginModal($scope);
    };

    $scope.customer_signup = function () {
        Customer.display_account_form = true;
        Customer.loginModal($scope);
    };

    $scope.customer_guestmode = function () {
        Loader.show();
        var currentTs = Date.now();
        var guestmail = 'guest' + currentTs + (parseInt(Math.random() * 1000, 10)) + '@guest.com';
        Customer.register({
                civility: 'm',
                firstname: 'Guest',
                lastname: 'Guest',
                email: guestmail,
                password: parseInt(Math.random() * 10000000000, 10),
                privacy_policy: true
            }).then(function () {
                $scope.is_logged_in = true;
                Customer.guest_mode = true;
                $scope.loadContent();
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.is_logged_in = false;
    });

    $scope.is_loading = true;
    Loader.show();
    $scope.is_logged_in = Customer.isLoggedIn();

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesCustomer.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant('My information');

    $scope.loadContent = function () {
        McommerceSalesCustomer
            .hasGuestMode()
            .then(function (dataGuestMode) {
                // Check if had guest mode!
                if (dataGuestMode.success && dataGuestMode.activated) {
                    $scope.hasguestmode = true;
                }
                // Getting user!
                McommerceSalesCustomer
                    .find()
                    .then(function (data) {
                        $scope.customer = data.customer;
                        // Fix birthday!
                        if ($scope.customer && $scope.customer.hasOwnProperty('metadatas') && $scope.customer.metadatas.birthday) {
                            $scope.customer.metadatas.birthday = new Date($scope.customer.metadatas.birthday);
                        }
                        $scope.settings = data.settings;
                    }).then(function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK');
                }
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.goToDeliveryPage = function () {
        $state.go('mcommerce-sales-delivery', {
            value_id: $stateParams.value_id
        });
    };

    $scope.updateCustomerInfos = function () {
        $rootScope.loginFeature = true;
        $rootScope.loginFeatureBack = false;

        $scope.is_loading = true;
        Loader.show();

        // Associate the customer to the cart and validate the extra fields!
        McommerceSalesCustomer
            .updateCustomerInfos({
                customer: $scope.customer
            })
            .then(function (data) {
                $scope.customer = data.customer;

                // Save Customer info
                Customer
                    .save($scope.customer)
                    .then(function (data) {
                        if (angular.isDefined(data.message)) {
                        }
                        $scope.goToDeliveryPage();
                    }, function (data) {
                        if (data && angular.isDefined(data.message)) {
                            Dialog.alert('Error', data.message, 'OK');
                        }
                    }).then(function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });
            }, function (data) {
                $scope.is_loading = false;
                Loader.hide();
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK');
                }
            });
    };

    $scope.right_button = {
        action: $scope.updateCustomerInfos,
        label: $translate.instant('Next')
    };

    $scope.loadContent();
});
;/*global
 App, angular, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesDeliveryViewController", function (Loader, $scope, $stateParams, $state,
                                                                $translate, McommerceCart, McommerceSalesDelivery,
                                                                Dialog) {

    $scope.page_title = $translate.instant("Delivery");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesDelivery.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.clients_calculate_change_form_is_visible = false;

    $scope.loadContent = function () {
        $scope.is_loading = true;
        Loader.show();
        McommerceCart.find()
            .then(function (data) {
                $scope.cart = data.cart;
                $scope.cart.delivery_method_extra_client_amount = $scope.cart.paid_amount ? parseFloat($scope.cart.paid_amount) : $scope.cart.total;
                $scope.cart.delivery_method_extra_amount_due = ($scope.cart.delivery_amount_due) ? parseFloat($scope.cart.delivery_amount_due) : 0;
                $scope.calculateAmountDue();

                McommerceSalesDelivery.findStore()
                    .then(function (data) {
                        $scope.clients_calculate_change_form_is_visible = data.clients_calculate_change;
                        $scope.selectedStore = data.store;

                    }).then(function () {
                        $scope.is_loading = false;
                    Loader.hide();
                    });

            }, function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.selectDeliveryMethod = function(cart, delivery_method) {
        var tmp_total_with_fees = cart.base_total_without_fees + delivery_method.price;

        cart.total = parseFloat(tmp_total_with_fees != cart.total && !cart.deliveryCost ? tmp_total_with_fees : cart.total);

        cart.delivery_method_extra_client_amount = cart.total;
        cart.deliveryMethodId = delivery_method.id;
        $scope.clients_calculate_change_form_is_visible = ((delivery_method.code == "home_delivery") && $scope.selectedStore.clients_calculate_change);
        $scope.cart.delivery_method_extra_amount_due = 0;
    };

    $scope.calculateAmountDue = function() {
        var price = parseFloat($scope.cart.delivery_method_extra_client_amount);

        if(isNaN(price) || price < $scope.cart.total) {
            if(isNaN(price)) {
                $scope.cart.delivery_method_extra_client_amount = "";
            }
            $scope.cart.delivery_method_extra_amount_due = null;
            return;
        }

        $scope.cart.delivery_method_extra_amount_due = (price - $scope.cart.total).toFixed(2);
        $scope.cart.total = $scope.cart.total;

    };

    $scope.updateDeliveryInfos = function () {

        if($scope.cart.delivery_method_extra_amount_due == null) {
            Dialog.alert("", "Remaining due is incorrect.", "OK").then(function() {
                return;
            });
        }

        if(!$scope.cart.deliveryMethodId) {
            Dialog.alert("", "You have to choose a delivery method.", "OK").then(function() {
                return;
            });
        }

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            Loader.show();

            var postParameters = {
                'delivery_method_id': $scope.cart.deliveryMethodId,
                'paid_amount': $scope.clients_calculate_change_form_is_visible ? $scope.cart.delivery_method_extra_client_amount:null,
                'store_id': $scope.selectedStore.id
            };

            McommerceSalesDelivery.updateDeliveryInfos(postParameters)
                .then(function (data) {
                    $scope.goToPaymentPage();
                }, function (data) {
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert("", data.message, "OK");
                    }
                }).then(function() {
                    $scope.is_loading = false;
                    Loader.hide();
                });
        }
    };

    $scope.goToPaymentPage = function () {
        $state.go("mcommerce-sales-payment", {
            value_id: $stateParams.value_id
        });
    };

    $scope.right_button = {
        action: $scope.updateDeliveryInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});;/*global
 App, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesErrorViewController", function ($scope, $state, $stateParams, $timeout) {

    $scope.value_id = $stateParams.value_id;

    $timeout(function() {
        $state.go("mcommerce-redirect", {value_id: $scope.value_id});
    }, 4000);

});;/*global
    App, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesHistoryViewController", function (Loader, $scope, $state, $stateParams,
                                                               $translate, McommerceSalesCustomer) {

    $scope.value_id = $stateParams.value_id;

    $scope.page_title = $translate.instant("Order history");

    $scope.showLoader = function() {
        Loader.show();
    };

    $scope.orders = [];
    $scope.offset = 0;
    $scope.can_load_older_posts = true;

    McommerceSalesCustomer.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        $scope.showLoader();

        McommerceSalesCustomer.getOrderHistory($scope.offset)
            .then(function(data) {

                if(data.orders) {
                    $scope.orders = $scope.orders.concat(data.orders);
                    $scope.offset += data.orders.length;

                    if(data.orders.length <= 0) {
                        $scope.can_load_older_posts = false;
                    }
                    return true;
                } else {
                    $scope.orders = [];
                    return false;
                }

            }).then(function(refresh) {
                if(refresh) {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }
                Loader.hide();
            });
    };

    $scope.loadContent();

    $scope.showDetails = function(order_id) {
        $state.go("mcommerce-sales-history-details", {value_id: $scope.value_id, order_id: order_id});
    };

    $scope.loadMore = function() {
        $scope.loadContent();
    };

}).controller("MCommerceSalesHistoryDetailsController", function (Loader, $scope, $stateParams,
                                                                  $translate, McommerceSalesCustomer) {

    $scope.value_id = $stateParams.value_id;
    $scope.page_title = $translate.instant("Order details");
    $scope.order_id = $stateParams.order_id;

    Loader.show();

    McommerceSalesCustomer.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        McommerceSalesCustomer.getOrderDetails($scope.order_id)
            .then(function(data) {

                $scope.order = data.order;

            }).then(function() {
                Loader.hide();
            });
    };

    $scope.loadContent();

});;/*global
 App, angular, BASE_PATH
 */
angular.module("starter").controller("MCommerceSalesPaymentViewController", function (Loader, $scope, $state, $stateParams,
                                                               $translate, McommerceCart, McommerceSalesPayment,
                                                               Dialog) {

    $scope.page_title = $translate.instant("Payment");

    McommerceCart.value_id = $stateParams.value_id;
    McommerceSalesPayment.value_id = $stateParams.value_id;
    $scope.value_id = $stateParams.value_id;

    $scope.loadContent = function () {
        Loader.show();
        McommerceCart.find()
            .then(function (data) {

            $scope.cart = data.cart;

            McommerceSalesPayment.findPaymentMethods()
                .then(function (data) {
                    $scope.paymentMethods = data.paymentMethods;

                    $scope.paymentMethodId = data.paymentMethods.reduce(function (paymentMethodId, paymentMethod) {
                        if ($scope.cart.paymentMethodId === paymentMethod.id) {
                            paymentMethodId = paymentMethod.id;
                        }

                        return paymentMethodId;
                    }, null);

                    if($scope.paymentMethods.length == 1 && $scope.paymentMethods[0].code == "free") {
                        //Free purchase we can skip the payment method selection
                        $scope.cart.paymentMethodId = $scope.paymentMethods[0].id;
                        $scope.updatePaymentInfos();
                    }

                }).then(function () {
                    $scope.is_loading = false;
                Loader.hide();
                });

        }, function () {
            $scope.is_loading = false;
                Loader.hide();
        });
    };

    $scope.updatePaymentInfos = function () {

        if(!$scope.is_loading) {

            $scope.is_loading = true;
            Loader.show();

            var postParameters = {
                'payment_method_id': $scope.cart.paymentMethodId
            };

            McommerceSalesPayment.updatePaymentInfos(postParameters)
                .then(function (data) {
                    $scope.goToConfirmationPage();

                }, function (data) {
                    if (data && angular.isDefined(data.message)) {
                        Dialog.alert("", data.message, "OK");
                    }

                }).then(function() {
                    $scope.is_loading = false;
                    Loader.hide();
                });
        }
    };

    $scope.goToConfirmationPage = function () {
        if($scope.cart.paymentMethodId) {
            $state.go("mcommerce-sales-confirmation", {
                value_id: $stateParams.value_id
            });
        } else {
            Dialog.alert("", "Please choose a payment method.", "OK");
        }
    };

    $scope.right_button = {
        action: $scope.updatePaymentInfos,
        label: $translate.instant("Next")
    };

    $scope.loadContent();

});;/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("MCommerceSalesStoreChoiceController", function (Loader, $scope, $state, $stateParams,
                                                               $translate, Dialog, McommerceSalesStorechoice) {

    $scope.value_id = $stateParams.value_id;
    McommerceSalesStorechoice.value_id = $stateParams.value_id;
    $scope.selected_store = {id:null};

    $scope.loadContent = function () {
        $scope.is_loading = true;
        Loader.show();
        McommerceSalesStorechoice
            .find()
            .then(function (data) {
                $scope.stores = data.stores;
                $scope.cart_amount = data.cart_amount;
                $scope.selected_store.id = data.store_id;
                if($scope.selected_store.id) {
                    $scope.chooseStore();
                }
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });
    };

    $scope.chooseStore = function() {
        if($scope.selected_store.id) {
            $scope.min_amount = 0;
            angular.forEach($scope.stores,function(store) {
                if(store.id == $scope.selected_store.id) {
                    $scope.min_amount = store.min_amount;
                    $scope.error_message = store.error_message;
                }
            });

            if($scope.min_amount <= $scope.cart_amount) {
                $scope.is_loading = true;
                Loader.show();
                McommerceSalesStorechoice
                    .update($scope.selected_store.id)
                    .then(function (data) {
                        if (data.store_id) {
                            $scope.showNextButton();
                        }
                    }).then(function () {
                        $scope.is_loading = false;
                        Loader.hide();
                    });

            } else {
                Dialog.alert("", $scope.error_message, "OK");
                $scope.hideNextButton();
            }
        }
    };

    $scope.goToOverview = function () {

        if(!$scope.is_loading) {
            $state.go("mcommerce-sales-customer", {
                value_id: $scope.value_id
            });
        }
    };

    $scope.showNextButton = function() {
        $scope.right_button = {
            action: $scope.goToOverview,
            label: $translate.instant("Proceed")
        };
    };

    $scope.hideNextButton = function() {
        $scope.right_button = {
            action: null,
            label: ""
        };
    };

    $scope.loadContent();

});;/*global
 App, BASE_PATH, Stripe
 */

angular.module("starter").controller("MCommerceSalesStripeViewController", function (Loader, $scope, $state,
                                                              $stateParams, $timeout, $translate,
                                                              Customer, McommerceStripe, Dialog) {

    $scope.is_loading = true;
    Loader.show();
    $scope.value_id = $stateParams.value_id;
    McommerceStripe.value_id = $stateParams.value_id;
    $scope.card = {};
    $scope.payment = {};
    $scope.payment.save_card = false;
    $scope.payment.use_stored_card = false;

    $scope.loadContent = function () {
        $scope.guest_mode = Customer.guest_mode;
        var cust_id = null;
        if(Customer.isLoggedIn()) {
            cust_id = Customer.id;
        }

        //reset save card param
        $scope.payment.save_card = false;

        McommerceStripe
            .find(cust_id)
            .then(function (data) {
                Stripe.setPublishableKey(data.publishable_key);
                $scope.cart_total = data.total;
                if(data.card && data.card.exp_year){
                    $scope.card = data.card;
                    $scope.payment.use_stored_card = true;
                }
            }).then(function () {
                $scope.is_loading = false;
                Loader.hide();
            });

    };

    if(typeof Stripe === "undefined") {
        var stripeJS = document.createElement('script');
        stripeJS.type = "text/javascript";
        stripeJS.src = "https://js.stripe.com/v2/";
        stripeJS.onload = function() {
            $scope.loadContent();
        };
        document.body.appendChild(stripeJS);
    } else {
        $scope.loadContent();
    }

    $scope.unloadcard = function () {
        Dialog.confirm("Confirmation", "Do you confirm you want to remove your card?", ["Yes", "No"])
            .then(function(result) {
                if(result) {
                    $scope.is_loading = true;
                    Loader.show();
                    //we cannot be there without customer
                    McommerceStripe
                        .removeCard(Customer.id)
                        .then(function (data) {
                            $scope.oldcard = $scope.card;
                            $scope.card = {};
                            $scope.payment.use_stored_card = false;
                        }).then(function () {
                            $scope.is_loading = false;
                            Loader.hide();
                        });
                }
            });

    };

    $scope.process = function () {
        if (!$scope.is_loading) {
            $scope.is_loading = true;
            Loader.show();
            if ($scope.payment.use_stored_card) {
                _process();
            } else {
                Stripe.card.createToken($scope.card, function (status, response) {
                    _stripeResponseHandler(status, response);
                });
            }
        }
    };

    var _stripeResponseHandler = function(status, response) {
        $timeout(function() {
            if (response.error) {
                Dialog.alert("", response.error.message, "OK");
                $scope.is_loading = false;
                Loader.hide();
            } else {
                $scope.card = {
                    token: response.id,
                    last4: response.card.last4,
                    brand: response.card.brand,
                    exp_month: response.card.exp_month,
                    exp_year: response.card.exp_year,
                    exp: Math.round(+(new Date((new Date(response.card.exp_year, response.card.exp_month, 1)) - 1)) / 1000) | 0
                };

                _process();
            }
        });
    };

    //function to make payment when all is ready
    var _process = function () {
        var data = {
            "token"             : $scope.card.token,
            "use_stored_card"   : $scope.payment.use_stored_card,
            "save_card"         : $scope.payment.save_card,
            "customer_id"       : Customer.id || null
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
                Loader.hide();
            });
    };

    $scope.right_button = {
        action: $scope.process,
        label: $translate.instant("Pay")
    };

});;/*global
    App, BASE_PATH
 */

angular.module("starter").controller('MCommerceSalesSuccessViewController', function ($scope, $state, $stateParams,
                                                                                      $timeout, Customer) {

    $scope.value_id = $stateParams.value_id;

    if(Customer.guest_mode) {
        Customer.guest_mode = false;
        Customer.logout();
    }

    $timeout(function() {
        $state.go("mcommerce-redirect", {
            value_id: $scope.value_id
        });
    }, 3000);

});;/*global
    App
 */
angular.module("starter").factory("McommerceCart", function($pwaRequest, $session) {

    var factory = {
        value_id: null
    };

    factory.find = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::find] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_cart/find", {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };
    
    factory.addProduct = function (form) {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::addProduct] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_cart/add", {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form
            }
        });
    };


    factory.adddiscount = function (discount_code, use_clean_code) {

        if (!this.value_id) {
            return use_clean_code ? $pwaRequest.reject("[McommerceCart::adddiscount] missing value_id.") : false;
        }

        //if no discount added, it's valid
        if(discount_code.length === 0 && !use_clean_code) {
            return true;
        }

        return $pwaRequest.post("mcommerce/mobile_cart/adddiscount", {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                discount_code: discount_code,
                customer_uuid: $session.getDeviceUid()

            }
        });
    };

    factory.addTip = function (cart) {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::addTip] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_cart/addtip", {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                tip: cart.tip ? cart.tip : 0
            }
        });
    };

    factory.compute = function () {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::compute] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_cart/compute", {
            urlParams: {
                value_id: this.value_id,
                customer_uuid: $session.getDeviceUid()
            }
        });
    };

    factory.deleteLine = function (line_id) {

        if (!this.value_id || !line_id) {
            return $pwaRequest.reject("[McommerceCart::deleteLine] missing value_id or line_id.");
        }
        
        return $pwaRequest.get("mcommerce/mobile_cart/delete", {
            urlParams: {
                value_id: this.value_id,
                line_id: line_id
            }
        });
                                          
    };

    factory.modifyLine = function (line) {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::modifyLine] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_cart/modify", {
            data: {
                line_id: line.id,
                qty : line.qty,
                format: line.format
            }
        });

    };

    factory.useFidelityPoints = function(points) {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::useFidelityPoints] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_cart/usefidelitypointsforcart", {
            data: {
                points: points
            }
        });
    };

    factory.removeAllDiscount = function() {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceCart::removeAllDiscount] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_cart/removealldiscount");
    };

    return factory;
});
;/*global
    App
 */
angular.module("starter").factory("McommerceCategory", function($pwaRequest) {

    var factory = {
        value_id: null,
        category_id: null
    };

    factory.findAll = function(offset) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceCategory::findAll] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_category/findall", {
            urlParams:  {
                value_id: this.value_id,
                category_id: this.category_id,
                offset: offset
            }
        }).then(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
            return data;
        });
    };

    return factory;
});
;/*global
    App
 */
angular.module("starter").factory("McommerceProduct", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.find = function(product_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceProduct::find] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_product/find", {
            urlParams: {
                value_id: this.value_id,
                product_id: product_id
            }
        });
    };

    return factory;
});
;/* global
    App
 */
angular.module('starter').factory('McommerceSalesCustomer', function ($pwaRequest) {
    var factory = {
        value_id: null
    };

    factory.updateCustomerInfos = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::updateCustomerInfos] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_customer/update', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form,
                option_value_id: this.value_id
            }
        });
    };

    factory.find = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::find] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/find', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.hasGuestMode = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::hasGuestMode] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/hasguestmode', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.getOrderHistory = function (offset) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::getOrderHistory] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/getorders', {
            urlParams: {
                value_id: this.value_id,
                offset: offset
            },
            cache: false
        });
    };

    factory.getOrderDetails = function (order_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesCustomer::getOrderDetails] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_customer/getorderdetails', {
            urlParams: {
                value_id: this.value_id,
                order_id: order_id
            },
            cache: false
        });
    };


    return factory;
});
;/*global
    App
 */
angular.module("starter").factory("McommerceSalesDelivery", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.findStore = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesDelivery::findStore] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_sales_delivery/findstore", {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };
    
    factory.updateDeliveryInfos = function (form) {

        if (!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesDelivery::updateDeliveryInfos] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_delivery/update", {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form
            }
        });
    };
    
    return factory;
});
;angular.module('starter').factory('McommerceSalesPayment', function ($pwaRequest, $session) {
    var factory = {
        value_id: null,
        notes: ''
    };

    factory.findPaymentMethods = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::findPaymentMethods] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_payment/findpaymentmethods', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.findOnlinePaymentUrl = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::findOnlinePaymentUrl] missing value_id.');
        }

        return $pwaRequest.get('mcommerce/mobile_sales_payment/findonlinepaymenturl', {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.updatePaymentInfos = function (form) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::updatePaymentInfos] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/update', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                form: form
            }
        });
    };

    factory.validatePayment = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::validatePayment] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/validatepayment', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                validate_payment: 1,
                customer_uuid: $session.getDeviceUid(),
                notes: factory.notes || '' // TG-459
            }
        });
    };

    factory.validateOnlinePayment = function (token, payer_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[McommerceSalesPayment::validateOnlinePayment] missing value_id.');
        }

        return $pwaRequest.post('mcommerce/mobile_sales_payment/validatepayment', {
            urlParams: {
                value_id: this.value_id
            },
            data: {
                token: token,
                PayerID: payer_id,
                payer_id: payer_id,
                is_ajax: 1
            }
        });
    };

    return factory;
});
;/*global
    App
 */
angular.module("starter").factory("McommerceSalesStorechoice", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.find = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesStorechoice::find] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_sales_storechoice/find", {
            urlParams: {
                value_id: this.value_id
            },
            cache: false
        });
    };

    factory.update = function(store_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceSalesStorechoice::update] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_storechoice/update", {
            data: {
                store_id: store_id
            }
        });
    };
    
    return factory;
});
;/*global
    App
 */
angular.module("starter").factory("McommerceStripe", function($pwaRequest) {

    var factory = {
        value_id: null
    };

    factory.find = function(cust_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::find] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/find", {
            data: {
                customer_id: cust_id
            }
        });
    };

    /**
     * @todo remove sessionStorage.getItem('mcommerce-notes') and use the pwa-cache registry
     *
     * @param data
     */
    factory.process = function(data) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::process] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/process", {
            data: {
                value_id        : this.value_id,
                token           : data["token"],
                customer_id     : data["customer_id"],
                use_stored_card : data["use_stored_card"],
                save_card       : data["save_card"],
                notes           : sessionStorage.getItem('mcommerce-notes') || ""
            }
        });
    };

    factory.getCard = function(customer_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::getCard] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/getcard", {
            data: {
                "value_id": this.value_id,
                "customer_id": customer_id
            }
        });
    };

    factory.removeCard = function(customer_id) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceStripe::removeCard] missing value_id.");
        }

        return $pwaRequest.post("mcommerce/mobile_sales_stripe/removecard", {
            data: {
                "value_id": this.value_id,
                "customer_id": customer_id
            }
        });
    };

    return factory;
});
