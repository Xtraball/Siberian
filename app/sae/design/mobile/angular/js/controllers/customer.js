App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/customer/mobile_account_login", {
        controller: 'CustomerLoginController',
        templateUrl: BASE_URL+"/customer/mobile_account_login/template",
        code: "customer_account"
    }).when(BASE_URL+"/customer/mobile_account_register", {
        controller: 'CustomerRegisterController',
        templateUrl: BASE_URL+"/customer/mobile_account_register/template",
        code: "customer_account"
    }).when(BASE_URL+"/customer/mobile_account_edit", {
        controller: 'CustomerEditController',
        templateUrl: BASE_URL+"/customer/mobile_account_edit/template",
        code: "customer_account"
    }).when(BASE_URL+"/customer/mobile_account_forgottenpassword", {
        controller: 'CustomerForgottenPasswordController',
        templateUrl: BASE_URL+"/customer/mobile_account_forgottenpassword/template",
        code: "customer_account"
    });

}).controller('CustomerLoginController', function($window, $rootScope, $scope, $facebook, $routeParams, Application, Message, Customer) {

    $rootScope.app_loader_is_visible = false;
    $scope.can_connect_with_facebook = !!(Customer.can_connect_with_facebook && Application.handle_facebook_connect);

    $scope.customer = {};

    $scope.post = function() {

        $scope.loginForm.submitted = true;

        if ($scope.loginForm.$valid) {

            $scope.customer.device_uid = Application.device_uid;

            Customer.login($scope.customer).success(function(data) {
                if(data.success) {
                    $window.history.back();
                }
            }).error(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally();
        }
    };

    $scope.loginWithFacebook = function() {
        $facebook.login().then(function(response) {
            if(response.authResponse && response.authResponse.accessToken) {
                $window.checkFacebookLoginStatus(response.authResponse.accessToken);
            }
        });
    };

    $window.checkFacebookLoginStatus = function() {

        $rootScope.app_loader_is_visible = true;

        var unbindFbListener = $scope.$on("fb.auth.authResponseChange", function(event, response, FB) {

            if(response.status == "connected") {

                Customer.loginWithFacebook(response.authResponse.accessToken, Application.device_uid).then(function(response) {
                    $rootScope.app_loader_is_visible = false;
                    if(response.data.success) {
                        $window.history.back();
                    }
                }, function() {
                    console.log("Customer not logged in");
                    $rootScope.app_loader_is_visible = false;
                });

            }

            unbindFbListener();
        });

        $facebook.init();

    };

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

}).controller('CustomerRegisterController', function($window, $rootScope, $scope, $routeParams, Message, Customer) {

    $rootScope.app_loader_is_visible = false;

    $scope.post = function() {

        $scope.registerForm.submitted = true;

        if ($scope.registerForm.$valid) {

            $scope.customer.device_uid = Application.device_uid;

            Customer.register($scope.customer).success(function(data) {
                if(data.success) {
                    $window.history.go(-2);
                }
            }).error(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally();
        }
    };

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

}).controller('CustomerForgottenPasswordController', function($window, $rootScope, $scope, $routeParams, Message, Customer) {

    $rootScope.app_loader_is_visible = false;

    $scope.post = function() {

        $scope.forgottenpasswordForm.submitted = true;

        if ($scope.forgottenpasswordForm.$valid) {

            Customer.forgottenpassword($scope.email).success(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.setText(data.message)
                        .show()
                    ;
                }
            }).error(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally();
        }
    };

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

}).controller('CustomerEditController', function($window, $rootScope, $scope, $facebook, $routeParams, Message, Customer) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $rootScope.app_loader_is_visible = true;

    $scope.loadContent = function() {
        Customer.find().success(function(customer) {
            $scope.customer = customer;
        }).finally(function() {
            $rootScope.app_loader_is_visible = false;
        });
    };

    $scope.post = function() {

        $scope.editForm.submitted = true;

        if ($scope.editForm.$valid) {

            Customer.save($scope.customer).success(function(data) {
                if(angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.setText(data.message)
                        .show()
                    ;
                }
            }).error(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally();
        }
    };

    $scope.logout = function() {
        Customer.logout().success(function(data) {
            if(data.success) {
                $window.history.back();
            }
        });
    };

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

    $scope.loadContent();

});