App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('discount-list', {
        url: BASE_PATH+"/promotion/mobile_list/index/value_id/:value_id",
        controller: 'DiscountListController',
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l2"; break;
                case "3": layout_id = "l5"; break;
                case "4": layout_id = "l6"; break;
                case "1":
                default: layout_id = "l3";
            }
            return 'templates/html/'+layout_id+'/list.html';
        }
    }).state('discount-view', {
        url: BASE_PATH+"/promotion/mobile_view/index/value_id/:value_id/promotion_id/:promotion_id",
        controller: 'DiscountViewController',
        templateUrl: "templates/discount/l1/view.html"
    });

}).controller('DiscountListController', function($cordovaBarcodeScanner, $cordovaSocialSharing, $filter, $ionicModal, $rootScope, $scope, $state, $stateParams, $timeout, $translate, $window, Application, Customer, Dialog, Discount, Url, AUTH_EVENTS, CACHE_EVENTS) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });
    $scope.$on(AUTH_EVENTS.logoutSuccess, function() {
        $scope.is_logged_in = false;
        $scope.loadContent();
    });

    $scope.social_sharing_active = false;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Discount.value_id = $stateParams.value_id;

    $rootScope.$on(CACHE_EVENTS.clearDiscount, function(event, args) {
        $scope.remove(args.discount_id);
    });

    $scope.loadContent = function() {

        Discount.findAll().success(function(data) {

            $scope.collection = data.promotions;
            /** Chunks for L5 */
            $scope.collection_chunks = $filter("chunk")($scope.collection, 2);

            $scope.tc_id = data.tc_id;

            $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && $scope.collection.length > 0 && !Application.is_webview);

            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.share = function () {

        // Fix for $cordovaSocialSharing issue that opens dialog twice
        if($scope.is_sharing) return;

        $scope.is_sharing = true;

        var app_name = Application.app_name;
        var link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;
        var subject = "";
        var file = "";
        var content = $scope.collection[$scope.carousel_index].title ? $scope.collection[$scope.carousel_index].title:null;
        var message = $translate.instant("Hi. I just found: $1 in the $2 app.").replace("$1", content).replace("$2", app_name);

        $cordovaSocialSharing
            .share(message, subject, file, link) // Share via native share sheet
            .then(function (result) {
                console.log("succes");
                $scope.is_sharing = false;
            }, function (err) {
                console.log(err);
                $scope.is_sharing = false;
            });
    };

    $scope.login = function() {
        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        $ionicModal.fromTemplateUrl('templates/customer/account/l1/login.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            Customer.modal = modal;
            Customer.modal.show();
        });

    };

    $scope.use = function(discount_id) {

        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }
        Discount.use(discount_id).success(function(data) {

            $scope.message = new Message();
            $scope.message.setText(data.message)
                .show()
            ;

            if(data.remove) {
                $rootScope.$broadcast(CACHE_EVENTS.clearDiscount, { discount_id: discount_id });
            }

        }).error(function(data) {

            if(data) {

                if(angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

                if(data.remove) {
                    $scope.remove(discount_id);
                }
            }

        }).finally();

    };

    $scope.remove = function(discount_id) {
        for(var i = 0; i < $scope.collection.length; i++) {
            if($scope.collection[i].id == discount_id) {
                $scope.collection.splice(i, 1);
            }
        }
    };

    $scope.openScanCamera = function() {

        if(!Application.is_webview) {
            $scope.scan_protocols = ["sendback:"];

            if (!$scope.is_logged_in) {
                $ionicModal.fromTemplateUrl('templates/customer/account/l1/login.html', {
                    scope: $scope,
                    animation: 'slide-in-up'
                }).then(function (modal) {
                    Customer.modal = modal;
                    Customer.modal.show();
                });

                $scope.$on('modal.hidden', function () {
                    $scope.is_logged_in = Customer.isLoggedIn();
                    $scope.showScanCamera();
                });
            } else {
                $scope.showScanCamera();
            }
        } else {
            Dialog.alert($translate.instant("Info"), $translate.instant("This will open the code scan camera on your device."), $translate.instant("OK"));
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
                            Discount.unlockByQRCode(qrcode).success(function(data) {

                                for(var i = 0; i < $scope.collection.length; i++) {
                                    if($scope.collection[i].id == data.promotion.id) {
                                        $scope.collection[i] = data.promotion;
                                        console.log($scope.collection[i]);
                                        break;
                                    }
                                }

                                $state.go("discount-view", { value_id: $scope.value_id, promotion_id: data.promotion.id });
                                $scope.is_loading = false;

                            }).error(function (data) {

                                var message_text = $translate.instant('An error occurred while reading the code.');
                                if(angular.isObject(data)) {
                                    message_text = data.message;
                                }

                                Dialog.alert($translate.instant("Error"), message_text, $translate.instant("OK"));

                            }).finally(function () {
                                $scope.is_loading = false;
                            });

                            break;
                        }
                    }

                });

            }

        }, function(error) {
            Dialog.alert($translate.instant("Error"), $translate.instant('An error occurred while reading the code.'), $translate.instant("OK"));
        });
    };

    if($rootScope.isOverview) {

        $scope.dummy = {};
        $scope.dummy.is_dummy = true;

        $window.prepareDummy = function() {
            //var hasDummy = false;
            //for(var i in $scope.collection) {
            //    if($scope.collection[i].is_dummy) {
            //        hasDummy = true;
            //    }
            //}
            //
            //if(!hasDummy) {
            //    $timeout(function() {
            //        $scope.collection.unshift($scope.dummy);
            //    });
            //}
        };

        $window.setAttributeToDummy = function(attribute, value) {
            //$timeout(function() {
            //    $scope.dummy[attribute] = value;
            //});
        };

        $scope.$on("$destroy", function() {
            $scope.prepareDummy = null;
            $scope.setAttributeToDummy = null;
        });

    }

    $scope.showItem = function(item) {
        if(item.is_locked) {
            $scope.openScanCamera();
        } else {
            $state.go("discount-view", {value_id: $scope.value_id, promotion_id: item.id});
        }
    };

    $scope.showTc = function() {
        $state.go("tc-view", {tc_id: $scope.tc_id});
    };

    $scope.loadContent();

}).controller('DiscountViewController', function($cordovaSocialSharing, $ionicHistory, $ionicModal, $ionicPopup, $rootScope, $scope, $state, $stateParams, $timeout, $translate, $window, Application, Customer, Dialog, Discount, Url, AUTH_EVENTS, CACHE_EVENTS) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });
    $scope.$on(AUTH_EVENTS.logoutSuccess, function() {
        $scope.is_logged_in = false;
        $scope.loadContent();
    });

    $scope.social_sharing_active = false;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Discount.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        Discount.find($stateParams.promotion_id).success(function(data) {

            $scope.promotion = data.promotion;

            $scope.modal_title = data.confirm_message;
            $scope.tc_id = data.tc_id;

            $scope.social_sharing_active = !!(data.social_sharing_is_active == 1 && !Application.is_webview);

            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.share = function () {

        // Fix for $cordovaSocialSharing issue that opens dialog twice
        if($scope.is_sharing) return;

        $scope.is_sharing = true;

        var app_name = Application.app_name;
        var link = DOMAIN + "/application/device/downloadapp/app_id/" + Application.app_id;
        var subject = "";
        var file = "";
        var content = $scope.promotion.title;
        var message = $translate.instant("Hi. I just found: $1 in the $2 app.").replace("$1", content).replace("$2", app_name);

        $cordovaSocialSharing
            .share(message, subject, file, link) // Share via native share sheet
            .then(function (result) {
                console.log("success");
                $scope.is_sharing = false;
            }, function (err) {
                console.log(err);
                $scope.is_sharing = false;
            });
    };

    $scope.login = function() {

        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        $ionicModal.fromTemplateUrl('templates/customer/account/l1/login.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            Customer.modal = modal;
            Customer.modal.show();
        });

    };
    
    $scope.confirmBeforeUse = function () {

        if ($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        var buttons = [$translate.instant("Cancel"), $translate.instant("OK")];

        Dialog.confirm("", $scope.modal_title, buttons, "text-center").then(function (res) {

            if (Application.is_webview) {
                if (res) {
                    $scope.use();
                }
            } else {
                if (res == 2) {
                    $scope.use();
                }
            }
        });

    };

    $scope.use = function() {

        Discount.use($stateParams.promotion_id).success(function(data) {

            $scope.is_loading = true;

            Dialog.alert("", data.message, $translate.instant("OK"));

            if(data.remove) {
                $rootScope.$broadcast(CACHE_EVENTS.clearDiscount, { discount_id: $stateParams.promotion_id });
                $ionicHistory.goBack();
            }

        }).error(function(data) {

            if(data) {

                if(angular.isDefined(data.message)) {
                    Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
                }

                if(data.remove) {
                    $rootScope.$broadcast(CACHE_EVENTS.clearDiscount, { discount_id: $stateParams.promotion_id });
                    $ionicHistory.goBack();
                }
            }

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showTc = function() {
        $state.go("tc-view", { tc_id: $scope.tc_id });
    };

    $scope.loadContent();

});