/*global
 App, angular, BASE_PATH, _
 */

angular.module('starter').controller('MCommerceCartViewController', function ($scope, $state, Loader, $stateParams,
                                                                              $translate, $timeout, Dialog,
                                                                              McommerceCart, Customer) {

    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        page_title: $translate.instant("Cart"),
        points_data: {
            use_points: false,
            nb_points_used: null
        },
        nb_stores: null,
        object: {
            cart: null
        },
    });

    McommerceCart.value_id = $stateParams.value_id;

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
                            angular.isObject($scope.object.cart) &&
                                (!angular.isString(data.cart.discount_code) ||
                                 data.cart.discount_code.trim().length < 1)
                        ) {
                            data.cart.discount_code = $scope.object.cart.discount_code;
                        }

                        $scope.object.cart = data.cart;

                        $scope.object.cart.discount_message = $scope.computation.message;
                        $scope.object.cart.discount = $scope.computation.discount;

                        $scope.nb_stores = data.nb_stores;

                        if ($scope.object.cart.lines.length > 0) {
                            $scope.right_button = {
                                action: $scope.proceed,
                                label: $translate.instant('Proceed')
                            };
                        }
                    }).then(function () {
                        Customer.find()
                            .then(function (data) {
                                $scope.object.cart.customer_fidelity_points = (data.metadatas && data.metadatas.fidelity_points) ?
                                    data.metadatas.fidelity_points.points : null;
                                if (!$scope.points_data.use_points) {
                                    $scope.points_data.nb_points_used = $scope.object.cart.customer_fidelity_points;
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
            $scope.object.cart.estimated_fidelity_discount =
                (Math.round($scope.points_data.nb_points_used * $scope.object.cart.fidelity_rate * 100)/100) +
                ' ' + $scope.object.cart.currency_code;
        }
    };

    $scope.useFidelityChange = function () {
        if ($scope.points_data.use_points) {
            $scope.object.cart.discount_code = null;
            $scope.updateTipAndDiscount();
        }
    };

    $scope._update = _.debounce(function () {
        Loader.show('Updating price');

        $scope.is_loading = true;
        McommerceCart.adddiscount($scope.object.cart.discount_code, true)
            .then(function () {
                McommerceCart.addTip($scope.object.cart)
                    .then(function (data) {
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
                        Loader.hide();
                        $scope.is_loading = false;
                    });
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('', data.message, 'OK');
                }
            }).then(function () {
                Loader.hide();
                $scope.is_loading = false;
            });
    }, 900);

    $scope.updateTipAndDiscount = function () {
        $scope._update();
    };

    $scope.proceed = function () {
        Loader.show();

        var gotToNext = function () {
            if (!$scope.object.cart.valid) {
                $scope.cartIdInvalid();
            } else if ($scope.nb_stores > 1) {
                $scope.goToStoreChoice();
            } else {
                $scope.goToOverview();
            }
        };

        if ($scope.object.cart && $scope.object.cart.discount_code) {
            McommerceCart.adddiscount($scope.object.cart.discount_code, true)
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
                    Dialog.alert("Sorry", "You don't have enough points", 'OK');
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
        Dialog.alert("Sorry", $scope.object.cart.valid_message, "OK", 3700);
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
        McommerceCart
        .deleteLine(line.id)
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
                $timeout(function () {
                    $scope.object.cart.formattedSubtotalExclTax = data.cart.formattedSubtotalExclTax;
                    $scope.object.cart.formattedDeliveryCost = data.cart.formattedDeliveryCost;
                    $scope.object.cart.formattedTotalExclTax = data.cart.formattedTotalExclTax;
                    $scope.object.cart.formattedTotalTax = data.cart.formattedTotalTax;
                    $scope.object.cart.formattedTotal = data.cart.formattedTotal;
                    $scope.object.cart.deliveryCost = data.cart.deliveryCost;
                    $scope.object.cart.valid = data.cart.valid;
                });

                return data;
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show();
                }
            }).then(function (data) {
                var scopeLineIndex = _.findIndex($scope.object.cart.lines, function (line) {
                    return line.id == data.line.id;
                });

                $timeout(function () {
                    $scope.object.cart.lines[scopeLineIndex] = data.line;

                    Loader.hide();
                    $scope.is_loading = false;
                });

                return data;
            }).catch(function () {
                Loader.hide();
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();
});
