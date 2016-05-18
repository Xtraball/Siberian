App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice/account_list", {
        controller: 'AccountListController',
        templateUrl: BASE_URL+"/backoffice/account_list/template"
    }).when(BASE_URL+"/backoffice/account_view/user_id/:user_id", {
        controller: 'AccountViewController',
        templateUrl: BASE_URL+"/backoffice/account_view/template"
    }).when(BASE_URL+"/backoffice/account_view", {
        controller: 'AccountViewController',
        templateUrl: BASE_URL+"/backoffice/account_view/template"
    });

}).controller("AccountListController", function($scope, $location, Header, Message, SectionButton, Account) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.button = new SectionButton(function() {
        $location.path("backoffice/account_view");
    });

    Account.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Account.findAll().success(function(data) {
        $scope.users = data.users;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.delete = function(user) {

        user.loader_is_visible = true;
        Account.delete(user.id).success(function(data) {
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

}).controller("AccountViewController", function($scope, $location, $routeParams, Header, Account, Message, Url, Label) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.button.left.action = function() {
        $location.path(Url.get("backoffice/account_list"));
    };
    $scope.content_loader_is_visible = true;

    Account.loadViewData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Account.find($routeParams.user_id).success(function (data) {
        $scope.user = data.user ? data.user : {};
        $scope.section_title = data.section_title;
    }).finally(function () {
        $scope.content_loader_is_visible = false;
    });

    $scope.saveUser = function() {

        $scope.form_loader_is_visible = true;

        if($scope.user.id && !$scope.user.change_password) {
            $scope.user.password = $scope.user.confirm_password = null;
        }

        Account.save($scope.user).success(function(data) {
            $location.path("backoffice/account_list");
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
    }

});
