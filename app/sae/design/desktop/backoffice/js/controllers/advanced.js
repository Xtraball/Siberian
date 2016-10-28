App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice/advanced_module", {
        controller: 'BackofficeAdvancedController',
        templateUrl: BASE_URL+"/backoffice/advanced_module/template"
    }).when(BASE_URL+"/backoffice/advanced_configuration", {
        controller: 'BackofficeAdvancedConfigurationController',
        templateUrl: BASE_URL+"/backoffice/advanced_configuration/template"
    }).when(BASE_URL+"/backoffice/advanced_tools", {
        controller: 'BackofficeAdvancedToolsController',
        templateUrl: BASE_URL+"/backoffice/advanced_tools/template"
    }).when(BASE_URL+"/backoffice/advanced_cron", {
        controller: 'BackofficeAdvancedCronController',
        templateUrl: BASE_URL+"/backoffice/advanced_cron/template"
    });

}).controller("BackofficeAdvancedController", function($scope, $interval, Header, Advanced) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;

    Advanced.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.content_loader_is_visible = true;
    Advanced.findAll().success(function(data) {
        $scope.modules = data.modules;
        $scope.core_modules = data.core_modules;
        $scope.layouts = data.layouts;
        $scope.icons = data.icons;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.moduleAction = function(action) {
        $scope.form_loader_is_visible = true;

        Advanced.moduleAction(action).success(function(data) {

            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            }

            $scope.message.setText(message)
                .show()
            ;
        }).error(function(data) {
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



}).controller("BackofficeAdvancedConfigurationController", function($scope, $timeout, $interval, Label, Header, AdvancedConfiguration) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;

    AdvancedConfiguration.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.content_loader_is_visible = true;
    AdvancedConfiguration.findAll().success(function(data) {
        $scope.configs = data;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.save = function() {

        $scope.form_loader_is_visible = true;

        AdvancedConfiguration.save($scope.configs).success(function(data) {

            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            } else {
                $scope.message.isError(true);
            }
            $scope.message.setText(message)
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

            $timeout(function() {
                location.reload();
            }, 500);
        });
    };



}).controller("BackofficeAdvancedToolsController", function($scope, $interval, Header, AdvancedTools) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;

    AdvancedTools.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.content_loader_is_visible = true;

    $scope.test_integrity = function() {
        $scope.content_loader_is_visible = true;
        AdvancedTools.runtest()
            .success(function(data) {
                $scope.integrity_result = data;
            }).finally(function() {

            $scope.content_loader_is_visible = false;
        });
    };

}).controller("BackofficeAdvancedCronController", function($scope, $interval, Header, AdvancedConfiguration, AdvancedCron) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;

    AdvancedCron.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.content_loader_is_visible = true;

    AdvancedConfiguration.findAll().success(function(data) {
        $scope.configs = data;
    }).finally(function() {});

    AdvancedCron.findAll().success(function(data) {
        $scope.system_tasks = data.system_tasks;
        $scope.tasks = data.tasks;
        $scope.apk_queue = data.apk_queue;
        $scope.source_queue = data.source_queue;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.save = function() {

        $scope.form_loader_is_visible = true;

        AdvancedConfiguration.save($scope.configs).success(function(data) {

            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            } else {
                $scope.message.isError(true);
            }
            $scope.message.setText(message)
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

            $timeout(function() {
                location.reload();
            }, 500);
        });
    };

});
