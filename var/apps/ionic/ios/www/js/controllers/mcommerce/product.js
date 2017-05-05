App.config(function ($stateProvider) {

    $stateProvider.state('mcommerce-product-view', {
        url: BASE_PATH+"/mcommerce/mobile_product/index/value_id/:value_id/product_id/:product_id",
        controller: 'MCommerceProductViewController',
        templateUrl: "templates/mcommerce/l1/product.html",
        cache:false
    })

}).controller('MCommerceProductViewController', function ($cordovaSocialSharing, $ionicLoading, $ionicPopup, $log, $state, $stateParams, $scope, $translate, Analytics, Application, Dialog, McommerceCategory, McommerceCart, McommerceProduct) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

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
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });

        McommerceProduct.find($scope.product_id).success(function (data) {
            $scope.product = data.product;

            Analytics.storeProductOpening($scope.product);

            $scope.social_sharing_active = !!($scope.product.social_sharing_active == 1 && !Application.is_webview);

            $scope.share = function () {

                // Fix for $cordovaSocialSharing issue that opens dialog twice
                if($scope.is_sharing) return;

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

        }).finally(function () {
            $scope.is_loading = false;
            $ionicLoading.hide();
        });
    };

    $scope.addProduct = function () {

        $scope.is_loading = true;
        $ionicLoading.show({
            template: "<ion-spinner class=\"spinner-custom\"></ion-spinner>"
        });

        var errors = new Array();

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
                        selected.push(e.id)
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
            McommerceCart.addProduct(postParameters).success(function (data) {
                if (data.success) {
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
                    $ionicLoading.hide();
                    $scope.openCart();
                }
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert("", data.message, $translate.instant("OK"));
                }
                $scope.is_loading = false;
                $ionicLoading.hide();
            });
        } else {
            var message = errors.join("<br/>");
            Dialog.alert("", message, $translate.instant("OK"));

            $scope.is_loading = false;
            $ionicLoading.hide();
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

});