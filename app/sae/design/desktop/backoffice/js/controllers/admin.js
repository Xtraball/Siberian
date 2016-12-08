App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/admin/backoffice_list", {
        controller: 'AdminListController',
        templateUrl: BASE_URL+"/admin/backoffice_list/template"
    }).when(BASE_URL+"/admin/backoffice_edit", {
        controller: 'AdminEditController',
        templateUrl: BASE_URL+"/admin/backoffice_edit/template"
    }).when(BASE_URL+"/admin/backoffice_edit/admin_id/:admin_id", {
        controller: 'AdminEditController',
        templateUrl: BASE_URL+"/admin/backoffice_edit/template"
    }).when(BASE_URL+"/admin/backoffice_export", {
        controller: 'AdminExportController',
        templateUrl: BASE_URL+"/admin/backoffice_export/template"
    });

}).controller("AdminListController", function($scope, $location, Header, SectionButton, Label, Admin) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = false;

    $scope.button = new SectionButton(function() {
        $location.path("admin/backoffice_edit");
    });

    Admin.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    $scope.admins = new Array();

    $scope.perPage = 10;
    $scope.page = 0;
    $scope.clientLimit = 250;

    $scope.urlParams = {
        filter: "",
        order: "admin_id",
        by: true
    };

    $scope.deleteAdmin = function(admin) {

        if(confirm(Label.admin.confirm_deletion)) {
            admin.loader_is_visible = true;
            Admin.delete(admin.id).success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                var admins = $scope.admins;
                $scope.admins = new Array();
                for(var i in admins) {
                    if(admins[i].id != admin.id) {
                        $scope.admins.push(admins[i]);
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

}).controller("AdminEditController", function($scope, $location, $routeParams, Header, Admin, Url, Label, Application) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.button.left.action = function() {
        $location.path(Url.get("admin/backoffice_list"));
    };
    $scope.content_loader_is_visible = true;

    $scope.admin = {
        id: null,
        change_password: false
    };

    Admin.loadEditData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Admin.find($routeParams.admin_id).success(function(data) {
        $scope.admin = data.admin ? data.admin : $scope.admin;
        $scope.section_title = data.section_title;
        $scope.applications_section_title = data.applications_section_title;
        $scope.country_codes = data.country_codes;
        $scope.roles = data.roles;
        $scope.admin.role_id = $routeParams.admin_id ? $scope.admin.role_id : data.default_role_id;

    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.perPage = 10;
    $scope.page = 0;
    $scope.clientLimit = 250;

    $scope.urlParams = {
        admin_id: $routeParams.admin_id,
        show_all_applications: false,
        filter: "",
        order: "app_id",
        by: true
    };


    $scope.saveAdmin = function() {

        $scope.form_loader_is_visible = true;

        if($scope.admin.id && !$scope.admin.change_password) {
            $scope.admin.password = $scope.admin.confirm_password = null;
        }

        Admin.save($scope.admin).success(function(data) {
            $location.path("admin/backoffice_list");
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

    $scope.setAppToAdmin = function(application) {

        if(application.loader_is_visible) {
            application.is_selected = !application.is_selected;
            return;
        }

        application.loader_is_visible = true;

        Admin.setApplication($routeParams.admin_id, application).success(function(data) {
            application.is_allowed_to_add_pages = data.is_allowed_to_add_pages;
        }).error(function() {
            application.is_selected = !application.is_selected;
        }).finally(function() {
            application.loader_is_visible = false;
        });
    };

}).controller("AdminExportController", function($scope, $location, Header, Admin) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = true;

    Admin.loadExportData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    }).finally(function() {
        $scope.header.loader_is_visible = false;
    });

}).filter('showAdminAppsOnly', function() {

    return function( applications, show_admin_apps_only) {

        var filtered = [];
        if(!angular.isDefined(show_admin_apps_only)) {
            show_admin_apps_only = false;
        }
        angular.forEach(applications, function(application) {
            if(!show_admin_apps_only || application.is_selected) {
                filtered.push(application);
            }
        });

        return filtered;
    };

});
