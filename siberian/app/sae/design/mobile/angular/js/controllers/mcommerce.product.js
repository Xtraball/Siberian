App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_product/index/value_id/:value_id/product_id/:product_id", {
        controller: 'MCommerceProductViewController',
        templateUrl: BASE_URL + "/mcommerce/mobile_product/template",
        code: "mcommerce-product"
    });

}).controller('MCommerceProductViewController', function ($scope, $routeParams, $location, McommerceProduct, McommerceCart, Message, Url, Pictos, Application) {

    $scope.truncateDescription = true;

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    McommerceProduct.value_id = $routeParams.value_id;
    McommerceCart.value_id = $routeParams.value_id;
    $scope.value_id = $routeParams.value_id;

    $scope.product_id = $routeParams.product_id;

    $scope.product_quantity = 1;

    $scope.selected_format = {id:null};

    $scope.loadContent = function () {
        McommerceProduct.find($scope.product_id).success(function (data) {
            data.product.optionsGroups = data.product.optionsGroups.reduce(function (optionsGroups, optionsGroup) {
                // default quantity: 1
                optionsGroup.selectedQuantity = 1;
                optionsGroups.push(optionsGroup);
                return optionsGroups;
            }, []);

            $scope.product = data.product;

            if($scope.product.social_sharing_active==1 && Application.handle_social_sharing) {
                $scope.share_picto = {
                    picto_url: Pictos.get("share", "header"),
                    action: function() {
                        $scope.sharing_data = {
                            "page_name": $scope.product.name,
                            "picture": $scope.product.picture?$scope.product.picture:null,
                            "content_url": null
                        }
                        Application.socialShareData($scope.sharing_data);
                    }
                };
            }

            if($scope.product.formatGroups.length > 0) {
                $scope.selected_format.id = $scope.product.formatGroups[0].id;
            }
            $scope.page_title = data.page_title;
        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.addProduct = function () {

        $scope.productForm.submitted = true;

        if ($scope.productForm.$valid) {

            $scope.is_loading = true;

            var postParameters = {
                'product_id': $scope.product_id,
                'qty': $scope.product_quantity,
                'options': $scope.product.optionsGroups.reduce(function (options, optionsGroup) {
                    options[optionsGroup.id] = {
                        'option_id': optionsGroup.selectedOptionId,
                        'qty': optionsGroup.selectedQuantity
                    };
                    return options;
                }, {}),
                'selected_format': $scope.selected_format.id
            };

            McommerceCart.addProduct(postParameters).success(function (data) {
                if (data.success) {
                    $scope.is_loading = false;
                    $scope.openCart();
                }
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show();
                }
                $scope.is_loading = false;
            });
        } else {
            $scope.message = new Message();
            $scope.message.isError(true)
                .setText(angular.isDefined(Translator.translations["Some mandatory fields are empty."])?Translator.translations["Some mandatory fields are empty."]:"Some mandatory fields are empty.")
                .show();
        }
    };

    $scope.openCart = function () {

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            $location.path(Url.get("mcommerce/mobile_cart/index", {
                value_id: $routeParams.value_id
            }));
        }
    };

    $scope.changeQuantity = function(qty) {
        if(qty) {
            $scope.product_quantity = qty;
        }
    };

    $scope.header_right_button = {
        action: $scope.openCart,
        title: "Cart"
    };

    $scope.loadContent();

});