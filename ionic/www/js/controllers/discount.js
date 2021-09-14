/**
 * Discount, QR Discount controllers
 */
angular
    .module('starter')
    .controller('DiscountListController', function ($cordovaBarcodeScanner, $filter, Modal, $location, $rootScope,
                                                    $scope, $state, $stateParams, $timeout, $translate, $window,
                                                    Application, Customer, Dialog, Discount, Url, SB, Loader,
                                                    $ionicSlideBoxDelegate, SocialSharing, Tc, Codescan) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        is_logged_in: Customer.isLoggedIn(),
        social_sharing_active: false,
        load_more: false,
        card_design: false,
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
                $scope.social_sharing_active = (data.social_sharing_is_active && isNativeApp);
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
        var buttons = ['Yes', 'No'];

        Dialog
            .confirm('Confirmation', $scope.modal_title, buttons, 'text-center')
            .then(function (result) {
                if (result) {
                    $scope.use(discount_id);
                }
            });
    };

    $scope.use = function (discount_id) {
        Loader.show();

        Discount
            .use(discount_id)
            .then(function (data) {
                Dialog.alert('Thank you', data.message, 'OK', -1);
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK', -1);
            }).then(function () {
                Loader.hide();
                $scope.loadContent(true);
            });
    };

    $scope.scanCoupon = function () {
        Codescan.scanDiscount();
    };

    $scope.showItem = function (item) {
        if (item.is_locked) {
            $scope.scanCoupon();
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
}).controller('DiscountViewController', function ($ionicHistory, Modal,
                                                 $rootScope, $scope, $state, $stateParams, $timeout, $translate,
                                                 $window, Application, Customer, Dialog, Discount, Url, SB, SocialSharing,
                                                 Loader) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id,
        is_logged_in: Customer.isLoggedIn(),
        card_design: false,
        use_pull_to_refresh: false,
        social_sharing_active: false
    });

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.is_logged_in = false;
        $scope.loadContent();
    });

    Discount.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Discount
            .find($stateParams.promotion_id)
            .then(function (data) {
                $scope.promotion = data.promotion;
                $scope.modal_title = data.confirm_message;
                $scope.tc_id = data.tc_id;
                $scope.social_sharing_active = data.social_sharing_is_active;
                $scope.page_title = data.page_title;
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.share = function () {
        SocialSharing.share();
    };

    $scope.login = function () {
        Customer.loginModal($scope);
    };

    $scope.confirmBeforeUse = function () {
        var buttons = ['Yes', 'No'];

        Dialog
            .confirm('Confirmation', $scope.modal_title, buttons, 'text-center')
            .then(function (result) {
                if (result) {
                    $scope.use();
                }
            });
    };

    $scope.use = function () {
        Loader.show();

        Discount
            .use($stateParams.promotion_id)
            .then(function (data) {
                Dialog
                    .alert('Thank you', data.message, 'OK', -1)
                    .then(function () {
                        if (data.remove) {
                            Discount
                                .findAll(true)
                                .then(function () {
                                    $ionicHistory.goBack();
                                });
                        }
                    });
            }, function (data) {
                Dialog
                    .alert('Error', data.message, 'OK', -1)
                    .then(function () {
                        if (data.remove) {
                            Discount
                                .findAll(true)
                                .then(function () {
                                    $ionicHistory.goBack();
                                });
                        }
                    });
            }).then(function () {
                Loader.hide();
            });
    };

    $scope.showTc = function () {
        $state.go('tc-view', {
            tc_id: $scope.tc_id
        });
    };

    $scope.loadContent();
});
