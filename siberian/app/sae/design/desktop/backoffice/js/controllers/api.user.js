App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/api/backoffice_user_list", {
        controller: 'ApiUserListController',
        templateUrl: BASE_URL+"/api/backoffice_user_list/template"
    }).when(BASE_URL+"/api/backoffice_user_edit", {
        controller: 'ApiUserEditController',
        templateUrl: BASE_URL+"/api/backoffice_user_edit/template"
    }).when(BASE_URL+"/api/backoffice_user_edit/user_id/:user_id", {
        controller: 'ApiUserEditController',
        templateUrl: BASE_URL+"/api/backoffice_user_edit/template"
    });

}).controller("ApiUserListController", function($scope, $location, Header, SectionButton, Label, ApiUser) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.button = new SectionButton(function() {
        $location.path("api/backoffice_user_edit");
    });

    ApiUser.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    ApiUser.findAll().success(function(data) {
        $scope.users = data.users;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.delete = function(user) {

        if(confirm(Label.admin.confirm_deletion)) {
            user.loader_is_visible = true;
            ApiUser.delete(user.id).success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                var users = $scope.users;
                $scope.users = new Array();
                for(var i in users) {
                    if(users[i].id != user.id) {
                        $scope.users.push(users[i]);
                    }
                }
            }).error(function(data) {
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;
                user.loader_is_visible = false;
            });
        }
    };

}).controller("ApiUserEditController", function($scope, $location, $routeParams, Header, ApiUser, Url, Label) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.button.left.action = function() {
        $location.path(Url.get("api/backoffice_user_list"));
    };
    $scope.content_loader_is_visible = true;

    $scope.user = {
        id: null,
        change_password: false
    };

    ApiUser.loadEditData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    ApiUser.find($routeParams.user_id).success(function(data) {
        $scope.user = data.user ? data.user : $scope.user;
        $scope.section_title = data.section_title;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.save = function() {

        $scope.form_loader_is_visible = true;

        if($scope.user.id && !$scope.user.change_password) {
            $scope.user.password = $scope.user.confirm_password = null;
        }

        ApiUser.save($scope.user).success(function(data) {
            $location.path("api/backoffice_user_list");
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
        }).error(function(data) {
            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.form_loader_is_visible = false;
        });
    };

});
