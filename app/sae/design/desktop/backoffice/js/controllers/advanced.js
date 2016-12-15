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

    $scope.moduleAction = function(module, action) {
        $scope.form_loader_is_visible = true;

        Advanced.moduleAction(module, action).success(function(data) {

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



}).controller("BackofficeAdvancedConfigurationController", function($scope, $timeout, $interval, Label, Header, AdvancedConfiguration, FileUploader, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.hostname = "";
    $scope.show_upload = false;

    AdvancedConfiguration.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
        $scope.hostname = data.hostname;
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

    $scope.show_force = false;

    $scope.generateSsl = function(hostname, force) {
        $scope.form_loader_is_visible = true;

        AdvancedConfiguration.generateSsl(hostname, force).success(function(data) {

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

            $scope.show_force = data.show_force;

        }).error(function(data) {

            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.message.setText(message)
                .isError(true)
                .show()
            ;

            $scope.show_force = false;

        }).finally(function() {
            $scope.form_loader_is_visible = false;

            $timeout(function() {
                location.reload();
            }, 500);
        });

        return false;
    };

    $scope.form = {
        hostname: "",
        cert_path: "",
        ca_path: "",
        private_path: "",
        upload: "0"
    };

    $scope.uploaders = new Array(
        {type : "cert_path",    uploader : "cert_path"},
        {type : "ca_path",      uploader : "ca_path"},
        {type : "private_path", uploader : "private_path"}
    );

    for (var i = 0; i < $scope.uploaders.length; i++) {
        var code = $scope.uploaders[i].uploader;
        $scope[code] = new FileUploader({
            url: Url.get("backoffice/advanced_configuration/uploadcertificate?code="+code)
        });

        $scope[code].filters.push({
            name: 'limit',
            fn: function(item, options) {
                return this.queue.length < 1;
            }
        });

        $scope[code].onWhenAddingFileFailed = function(item, filter, options) {
            if(filter.name == "limit") {
                $scope.message.setText(Label.uploader.error.only_one_at_a_time).isError(true).show();
            }
        };

        $scope[code].onAfterAddingFile = function(item, filter, options) {
            item.upload();
        };

        $scope[code].onSuccessItem = function(fileItem, response, status, headers) {
            if(angular.isObject(response) && response.success) {

                $scope.form[response.code] = response.tmp_path;
                console.log($scope.form);

            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;
            }
        };

        $scope[code].onErrorItem = function(fileItem, response, status, headers) {
            $scope.message.setText(response.message)
                .isError(true)
                .show()
            ;
        };
    }

    $scope.disable_form = false;
    $scope.createCertificate = function() {
        if(!$scope.disable_form) {
            $scope.disable_form = true;

            AdvancedConfiguration.createCertificate($scope.form).success(function(data) {

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

                $scope.configs.certificates = data.certificates;

                $scope.form = {
                    hostname: "",
                    cert_path: "",
                    ca_path: "",
                    private_path: "",
                    upload: "0"
                };

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
                $scope.disable_form = false;
            });
        }
    };

    $scope.removeCertificate = function(confirm, id) {
        if(window.confirm(confirm)) {
            AdvancedConfiguration.removeCertificate(id).success(function(data) {

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

                $scope.configs.certificates = data.certificates;

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
    }



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
