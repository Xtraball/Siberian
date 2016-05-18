App.controller('CustomerController', function($cordovaOauth, $ionicScrollDelegate, $rootScope, $scope, $translate, $window, Application, Customer, Dialog, FacebookConnect, HomepageLayout) {

    $scope.can_connect_with_facebook = !!Customer.can_connect_with_facebook;

    $scope.customer = {};

    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.app_name = Application.app_name;

    $scope.display_login_form = !$scope.is_logged_in;
    $scope.display_account_form = $scope.is_logged_in;

    HomepageLayout.getData().then(function(data) {
        if(data.customer_account.name) {
            $scope.page_title = data.customer_account.name;
        }
    });
    $scope.closeLoginModal = function() {
        Customer.modal.hide();
    };

    $scope.login = function() {

        $scope.is_loading = true;
        Customer.login($scope.customer).success(function(data) {
            if(data.success) {
                $scope.closeLoginModal();
            }
        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loginWithFacebook = function() {
        if(typeof IS_PREVIEWER !== 'undefined' && angular.isDefined(IS_PREVIEWER)) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }
        FacebookConnect.login();
    };

    $scope.forgotPassword = function() {

        $scope.is_loading = true;

        Customer.forgottenpassword($scope.customer.email).success(function(data) {
            if(data && angular.isDefined(data.message)) {
                Dialog.alert("", data.message, $translate.instant("OK"));

                if(data.success) {
                    $scope.displayLoginForm();
                }
            }
        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.loadContent = function() {

        if(!$scope.is_logged_in) return;

        $scope.is_loading = true;
        Customer.find().success(function(customer) {
            $scope.customer = customer;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.save = function() {

        $scope.is_loading = true;

        Customer.save($scope.customer).success(function(data) {
            if(angular.isDefined(data.message)) {
                Dialog.alert("", data.message, $translate.instant("OK"));
            }

            if(data.success) {
                $scope.closeLoginModal();
            }

        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                Dialog.alert($translate.instant("Error"), data.message, $translate.instant("OK"));
            }

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.logout = function() {
        Customer.logout().success(function(data) {
            FacebookConnect.logout();
            if(data.success) {
                $scope.closeLoginModal();
            }
        });
    };

    $scope.displayLoginForm = function() {
        $scope.display_forgot_password_form = false;
        $scope.display_account_form = false;
        $scope.display_privacy_policy = false;
        $scope.display_login_form = true;
    };

    $scope.displayForgotPasswordForm = function() {
        $scope.display_login_form = false;
        $scope.display_account_form = false;
        $scope.display_privacy_policy = false;
        $scope.display_forgot_password_form = true;
    };

    $scope.displayAccountForm = function() {
        $scope.display_login_form = false;
        $scope.display_forgot_password_form = false;
        $scope.display_privacy_policy = false;
        $scope.display_account_form = true;
    };

    $scope.displayPrivacyPolicy = function(from) {
        $scope.displayed_from = from || '';
        $scope.display_login_form = false;
        $scope.display_forgot_password_form = false;
        $scope.display_account_form = false;
        $scope.display_privacy_policy = true;
    };

    $scope.scrollTop = function() {
        $ionicScrollDelegate.scrollTop(false);
    };

    $scope.loadContent();

});