App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/loyaltycard/mobile_view/index/value_id/:value_id", {
        controller: 'LoyaltyController',
        templateUrl: BASE_URL+"/loyaltycard/mobile_view/template",
        code: "loyalty"
    });

}).controller('LoyaltyController', function($window, $rootScope, $scope, $routeParams, $location, Url, CACHE_EVENTS, Message, Customer, Loyalty) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    Customer.onStatusChange("loyalty", [
        Url.get("loyaltycard/mobile_view/findall", {value_id: $routeParams.value_id})
    ]);

    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Loyalty.value_id = $routeParams.value_id;
    $scope.pad = {
        show: false,
        password: "",
        points: new Array(),
        number_of_points: 0,
        show_points_selector: false,
        add: function(nbr) {
            if(this.password.length < 4) {

                this.password += nbr;
                if(this.password.length == 4) {
                    $scope.validate();
                }

            }
            return this;
        },
        remove: function() {
            this.password = this.password.substr(0, this.password.length - 1);
            return this;
        }
    }

    $scope.loadContent = function() {

        Loyalty.findAll().success(function(data) {
            $scope.promotions = data.promotions;
            $scope.card = data.card;
            $scope.picto_urls = data.picto_urls;
            $scope.card_is_locked = data.card_is_locked;
            $scope.points = data.points;
            $scope.page_title = data.page_title;
            $scope.default_page_title = data.page_title;
            $scope.pad_title = data.pad_title;
            $scope.tc_id = data.tc_id;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.openPad = function(card) {

        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        if(!Customer.isLoggedIn()) {
            $location.path(Url.get("customer/mobile_account_login"));
            return this;
        }
        $scope.pad.password = "";
        $scope.pad.points = new Array();
        $scope.pad.card = card;
        $scope.pad.number_of_points = 1;
        $scope.pad.show_points_selector = false;
        $scope.page_title = $scope.pad_title;

        var remaining = card.max_number_of_points - card.number_of_points;
        var points = new Array();
        for(var i = 0; i <= remaining-1; i++) {
            points[i] = i+1;
        }

        $scope.pad.points = points;
        $scope.pad.show = true;
    }

    $scope.closePad = function() {
        $scope.page_title = $scope.default_page_title;
        $scope.pad.show = false;
    }

    $scope.validate = function() {

        Loyalty.validate($scope.pad).success(function(data) {

            if(data && data.message) {
                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;

                if(data.close_pad) {
                    $scope.closePad();
                } else {
                    $scope.pad.password = "";
                }

                if(data.points) {
                    $scope.card.number_of_points = data.number_of_points;
                } else if(data.promotion_id_to_remove) {
                    for(var i in $scope.promotions) {
                        if($scope.promotions[i].id == data.promotion_id_to_remove) {
                            $scope.promotions.splice(i, 1);
                        }
                    }
                } else {
                    $scope.loadContent();
                }

            }

            $rootScope.$broadcast(CACHE_EVENTS.clearSocialGaming);

        }).error(function(data) {

            if(data && data.message) {
                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;

                if(data.close_pad) {
                    $scope.closePad();
                    if(data.card_is_locked) {
                        $scope.card_is_locked = true;
                    }
                } else {
                    $scope.pad.password = "";
                }

                if(data.customer_card_id) {
                    $scope.card.id = data.customer_card_id;
                }
            }

        }).finally(function() {

        });
    };

    $scope.login = function() {
        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        $location.path(Url.get("customer/mobile_account_login"));
    };

    if($scope.isOverview) {

        $window.prepareDummy = function() {
            $scope.card = {is_visible: true};
            $scope.points = new Array();
            $scope.$apply();
        };

        $window.setAttributeToDummy = function(attribute, value) {
            $scope.card[attribute] = value;
            $scope.$apply();
        }

        $window.setNumberOfPoints = function(nbr) {

            var points = new Array();
            for(var i = 0; i < nbr; i++) {
                points.push({
                    is_validated: false,
                    image_url: $scope.picto_urls.normal_url
                });
            };

            console.log(points);
            $scope.points = points;
            $scope.$apply();
        }

        $scope.$on("$destroy", function() {
            $window.prepareDummy = null;
            $window.setAttributeToDummy = null;
            $window.setNumberOfPoints = null;
        });
    }

    $scope.showTc = function() {
        $location.path(Url.get("application/mobile_tc_view/index", {tc_id: $scope.tc_id}));
    };

    $scope.loadContent();

});