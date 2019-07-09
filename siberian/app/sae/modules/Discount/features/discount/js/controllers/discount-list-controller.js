/**
 * Discount
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 *
 */
angular
    .module("starter")
    .controller("DiscountListController", function ($filter, Modal, $location,
                                                                         $rootScope, $scope, $state, $stateParams,
                                                                         $timeout, $translate, $window, Application,
                                                                         Customer, Dialog, Discount, Url, SB, Loader,
                                                                         $ionicSlideBoxDelegate, SocialSharing, Tc) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        is_logged_in: Customer.isLoggedIn(),
        sharingIsActive: false,
        load_more: false,
        cardDesign: false,
        use_pull_refresh: true,
        collection: [],
        pull_to_refresh: false,
        current_index: 0
    });

    Discount.setValueId($stateParams.value_id);

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent(true);
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.is_logged_in = false;
        $scope.loadContent(true);
    });

    $scope.gotoPage = function (index) {
        $scope.current_index = index;
        $ionicSlideBoxDelegate.$getByHandle('slideBoxDiscount').slide(index);
    };

    $scope.repeatDone = function () {
        $ionicSlideBoxDelegate.$getByHandle('slideBoxDiscount').update();
        $ionicSlideBoxDelegate.$getByHandle('slideBoxDiscount').slide(0);
    };

    $scope.loadContent = function (pullToRefresh) {
        $scope.is_loading = true;

        Discount.findAll(true)
            .then(function (data) {
                $scope.collection = angular.copy(data.promotions);

                // Chunks for L5!
                $scope.collection_chunks = $filter('chunk')($scope.collection, 2);
                $scope.tc_id = data.tc_id;
                $scope.sharingIsActive = (data.social_sharing_is_active && isNativeApp);
                $scope.page_title = data.page_title;

                return data;
            }, function (error) {
                // Do nothing!
            }).then(function (data) {
                if ($scope.pull_to_refresh) {
                    $scope.$broadcast('scroll.refreshComplete');
                    $scope.pull_to_refresh = false;
                }

                $scope.is_loading = false;

                // Preload discounts, and T&Cs
                if (data.tc_id) {
                    Tc.find(data.tc_id, pullToRefresh);
                }

                angular.forEach(data.promotions, function (promotion) {
                    Discount.find(promotion.id, pullToRefresh);
                });

                $ionicSlideBoxDelegate.$getByHandle('slideBoxDiscount').update();
            });
    };

    $scope.getState = function () {
       return (!$scope.is_loading && $scope.collection && !$scope.collection.length) ? 'NO_RESULTS' : 'RESULTS';
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.loadContent(true);
    };

    $scope.share = function () {
        var content = $scope.collection[$scope.current_index].title ? $scope.collection[$scope.current_index].title : null;
        var message = 'I just found this discount $1 in $2 app.';

        SocialSharing.share(content, message);
    };

    $scope.login = function () {
        Customer.loginModal($scope);
    };

    $scope.confirmBeforeUse = function (discount_id) {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        var buttons = ['Yes', 'No'];

        Dialog.confirm('Confirmation', $scope.modal_title, buttons, 'text-center')
            .then(function (result) {
                if (result) {
                    $scope.use(discount_id);
                }
            });
    };

    $scope.use = function (discount_id) {
        Loader.show();

        Discount.use(discount_id)
            .then(function (data) {
                Dialog.alert('Thank you', data.message, 'OK', -1);
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK', -1);
            }).then(function () {
                Loader.hide();
                $scope.loadContent(true);
            });
    };

    /**
     * @todo this should be a service !
     */
    $scope.openScanCamera = function () {
        if (!Application.is_webview) {
            $scope.scan_protocols = ['sendback:'];

            if (!$scope.is_logged_in) {
                $scope.login();
            } else {
                $scope.showScanCamera();
            }
        } else {
            Dialog.alert('Info', 'This will open the code scan camera on your device.', 'OK');
        }
    };

    $scope.showScanCamera = function () {
        cordova.plugins.barcodeScanner
        .scan()
        .then(function (barcodeData) {
            if (!barcodeData.cancelled && (barcodeData.text !== '')) {
                $timeout(function () {
                    $scope.is_loading = true;

                    var qrcode = barcodeData.text.replace('sendback:', '');

                    // Load data!
                    Discount.unlockByQRCode(qrcode)
                        .then(function (data) {
                            for (var i = 0; i < $scope.collection.length; i++) {
                                if ($scope.collection[i].id == data.promotion.id) {
                                    $scope.collection[i] = data.promotion;
                                    console.log($scope.collection[i]);
                                    break;
                                }
                            }

                            $state.go('discount-view', {
                                value_id: $scope.value_id,
                                promotion_id: data.promotion.id
                            });

                            $scope.is_loading = false;
                        }, function (data) {
                            var message_text = 'An error occurred while reading the code.';
                            if (angular.isObject(data)) {
                                message_text = data.message;
                            }

                            Dialog.alert('Error', message_text, 'OK', -1);
                        }).then(function () {
                            $scope.is_loading = false;
                        });
                });
            }
        }, function (error) {
            Dialog.alert('Error', 'An error occurred while reading the code.', 'OK', -1);
        });
    };

    $scope.showItem = function (item) {
        if (item.is_locked) {
            $scope.openScanCamera();
        } else {
            $state.go('discount-view', {
                value_id: $scope.value_id,
                promotion_id: item.id
            });
        }
    };

    $scope.showTc = function () {
        $state.go('tc-view', {
            tc_id: $scope.tc_id
        });
    };

    $scope.loadContent(false);
});
