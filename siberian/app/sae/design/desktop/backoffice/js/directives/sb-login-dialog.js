App.directive('sbLoginDialog', function ($templateCache, AUTH_EVENTS, Auth) {
    return {
        restrict: 'E',
        template: '<div ng-include src="\'loginForm.html\'"></div>',
        replace: true,
        controller: function($rootScope, $scope) {

            $scope.form_loader_is_visible = false;
            $scope.credentials = {email: "", password: ""};
            $scope.show_forgotpassword_form = false;
            $scope.loginForm = {};

            $scope.login = function() {

                $scope.form_loader_is_visible = true;
                Auth.login($scope.credentials).success(function() {

                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);

                }).error(function(data) {
                    if(data.message) {
                        $rootScope.message.isError(1)
                            .setText(data.message)
                            .show()
                        ;
                    }
                }).finally(function() {
                    $scope.form_loader_is_visible = false;
                });
            };

            $scope.forgottenPassword = function() {

                $scope.form_loader_is_visible = true;

                Auth.forgottenPassword($scope.credentials.email).success(function(data) {
                    $scope.show_forgotten_password_form = false;
                    $scope.message.isError(0)
                        .setText(data.message)
                        .show()
                    ;
                }).error(function(data) {
                    if(data.message) {
                        $rootScope.message.isError(1)
                            .setText(data.message)
                            .show()
                        ;
                    }
                }).finally(function() {
                    $scope.form_loader_is_visible = false;
                });
            };

        }
    };
});