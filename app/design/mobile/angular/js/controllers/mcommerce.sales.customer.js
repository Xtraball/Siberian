App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/mcommerce/mobile_sales_customer/index/value_id/:value_id", {
        controller: 'MCommerceSalesCustomerViewController',
        templateUrl: BASE_URL + "/mcommerce/mobile_sales_customer/template",
        code: "mcommerce-sales-customer"
    });

}).controller('MCommerceSalesCustomerViewController', function ($scope, $routeParams, $location, McommerceCart, McommerceSalesCustomer, Message, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    McommerceCart.value_id = $routeParams.value_id;
    McommerceSalesCustomer.value_id = $routeParams.value_id;
    $scope.value_id = $routeParams.value_id;
    
    $scope.loadContent = function () {
        McommerceCart.find().success(function (data) {
            if(parseInt(data.cart.require_address)==1)
			    {data.cart.require_address_star=' *';data.cart.require_address_text=' required';} 
				else {data.cart.require_address_star='';data.cart.require_address_text='';}
            if(parseInt(data.cart.age_control)==1)
			    {data.cart.age_control_star=' *';data.cart.age_control_text=' required'; data.cart.age_control_style='';} 
				else {data.cart.age_control_star='';data.cart.age_control_text=''; data.cart.age_control_style='display:none';}
			$scope.cart = data.cart;
			if($scope.cart.customer.birthday!='') 
			  {$scope.cart.customer.birthday = new Date($scope.cart.customer.birthday);}
        }).finally(function () {
            $scope.is_loading = false;
        });
    };
    
    $scope.goToDeliveryPage = function () {
        $location.path(Url.get("mcommerce/mobile_sales_delivery/index", {
            value_id: $routeParams.value_id
        }));
    }

    $scope.updateCustomerInfos = function () {

        if(!$scope.is_loading) {
            $scope.is_loading = true;
            var postParameters = {
                'product_id': $scope.product_id,
                'customer': $scope.cart.customer
            };

            McommerceSalesCustomer.updateCustomerInfos(postParameters).success(function (data) {
                $scope.goToDeliveryPage();
            }).error(function (data) {
                if (data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show();
                }

                $scope.is_loading = false;
            });
        }
    };

    $scope.header_right_button = {
        action: $scope.updateCustomerInfos,
        title: "Next"
    };

    $scope.loadContent();

});