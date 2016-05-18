App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/promotion/mobile_list/index/value_id/:value_id", {
        controller: 'PromotionsListController',
        templateUrl: function(params) {
            return BASE_URL+"/promotion/mobile_list/template/value_id/"+params.value_id;
        },
        code: "promotions"
    }).when(BASE_URL+"/promotion/mobile_view/index/value_id/:value_id/promotion_id/:promotion_id", {
        controller: 'PromotionViewController',
        templateUrl: function(params) {
            return BASE_URL+"/promotion/mobile_view/template/value_id/"+params.value_id;
        },
        code: "promotion-view"
    });

}).controller('PromotionsListController', function($window, $rootScope, $route, $scope, $routeParams, $location, Application, Message, Url, CACHE_EVENTS, Customer, Promotion,  Pictos, httpCache) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.carousel_index = 0;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Promotion.value_id = $routeParams.value_id;
    $scope.handle_code_scan = Application.handle_code_scan?Application.handle_code_scan:null;

    $scope.loadContent = function() {
        
        Promotion.findAll().success(function(data) {

            $scope.collection = data.promotions;
            $scope.tc_id = data.tc_id;

            if(data.social_sharing_is_active==1  && $scope.collection.length > 0  && Application.handle_social_sharing) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.collection[$scope.carousel_index].title ? $scope.collection[$scope.carousel_index].title:null,
                            "picture": null,
                            "content_url": null,
                            "content": null
                        };
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.showBackButton = function() {
        return false;
    };

    $scope.login = function() {
        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        $location.path(Url.get("customer/mobile_account_login"));
    };

    $scope.use = function(promotion_id) {

        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        Promotion.use(promotion_id).success(function(data) {

            $scope.message = new Message();
            $scope.message.setText(data.message)
                .show()
            ;

            if(data.remove) {
                $rootScope.$broadcast(CACHE_EVENTS.clearDiscount);
                $scope.remove(promotion_id);
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
                    $scope.remove(promotion_id);
                }
            }

        }).finally();

    };

    $scope.remove = function(promotion_id) {
        for(var i = 0; i < $scope.collection.length; i++) {
            if($scope.collection[i].id == promotion_id) {
                $scope.collection.splice(i, 1);
            }
        }
    };

    $scope.openScanCamera = function() {

        $window.scan_camera_protocols = JSON.stringify(["sendback:"]);
        Application.openScanCamera({protocols: ["sendback:"]}, function(qrcode) {
            $scope.is_loading = true;

            // load data
            Promotion.unlockByQRCode(qrcode).success(function(data) {

                var url =  Url.get("promotion/mobile_list/findall", {value_id: Promotion.value_id});
                httpCache.remove(url);
                $route.reload();

            }).error(function (data) {

                var message_text = "An error occurred while loading. Please, try again later.";
                if(angular.isObject(data)) {
                    message_text = data.message;
                }

                $scope.message = new Message();
                $scope.message.setText(message_text)
                    .isError(true)
                    .show()
                ;

            }).finally(function () {
                $scope.is_loading = false;
            });
        }, function() {});
    };

    if($scope.isOverview) {
        $scope.dummy = {};
        $scope.dummy.is_dummy = true;

        $window.prepareDummy = function() {
            var hasDummy = false;
            for(var i in $scope.collection) {
                if($scope.collection[i].is_dummy) {
                    hasDummy = true;
                }
            }

            if(!hasDummy) {
                $scope.collection.unshift($scope.dummy);
                $scope.$apply();
                $scope.carousel_index = 0;
                $scope.$apply();
            }
        };

        $window.setAttributeToDummy = function(attribute, value) {
            $scope.dummy[attribute] = value;
            $scope.$apply();
        };

        $scope.$on("$destroy", function() {
            $scope.prepareDummy = null;
            $scope.setAttributeToDummy = null;
        });

    };

    $scope.showItem = function(promotion) {
        $location.path(promotion.url);
    };

    $scope.showTc = function() {
        $location.path(Url.get("application/mobile_tc_view/index", {tc_id: $scope.tc_id}));
    };

    $scope.loadContent();

}).controller('PromotionViewController', function($window, $rootScope, $scope, $routeParams, $location, Message, Url, Customer, Promotion, Pictos, Application, CACHE_EVENTS, modalManager) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Promotion.value_id = $routeParams.value_id;

    $scope.loadContent = function() {

        Promotion.find($routeParams.promotion_id).success(function(data) {

            $scope.promotion = data.promotion;

            $scope.modal_title = data.confirm_message;
            $scope.tc_id = data.tc_id;

            if(data.social_sharing_is_active) {
                $scope.header_right_button = {
                    picto_url: Pictos.get("share", "header"),
                    hide_arrow: true,
                    action: function () {
                        $scope.sharing_data = {
                            "page_name": $scope.collection[$scope.carousel_index].title,
                            "picture": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    },
                    height: 25
                };
            }

            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.login = function() {
        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        $location.path(Url.get("customer/mobile_account_login"));
    }

    $scope.confirmBeforeUse = function() {

        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }

        var modal = {
            "title": $scope.modal_title,
            "show_cancel": "true",
            "ok_label": "Confirm",
            "confirmAction": function() {return $scope.use();}
        };

        modalManager.instances.push(modal);
        modalManager.show();


    };

    $scope.use = function() {

        Promotion.use($routeParams.promotion_id).success(function(data) {

            $scope.is_loading = true;
            $scope.message = new Message();
            $scope.message.setText(data.message)
                .show()
            ;

            if(data.remove) {
                $rootScope.$broadcast(CACHE_EVENTS.clearDiscount);
                $window.history.back();
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
                    $rootScope.$broadcast(CACHE_EVENTS.clearDiscount);
                    $window.history.back();
                }
            }

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showTc = function() {
        $location.path(Url.get("application/mobile_tc_view/index", {tc_id: $scope.tc_id}));
    };

    $scope.loadContent();

});