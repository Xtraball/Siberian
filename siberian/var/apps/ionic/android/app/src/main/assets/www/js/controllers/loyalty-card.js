/* global
 angular, BASE_PATH
 */

angular.module('starter').controller('LoyaltyViewController', function ($cordovaBarcodeScanner, Modal, $rootScope,
                                                                       $scope, $state, $stateParams, $timeout,
                                                                       $translate, $window, Application, Customer,
                                                                       Dialog, LoyaltyCard, Url, SB, Tc, Pages, Loader) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        is_logged_in: Customer.isLoggedIn(),
        is_card: true,
        card_design: false
    });

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent(true);
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.is_logged_in = false;
        $scope.loadContent(true);
    });

    LoyaltyCard.setValueId($stateParams.value_id);

    /**
     * This var is here to show the unlock by qrcode section in pad template
     * Because this template could be used in other feature without qrcode validation
     *
     * @todo check usage, behavior, and what can be improved
     * @type {boolean}
     */
    $scope.unlock_by_qrcode = true;

    $scope.pad = {
        password: '',
        points: [],
        number_of_points: 0,
        add: function (nbr) {
            if (this.password.length < 4) {
                this.password += nbr;
                if (this.password.length === 4) {
                    $scope.validate();
                }
            }
            return this;
        },
        remove: function () {
            this.password = this.password.substr(0, this.password.length - 1);
            return this;
        }
    };

    /**
     *
     * @param refresh no used for now, until new ...
     */
    $scope.loadContent = function (refresh) {
        Loader.show();
        $scope.is_loading = true;

        // Force refresh for now!
        var localRefresh = false;
        if ($rootScope.isOnline) {
            localRefresh = true;
        }

        LoyaltyCard.findAll(localRefresh)
            .then(function (data) {
                $scope.promotions = data.promotions;
                $scope.card = data.card;
                $scope.pictos = data.picto_urls;
                $scope.card_is_locked = data.card_is_locked;
                $scope.points = data.points;
                $scope.page_title = data.page_title;
                $scope.tc_id = data.tc_id;
                $scope.is_card = ($scope.card.id !== false);

                return data;
            }).then(function (data) {
                Loader.hide();
                $scope.is_loading = false;
                Tc.find(data.tc_id);
            });
    };

    $scope.openPad = function (card) {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        if (!Customer.isLoggedIn()) {
            Customer.loginModal($scope);
            return;
        }

        $scope.pad.modal = {};
        $scope.pad.password = '';
        $scope.pad.points = [];
        $scope.pad.buttons = [];
        $scope.pad.mode_qrcode = false;
        for (var i = 1; i < 10; i++) {
            $scope.pad.buttons.push(i);
        }
        $scope.pad.buttons.push(0);

        $scope.pad.card = card;
        $scope.pad.number_of_points = 1;
        $scope.page_title = $scope.pad_title;
        if ($scope.page_title === '' || $scope.page_title === undefined) {
            $scope.page_title = 'Enter password';
        }

        var remaining = card.max_number_of_points - card.number_of_points;
        var points = [];
        for (var i = 0; i <= remaining-1; i = i + 1) {
            points[i] = i+1;
        }

        if (isNaN(remaining) || remaining === 0) {
            $scope.page_title = 'Use this coupon';
        }

        $scope.pad.points = points;

        Modal
            .fromTemplateUrl('templates/loyalty-card/l1/pad.html', {
                scope: $scope
            })
            .then(function (modal) {
                $scope.pad.modal = modal;
                $scope.pad.modal.show();
            });
    };

    $scope.closePad = function () {
        $scope.pad.modal.hide();
    };

    $scope.$on('$destroy', function () {
        if ($scope.pad.modal) {
            $scope.pad.modal.hide();
        }
    });

    $scope.validate = function () {
        LoyaltyCard.validate($scope.pad)
            .then(function (data) {
                if (data) {
                    if (data.message) {
                        Dialog.alert('Success', data.message, 'OK', -1)
                            .then(function () {
                                if (data.close_pad) {
                                    $scope.closePad();
                                } else {
                                    $scope.pad.password = '';
                                }

                                if (data.number_of_points) {
                                    $scope.card.number_of_points = data.number_of_points;
                                } else if (data.promotion_id_to_remove) {
                                    for (var i in $scope.promotions) {
                                        if ($scope.promotions[i].id == data.promotion_id_to_remove) {
                                            $scope.promotions.splice(i, 1);
                                        }
                                    }
                                }

                                if (data.customer_card_id) {
                                    $scope.card.id = data.customer_card_id;
                                }

                                return true;
                            }).then(function () {
                                Pages.refresh();
                                $scope.loadContent(true);
                            });
                    }
                }
            }, function (data) {
                if (data && data.message) {
                    Dialog.alert('Error', data.message, 'OK', -1)
                        .then(function () {
                            if (data.close_pad) {
                                $scope.closePad();
                                if (data.card_is_locked) {
                                    $scope.card_is_locked = true;
                                }
                            } else {
                                $scope.pad.password = '';
                            }
                        });
                }

                if (data.customer_card_id) {
                    $scope.card.id = data.customer_card_id;
                }
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.login = function () {
        Customer.loginModal($scope);
    };

    $scope.openScanCamera = function () {
        if (!Application.is_webview) {
            $scope.scan_protocols = ['sendback:'];

            if (!Customer.isLoggedIn()) {
                Customer.loginModal($scope, function () {
                    $scope.showScanCamera();
                });
            } else {
                $scope.showScanCamera();
            }
        } else {
            Dialog.alert('Info', 'This will open the code scan camera on your device.', 'OK', -1);
        }
    };

    $scope.showScanCamera = function () {
        $cordovaBarcodeScanner.scan().then(function (barcodeData) {
            if (!barcodeData.cancelled && (barcodeData.text !== '')) {
                $timeout(function () {
                    $scope.good_qr_code = false;
                    for (var i = 0; i < $scope.scan_protocols.length; i++) {
                        if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) == 0) {
                            $scope.good_qr_code = true;
                            $scope.is_loading = true;

                            var qrcode = barcodeData.text.replace($scope.scan_protocols[i], '');
                            $scope.pad.password = qrcode;
                            $scope.pad.mode_qrcode = true;
                            $scope.validate();
                            break;
                        }
                    }

                    if (!$scope.good_qr_code) {
                        Dialog.alert('Info', 'Unreadable QRCode, sorry.', 'OK', -1);
                    }
                });
            }
        }, function (error) {
            Dialog.alert('Error', 'An error occurred while reading the code.', 'OK', -1);
        });
    };

    /**
     * @todo overview ...
     */
    if ($scope.isOverview) {
        $window.prepareDummy = function () {
            $timeout(function () {
                $scope.card = { id: -1, is_visible: true };
                $scope.points = [];
            });
        };

        $window.setAttributeToDummy = function (attribute, value) {
            $timeout(function () {
                $scope.card[attribute] = value;
            });
        };

        $window.setNumberOfPoints = function (nbr) {
            $timeout(function () {
                var points = [];
                for (var i = 0; i < nbr; i++) {
                    points.push({
                        is_validated: false,
                        image_url: $scope.pictos.normal_url
                    });
                }
                $scope.card.max_number_of_points = nbr;
                $scope.points = points;
            });
        };

        $scope.$on('$destroy', function () {
            $window.prepareDummy = null;
            $window.setAttributeToDummy = null;
            $window.setNumberOfPoints = null;
        });
    }

    $scope.showTc = function () {
        $state.go('tc-view', {
            tc_id: $scope.tc_id
        });
    };

    $scope.loadContent();
});
