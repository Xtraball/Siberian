App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/installer/backoffice_module", {
        controller: 'ModuleController',
        templateUrl: BASE_URL+"/installer/backoffice_module/template"
    });

}).controller("ModuleController", function($scope, $interval, $timeout, Backoffice, Header, Installer, Url, Label, FileUploader, AdvancedTools) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.show_release_note = false;
    $scope.show_only = false;

    $scope.checking_module = false;
    $scope.installation_progress = 0;
    $scope.package_full = null;
    $scope.package_details = {
        is_visible: false,
        name: null,
        version: null,
        description: null
    };
    $scope.check_for_updates = {
        check: true,
        download: false,
        next_version: null,
        loader_is_visible: false
    };
    $scope.permissions = {
        is_visible: false,
        progress: 0,
        interval_id: null,
        running: false,
        success: false,
        error: false,
        error_message: null
    };
    $scope.ftp = {
        credentials: {
            host: null,
            username: null,
            password: null,
            path: null,
            port: null
        },
        error: false,
        error_message: "",
        error_from_info: false,
        error_from_path: false,
        loader_is_visible: false
    };
    $scope.installation = {

        copy: {
            is_visible: false,
            progress: 0,
            interval_id: null,
            running: false,
            success: false,
            error: false
        },
        install: {
            is_visible: false,
            progress: 0,
            interval_id: null,
            running: false,
            success: false,
            error: false
        }
    };

    $scope.uploader = new FileUploader({
        url: Url.get("installer/backoffice_module/upload")
    });

    $scope.uploader.filters.push({
        name: 'limit',
        fn: function (item, options) {
            return this.queue.length < 1;
        }
    });

    /*/******** PAGE DATA **********/
    Installer.loadData().success(function (data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
        //$scope.ftp.credentials = data.ftp;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    /*/*** CHECK FOR UPDATES ******/
    $scope.checkForUpdates = function() {

        $scope.check_for_updates.loader_is_visible = true;

        Installer.checkForUpdates().success(function(data) {

            $scope.check_for_updates.check = false;
            if(data.url) {
                $scope.check_for_updates.next_version = data.version;
                $scope.check_for_updates.download = true;
            } else if(data.message) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                $scope.check_for_updates.no_updates_available = true;
            }

        }).error(function(data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.check_for_updates.loader_is_visible = false;
        });
    };

    $scope.downloadUpdate = function() {

        $scope.check_for_updates.loader_is_visible = true;

        Installer.downloadUpdate().success(function(data) {

            $scope.package_full = data;

            if(data.filename) {
                $scope.package_details = data.package_details;
                $scope.showPackageDetails();
                Installer.filename = data.filename;
                $scope.check_for_updates.check = false;
            } else if(data.message) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                $scope.check_for_updates.no_updates_available = true;
            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;
            }

        }).error(function(data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.check_for_updates.loader_is_visible = false;
        });

    };

    /*/******** UPLOADER **********/
    $scope.uploader.onWhenAddingFileFailed = function(item, filter, options) {
        if(filter.name == "zip_only") $scope.message.setText(Label.uploader.error.type.zip).isError(true).show();
        if(filter.name == "limit") $scope.message.setText(Label.uploader.error.only_one_at_a_time).isError(true).show();
    };

    $scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {

        if(angular.isObject(response) && response.success) {

            $scope.package_details = response.package_details;
            $scope.package_full = response;
            $scope.showPackageDetails();
            Installer.filename = response.filename;

        } else {
            $scope.message.setText(Label.uploader.error.general)
                .isError(true)
                .show()
            ;
        }
    };

    $scope.uploader.onErrorItem = function(fileItem, response, status, headers) {

        $scope.message.setText(response.message)
            .isError(true)
            .show()
        ;
    };

    $scope.showReleasenote = function(show_only) {
        if(typeof show_only != "undefined") {
            $scope.show_release_note = true;
            $scope.show_only = true;
            document.getElementById("informations").src = $scope.package_full.release_note.url;
        } else if ($scope.package_full.release_note.show) {
            $scope.show_only = false;
            $scope.show_release_note = true;
            document.getElementById("informations").src = $scope.package_full.release_note.url;
        } else {
            $scope.checkPermissions();
        }
    };

    $scope.close = function() {
        $scope.show_only = false;
        $scope.show_release_note = false;
    };

    $scope.dismissInstall = function() {
        $scope.show_release_note = false;
    };

    $scope.confirmInstall = function() {
        $scope.show_release_note = false;
        $scope.checkPermissions();
    };

    /*/******** PACKAGE DETAILS **********/
    $scope.showPackageDetails = function() {
        $scope.package_details.is_visible = true;
    };

    /*/******** PERMISSIONS **********/
    $scope.checkPermissions = function() {
        $scope.permissions.is_visible = true;
        $scope.permissions.running = true;
        $scope.permissions.success = false;
        $scope.permissions.error = false;
        $scope.permissions.error_message = null;
        $scope.permissions.progress = 0;

        $scope.permissions.interval_id = $interval(function() {
            $scope.permissions.progress += 5;
        }, 500, 18);

        Installer.checkPermissions().success(function(data) {

            if(angular.isObject(data) && data.success) {

                $scope.permissions.success = true;
                $scope.showModuleInstallation();
                $scope.copyModule();

            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;

                $scope.permissions.error = true;

            }

        }).error(function(data) {

            $scope.permissions.error_message = data.message;
            $scope.permissions.error = true;

        }).finally(function() {
            $interval.cancel($scope.permissions.interval_id);
            $scope.permissions.running = false;
            $scope.permissions.progress = 100;
        });

    };

    $scope.saveFtp = function() {

        if($scope.ftp.loader_is_visible) {
            return;
        }

        $scope.ftp.error_message = "";
        $scope.ftp.error = false;
        $scope.ftp.loader_is_visible = true;
        $scope.ftp.error_from_info = false;
        $scope.ftp.error_from_path = false;

        Installer.saveFtp($scope.ftp.credentials).success(function() {

            $scope.permissions.error = false;
            $scope.permissions.error_message = "";
            $scope.showModuleInstallation();
            $scope.copyModule();

        }).error(function(data) {

            var message = "An error occurred while trying to install the module. Please, reload the page and try again.";

            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.ftp.error = true;
            $scope.ftp.error_message = message;
            if(data.code == 1) {
                $scope.ftp.error_from_info = true;
            } else if(data.code == 2) {
                $scope.ftp.error_from_path = true;
            }

        }).finally(function() {
            $scope.ftp.loader_is_visible = false;
        });

    };

    /*/******** INSTALLATION **********/
    $scope.increaseProgressBar = function(state, step) {
        step = (typeof step == 'undefined') ? 5 : step;
        $scope.installation[state].progress += step;
    };

    $scope.copyModule = function() {

        if(!Installer.filename) {
            $scope.message.setText("An error occurred while trying to install the module. Please, reload the page and try again.")
                .isError(true)
                .show()
            ;
            return;
        }

        $scope.installation.copy.progress = 0;
        $scope.installation.copy.success = false;
        $scope.installation.copy.error = false;
        $scope.installation.copy.running = true;

        $scope.installation.copy.interval_id = $interval(function() {
            $scope.increaseProgressBar("copy");
        }, 500, 18);

        Installer.copy().success(function(data) {

            if(angular.isObject(data) && data.success) {

                $scope.installation.copy.success = true;
                $scope.installModule();

            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;

                $scope.installation.copy.error = true;

            }

        }).error(function(data) {

            $scope.message.setText()
                .isError(true)
                .show()
            ;

            $scope.installation.copy.error = true;

        }).finally(function() {
            $interval.cancel($scope.installation.copy.interval_id);
            $scope.installation.copy.running = false;
            $scope.installation.copy.progress = 100;
        });

    };

    $scope.installRetry = 0;
    $scope.installPoller = function () {
        Installer.install().success(function (data) {
            if ($scope.installRetry < 5 &&
                angular.isObject(data) &&
                data.success && angular.isDefined(data.reached_timeout)) {
                // Continue update, recall itself
                $scope.installPoller();
                $scope.installRetry++;

                console.log('Installer recall ' + $scope.installRetry + ' time.');
            } else if (angular.isObject(data) && data.success) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;

                $scope.installation.install.success = true;

                $scope.content_loader_is_visible = true;

                if (($scope.package_details !== undefined) &&
                    ($scope.package_details.restore_apps !== undefined) &&
                    ($scope.package_details.restore_apps === true)) {
                    AdvancedTools.restoreapps()
                        .finally(function () {
                            Backoffice.clearCache('app_manifest')
                                .success(function (manifestData) {
                                    $scope.content_loader_is_visible = true;
                                    $scope.message.setText(manifestData.message)
                                        .isError(false)
                                        .show()
                                    ;
                                }).finally(function () {
                                    $scope.installation.install.running = false;
                                    $timeout(function () {
                                        location.reload();
                                    }, 1500);
                                });
                        });
                } else {
                    Backoffice.clearCache('app_manifest')
                        .success(function (data) {
                            $scope.content_loader_is_visible = true;
                            $scope.message.setText(data.message)
                                .isError(false)
                                .show()
                            ;
                        }).finally(function () {
                            $scope.installation.install.running = false;
                            $timeout(function () {
                                location.reload();
                            }, 1500);
                        });
                }

            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;

                $scope.installation.install.error = true;

            }

        }).error(function(data) {

            var message = ((data !== undefined) && (data.message !== undefined)) ? data.message : "An unknown error occurred.";

            $scope.message.setText(message)
                .isError(true)
                .show()
            ;

            $scope.installation.install.error = true;

        }).finally(function() {
            $interval.cancel($scope.installation.install.interval_id);
            $scope.content_loader_is_visible = true;
            $scope.installation.install.running = false;
            $scope.installation.install.progress = 100;
        });
    };

    $scope.installModule = function() {

        $scope.installRetry = 0;

        $scope.installation.install.is_visible = true;
        $scope.installation.install.progress = 0;
        $scope.installation.install.success = false;
        $scope.installation.install.error = false;
        $scope.installation.install.running = true;

        $interval(function() {
            $scope.increaseProgressBar("install");
        }, 500, 1);

        $scope.installation.install.interval_id = $interval(function() {
            $scope.increaseProgressBar("install", 1);
        }, 500, 90);

        /** Installation poller with multiple retries. */
        $scope.installPoller();

    };

    $scope.showModuleInstallation = function() {
        $scope.installation.copy.is_visible = true;
    };

    $scope.toggleLoader = function() {
        $scope.header.loader_is_visible = !$scope.header.loader_is_visible;
    }


});
