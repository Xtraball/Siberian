/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("DiscountListController", function($cordovaBarcodeScanner, $filter, Modal,
                                                                        $rootScope, $scope, $state, $stateParams,
                                                                        $timeout, $translate, $window, Application,
                                                                        Customer, Dialog, Discount, Url, SB,
                                                                        SocialSharing, Tc) {

    angular.extend($scope, {
        is_loading              : false,
        value_id                : $stateParams.value_id,
        is_logged_in            : Customer.isLoggedIn(),
        social_sharing_active   : false,
        load_more               : false,
        card_design             : false,
        use_pull_refresh        : true,
        collection              : [],
        pull_to_refresh         : false
    });

    Discount.setValueId($stateParams.value_id);

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function() {
        $scope.is_logged_in = true;
        $scope.loadContent(true);
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function() {
        $scope.is_logged_in = false;
        $scope.loadContent(true);
    });

    $rootScope.$on(SB.EVENTS.CACHE.clearDiscount, function(event, args) {
        $scope.remove(args.discount_id);
    });

    $scope.loadContent = function(pullToRefresh) {

        $scope.is_loading = true;

        Discount.findAll(pullToRefresh)
            .then(function(data) {

                $scope.collection = angular.copy(data.promotions);

                /** Chunks for L5 */
                $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
                $scope.tc_id = data.tc_id;
                $scope.social_sharing_active = (data.social_sharing_is_active && $rootScope.isNativeApp);
                $scope.page_title = data.page_title;

                return data;

            }, function(error) {

            }).then(function(data) {

                if($scope.pull_to_refresh) {
                    $scope.$broadcast('scroll.refreshComplete');
                    $scope.pull_to_refresh = false;
                }

                $scope.is_loading = false;

                /** Preload discounts, and T&Cs */
                if(data.tc_id) {
                    Tc.find(data.tc_id, pullToRefresh);
                }

                angular.forEach(data.promotions, function(promotion) {
                    Discount.find(promotion.id, pullToRefresh);
                });
            });
    };

    $scope.getState = function() {
       return (!$scope.is_loading && $scope.collection && !$scope.collection.length) ? "NO_RESULTS" : "RESULTS";
    };

    $scope.pullToRefresh = function() {
        $scope.pull_to_refresh = true;
        $scope.loadContent(true);
    };

    $scope.share = function () {

        var content = $scope.collection[$scope.carousel_index].title ? $scope.collection[$scope.carousel_index].title:null;
        var message = "I just found this discount $1 in $2 app.";

        SocialSharing.share(content, message);
    };

    $scope.login = function() {
        Customer.loginModal($scope);
    };

    $scope.use = function(discount_id) {

        if($rootScope.isNotAvailableInOverview()) {
            return;
        }

        Discount.use(discount_id)
            .then(function(data) {

                Dialog.alert("Success", data.message, "OK", -1);

                if(data.remove) {
                    $rootScope.$broadcast(SB.EVENTS.CACHE.clearDiscount, {
                        discount_id: discount_id
                    });
                }

            }, function(data) {

                if(data) {

                    if(angular.isDefined(data.message)) {
                        Dialog.alert("Error", data.message, "OK", -1);
                    }

                    if(data.remove) {
                        $scope.remove(discount_id);
                    }
                }

            });

    };

    /**
     * Clean-up collection, should refresh cache too.
     *
     * @param discount_id
     */
    $scope.remove = function(discount_id) {

        $timeout(function() {
            _.remove($scope.collection, function(discount) {
                return ((discount.id * 1) === (discount_id * 1));
            });

            Discount.findAll();
        }, 1);

    };

    /**
     * @todo this should be a service !
     */
    $scope.openScanCamera = function() {

        if(!Application.is_webview) {
            $scope.scan_protocols = ["sendback:"];

            if (!$scope.is_logged_in) {
                $scope.login();
            } else {
                $scope.showScanCamera();
            }

        } else {
            Dialog.alert("Info", "This will open the code scan camera on your device.", "OK");
        }

    };

    $scope.showScanCamera = function() {
        $cordovaBarcodeScanner.scan().then(function(barcodeData) {

            if(!barcodeData.cancelled && barcodeData.text != "") {

                $timeout(function () {
                    for (var i = 0; i < $scope.scan_protocols.length; i++) {
                        if (barcodeData.text.toLowerCase().indexOf($scope.scan_protocols[i]) == 0) {
                            $scope.is_loading = true;

                            var qrcode = barcodeData.text.replace($scope.scan_protocols[i], "");

                            // load data
                            Discount.unlockByQRCode(qrcode)
                                .then(function(data) {

                                    for(var i = 0; i < $scope.collection.length; i++) {
                                        if($scope.collection[i].id == data.promotion.id) {
                                            $scope.collection[i] = data.promotion;
                                            console.log($scope.collection[i]);
                                            break;
                                        }
                                    }

                                    $state.go("discount-view", {
                                        value_id: $scope.value_id,
                                        promotion_id: data.promotion.id
                                    });

                                    $scope.is_loading = false;

                                }, function (data) {

                                    var message_text = "An error occurred while reading the code.";
                                    if(angular.isObject(data)) {
                                        message_text = data.message;
                                    }

                                    Dialog.alert("Error", message_text, "OK", -1);

                                }).then(function () {
                                    $scope.is_loading = false;
                                });

                            break;
                        }
                    }

                });

            }

        }, function(error) {
            Dialog.alert("Error", "An error occurred while reading the code.", "OK", -1);
        });
    };

    if($rootScope.isOverview) {

        $scope.dummy = {};
        $scope.dummy.is_dummy = true;

        $window.prepareDummy = function() {};

        $window.setAttributeToDummy = function(attribute, value) {};

        $scope.$on("$destroy", function() {
            $scope.prepareDummy = null;
            $scope.setAttributeToDummy = null;
        });

    }

    $scope.showItem = function(item) {
        if(item.is_locked) {
            $scope.openScanCamera();
        } else {
            $state.go("discount-view", {
                value_id: $scope.value_id,
                promotion_id: item.id
            });
        }
    };

    $scope.showTc = function() {
        $state.go("tc-view", {
            tc_id: $scope.tc_id
        });
    };

    $scope.loadContent(false);

}).controller("DiscountViewController", function($cordovaSocialSharing, $ionicHistory, Modal, $ionicPopup,
                                                 $rootScope, $scope, $state, $stateParams, $timeout, $translate,
                                                 $window, Application, Customer, Dialog, Discount, Url, SB, SocialSharing,
                                                 Loader) {


    angular.extend($scope, {
        is_loading                  : false,
        value_id                    : $stateParams.value_id,
        is_logged_in                : Customer.isLoggedIn(),
        card_design                 : false,
        use_pull_to_refresh         : false,
        social_sharing_active       : false
    });

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function() {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function() {
        $scope.is_logged_in = false;
        $scope.loadContent();
    });

    Discount.setValueId($stateParams.value_id);

    $scope.loadContent = function() {

        Discount.find($stateParams.promotion_id)
            .then(function(data) {

                $scope.promotion = data.promotion;

                $scope.modal_title = data.confirm_message;
                $scope.tc_id = data.tc_id;

                $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && !Application.is_webview);

                $scope.page_title = data.page_title;

            }).then(function() {
                $scope.is_loading = false;
            });
    };

    $scope.share = function() {
        SocialSharing.share();
    };

    $scope.login = function() {
        Customer.loginModal($scope);
    };
    
    $scope.confirmBeforeUse = function () {

        if($rootScope.isNotAvailableInOverview()) {
            return;
        }

        var buttons = ["Yes", "No"];

        Dialog.confirm("Confirmation", $scope.modal_title, buttons, "text-center")
            .then(function (result) {
                if(result) {
                    $scope.use();
                }
            });

    };

    $scope.use = function() {

        Loader.show();

        Discount.use($stateParams.promotion_id)
            .then(function(data) {

                Dialog.alert("Thank you", data.message, "OK", -1)
                    .then(function() {

                        if(data.remove) {
                            Discount.findAll(true)
                                .then(function() {
                                    $rootScope.$broadcast(SB.EVENTS.CACHE.clearDiscount, {
                                        discount_id: $stateParams.promotion_id
                                    });
                                    $ionicHistory.goBack();
                                });
                        }
                    });

            }, function(data) {

                Dialog.alert("Error", data.message, "OK", -1)
                    .then(function() {

                        if(data.remove) {
                            Discount.findAll(true)
                                .then(function() {
                                    $rootScope.$broadcast(SB.EVENTS.CACHE.clearDiscount, {
                                        discount_id: $stateParams.promotion_id
                                    });
                                    $ionicHistory.goBack();
                                });
                        }
                    });

            }).then(function() {
                Loader.hide();
            });

    };

    $scope.showTc = function() {
        $state.go("tc-view", {
            tc_id: $scope.tc_id
        });
    };

    $scope.loadContent();

});