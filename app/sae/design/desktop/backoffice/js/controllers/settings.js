App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/system/backoffice_config_general", {
        controller: 'SettingsController',
        templateUrl: BASE_URL+"/system/backoffice_config_general/template",
        code: "general"
    }).when(BASE_URL+"/system/backoffice_config_email", {
        controller: 'SettingsController',
        templateUrl: BASE_URL+"/system/backoffice_config_email/template",
        code: "email"
    }).when(BASE_URL+"/system/backoffice_config_design", {
        controller: 'SettingsController',
        templateUrl: BASE_URL+"/system/backoffice_config_design/template",
        code: "design"
    });

}).controller("SettingsController", function($scope, Header, Label, Settings, Url, FileUploader, LicenseService) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    $scope.design_message = "";
    $scope.is_flat_design = false;
    $scope.iosBuildActivationRemain = false;
    $scope.iosBuildLicenceError = '';
    $scope.iosBuildLicenceInfo = '';
    $scope.generateAnalyticsPeriod = {'from':'','to':'','from_displayed_date':'','to_displayed_date':''};
    $scope.test = {
        email: ""
    };

    Settings.type = $scope.code;

    Settings.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
        $scope.translated_messages = data.message;
    });

    Settings.findAll().success(function(configs) {

        if(configs.countries) {
            $scope.countries = configs.countries;
            delete configs.countries;
        }

        $scope.configs = configs;

        $scope.is_flat_design = $scope.configs.editor_design.value == "flat" ? true : false;

        if($scope.code == "design") {
            $scope.designs = configs.designs;
            $scope.prepareDesignUploaders();
            $scope.Change_Design();
        }

        //we check license info on config sucees
        if(configs.ios_autobuild_key && configs.ios_autobuild_key.value !== "") {
            LicenseService.getIosBuildLicenseInfo(configs.ios_autobuild_key.value).then(function(infos) {
                $scope.iosBuildActivationRemain = infos.remainingBuild;
                $scope.iosBuildLicenceError = infos.errorMessage;
            },function(reason){
                $scope.iosBuildActivationRemain = 'n/a';
                $scope.iosBuildLicenceError = 'We cannot get your license information';
            });
        } else {
            $scope.iosBuildActivationRemain = 'n/a';
            $scope.iosBuildLicenceError = '';
        }

    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.save = function() {

        $scope.form_loader_is_visible = true;

        Settings.save($scope.configs).success(function(data) {

            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            } else {
                $scope.message.isError(true);
            }
            $scope.message.setText(message)
                .show();
            window.location.href = window.location.href;
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

    $scope.testemail = function() {

        $scope.form_loader_is_visible = true;

        Settings.testemail($scope.test.email).success(function(data) {

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
        });

    };



    if($scope.code == "design") {
        var codes = ["logo", "favicon"];
        for (var i = 0; i < codes.length; i++) {
            var code = codes[i];
            $scope[code + "_uploader"] = new FileUploader({
                url: Url.get("system/backoffice_config_design/upload")
            });
        }
    }

    $scope.prepareDesignUploaders = function() {

        for(var i = 0; i < codes.length; i++) {

            var code = codes[i];
            $scope[code+"_uploader"].formData.push(code == "logo" ? $scope.configs.logo : $scope.configs.favicon);

            if(code == "logo") {
                $scope[code + "_uploader"].filters.push({
                    name: 'image_only',
                    fn: function (item, options) {
                        var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                        return '|jpg|png|jpeg|gif|'.indexOf(type) !== -1;
                    }
                });
            } else {
                $scope[code + "_uploader"].filters.push({
                    name: 'icon_only',
                    fn: function (item, options) {
                        var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                        return '|png|ico|'.indexOf(type) !== -1;
                    }
                });
            }

            $scope[code+"_uploader"].onWhenAddingFileFailed = function(item, filter, options) {
                if(filter.name == "image_only") {
                    $scope.message.setText(Label.uploader.error.type.image).isError(true).show();
                } else if(filter.name == "icon_only") {
                    $scope.message.setText(Label.uploader.error.type.icon).isError(true).show();
                }
            };

            $scope[code+"_uploader"].onAfterAddingFile = function(item, filter, options) {
                item.upload();
            };

            $scope[code+"_uploader"].onSuccessItem = function(fileItem, response, status, headers) {
                if(angular.isObject(response) && response.success) {
                    $scope.message.setText(response.message)
                        .isError(false)
                        .show()
                    ;

                } else {
                    $scope.message.setText(Label.uploader.error.general)
                        .isError(true)
                        .show()
                    ;
                }
            };
            $scope[code+"_uploader"].onErrorItem = function(fileItem, response, status, headers) {
                $scope.message.setText(response.message)
                    .isError(true)
                    .show()
                ;
            };

        }
    };

    $scope.Change_Design = function() {
        $scope.design_message = $scope.translated_messages[$scope.configs.editor_design.value];
    };

    $scope.Compute_Analytics = function() {
        $scope.form_loader_is_visible = true;
        Settings.computeAnalytics().success(function(data) {

            $scope.message.isError(false);
            $scope.message.setText(data.message)
                .show()
            ;

        }).error(function(data) {
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(true);
                $scope.message.setText(data.message)
                    .show()
                ;
            }
        }).finally(function() {
            $scope.form_loader_is_visible = false;
        });
    }

    $scope.Compute_Analytics_For_Period = function() {
        $scope.form_loader_is_visible_for_analytics = true;
        var period = {
            'from': $scope.generateAnalyticsPeriod.from,
            'to': $scope.generateAnalyticsPeriod.to
        }
        Settings.computeAnalyticsForPeriod(period).success(function(data) {
            $scope.message.isError(false);
            $scope.message.setText(data.message)
                .show()
            ;
        }).error(function(data) {
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(true);
                $scope.message.setText(data.message)
                    .show()
                ;
            }
        }).finally(function() {
            $scope.form_loader_is_visible_for_analytics = false;
        });
    }

});