App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/application/backoffice_view", {
        controller: 'ApplicationViewController',
        templateUrl: BASE_URL+"/application/backoffice_view/template"
    }).when(BASE_URL+"/application/backoffice_view/index/app_id/:app_id", {
        controller: 'ApplicationViewController',
        templateUrl: BASE_URL+"/application/backoffice_view/template"
    }).when(BASE_URL+"/application/backoffice_view_acl/app_id/:app_id/admin_id/:admin_id", {
        controller: 'ApplicationEditController',
        templateUrl: BASE_URL+"/application/backoffice_view_acl/template"
    });

}).controller("ApplicationViewController", function($scope, $location, $timeout, $routeParams, Header,
                                                    Application, Url, FileUploader, Label) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.button.left.action = function() {
        $location.path(Url.get("application/backoffice_list"));
    };
    $scope.content_loader_is_visible = true;

    $scope.tmp_application = {};
    $scope.mobile_source = {
        design_code: null
    };
    $scope.application_banner = {};
    $scope.application_admob = {};

    $scope.datepicker_visible = false;
    $scope.showApkService = true;

    $scope.owners = {
        list: [],
        filter: ''
    };

    Application.loadViewData().success(function (data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
        $scope.ionic_confirm_message = data.ionic_message;
    });

    Application.find($routeParams.app_id).success(function (data) {
        $scope.application = data.application;
        $scope.statuses = data.statuses;
        $scope.design_codes = data.design_codes;
        $scope.section_title = data.section_title;
        angular.extend($scope.tmp_application, data.application);
        angular.extend($scope.application_banner, data.application);
        angular.extend($scope.application_admob, data.application);
        $scope.mobile_source.design_code = $scope.application.design_code;
        $scope.confirm_message_domain = data.application.confirm_message_domain;
        $scope.confirm_message_owner = data.application.confirm_message_owner;
        $scope.confirm_message_delete_admin = data.application.confirm_message_delete_admin;
        $scope.filter_too_short = data.application.filter_too_short;
        $scope.filter_no_result = data.application.filter_no_result;
        $scope.tmp_application.selectedOwner = data.application.admin_id;
        $timeout(function () {
            $scope.tmp_application.selectedOwner = data.application.admin_id;
        });

        $scope.initKeystoreUploader();
    }).finally(function () {
        $scope.content_loader_is_visible = false;
    });

    Application
        .getLicenseType()
        .success(function (response) {
            if (typeof response.success !== undefined &&
                response.success &&
                response.result &&
                response.result.isAllowed
            ) {
                $scope.showApkService = true;
                $scope.showApkServiceMessage = response.result.message;
                $scope.licenseType = response.result.type;
            }
        });

    $scope.removeAppAdmin = function (appId, adminId) {
        if (!window.confirm($scope.confirm_message_delete_admin)) {
            $scope.application_form_loader_is_visible = false;
            return false;
        }

        Application
            .removeAppAdmin(appId, adminId)
            .success(function (success) {
                $scope.message.setText(success.message)
                    .isError(false)
                    .show()
                ;
            }).error(function (error) {
                $scope.message.setText(error.message)
                    .isError(true)
                    .show()
                ;
            }).finally(function () {
                $timeout(function () {
                    location.reload();
                }, 1500);
            });
    };

    $scope.searchAppOwners = function () {
        if ($scope.owners.filter.length < 3) {
            window.alert($scope.filter_too_short);
            return;
        }

        Application
            .searchAppOwners($scope.owners.filter)
            .success(function (data) {
                if (data.collection.length <= 0) {
                    window.alert($scope.filter_no_result);
                } else {
                    $timeout(function () {
                        $scope.owners.list = data.collection;
                    });
                }
            }).error(function (data) {
                //
                console.error('error searchAppOwners data', data);
            });
    };

    $scope.saveAppOwner = function () {

        $scope.device_form_loader_is_visible = true;
        if (!window.confirm($scope.confirm_message_owner)) {
            $scope.application_form_loader_is_visible = false;
            return false;
        }

        Application
            .saveAppOwner($scope.application.id, $scope.tmp_application.selectedOwner)
            .success(function (data) {
                $scope.tmp_application.admin_id = $scope.application.admin_id = $scope.tmp_application.selectedOwner;

                if (angular.isObject(data)) {
                    $scope.message.setText(data.message)
                        .isError(false)
                        .show()
                    ;
                } else {
                    $scope.message.setText(Label.save.error)
                        .isError(true)
                        .show()
                    ;
                }
            }).error(function (data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function () {
            $scope.form_loader_is_visible = false;

            $timeout(function () {
                location.reload();
            }, 3000);
        });
    };

    $scope.sendApkToService = function () {
        $scope.generateSource(2, 0, 'apk');
    };

    $scope.switchToIonic = function() {
        if(!window.confirm($scope.ionic_confirm_message)) {
            return false;
        }

        $scope.application_form_loader_is_visible = true;

        Application.switchToIonic($scope.tmp_application.app_id).success(function(data) {

            if(angular.isObject(data)) {

                $scope.tmp_application.design_code = data.design_code;
                $scope.mobile_source.design_code = data.design_code;

                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            } else {
                $scope.message.setText(Label.save.error)
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
            $scope.application_form_loader_is_visible = false;
        });
    };

    $scope.generateSource = function(device_id, no_ads, apk) {
        $scope.content_loader_is_visible = true;
        Application.generateSource(device_id, no_ads, $scope.application.id, $scope.mobile_source.design_code, apk)
            .success(function(data) {
                if(data.reload) {
                    /** Only for direct download */
                    var device = (device_id == 1) ? "ios" : "android";
                    device = (no_ads == 1) ? device+"noads" : device;
                    var base = data.more["zip"][device]["path"];
                    window.location.href = BASE_URL+"/"+base;
                } else {
                    $scope.message.setText(data.message)
                        .isError(false)
                        .show()
                    ;
                }
                $scope.application.apk = data.more.apk;
                $scope.application.apk_service = data.more.apk_service;
                $scope.application.zip = data.more.zip;
                $scope.application.queued = data.more.queued;
                $scope.content_loader_is_visible = false;
            })
            .error(function(data) {
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.cancelQueue = function(device_id, no_ads, type) {

        $scope.content_loader_is_visible = true;
        if(typeof type == "undefined") {
            type = "zip";
        }

        Application.cancelQueue($scope.application.id, device_id, no_ads, type)
            .success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                $scope.application.zip = data.more.zip;
                $scope.application.queued = data.more.queued;
                $scope.content_loader_is_visible = false;
            })
            .error(function(data) {
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.downloadAndroidApk = function() {
        $scope.content_loader_is_visible = true;
        $scope.message.setText(Label.android.generating_apk)
            .isError(false)
            .show()
        ;

        Application.downloadAndroidApk($scope.application.id, $scope.mobile_source.design_code)
            .success(function(data) {
                if (data.reload) {
                    /** Only for direct download */
                    var base = data.more["apk"]["path"];
                    window.location.href = BASE_URL+"/"+base;
                } else {
                    $scope.message.setText(data.message)
                        .isError(false)
                        .show()
                    ;
                }
                $scope.application.apk = data.more.apk;
                $scope.application.zip = data.more.zip;
                $scope.application.queued = data.more.queued;
                $scope.content_loader_is_visible = false;

            })
            .error(function(data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
                $scope.content_loader_is_visible = false;
        });
    };

    $scope.toCalendar = function (date) {
        return moment(date).calendar();
    };

    $scope.noop = function () {
        // Do nothing!
    };

    $scope.saveInfo = function() {
        $scope.application_form_loader_is_visible = true;

        if(($scope.tmp_application.key != $scope.application.key) || ($scope.tmp_application.domain != $scope.application.domain)) {
            if(!window.confirm($scope.confirm_message_domain)) {
                $scope.tmp_application.key = $scope.application.key;
                $scope.tmp_application.domain = $scope.application.domain;
                $scope.application_form_loader_is_visible = false;
                return false;
            }
        }

        Application.saveInfo($scope.tmp_application).success(function(data) {

            if(angular.isObject(data)) {

                $scope.tmp_application.bundle_id = $scope.application.bundle_id = data.bundle_id;
                $scope.tmp_application.url = $scope.application.url = data.url;

                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            } else {
                $scope.message.setText(Label.save.error)
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
            $scope.application_form_loader_is_visible = false;
        });

    };

    $scope.cleanKeystoreQueue = function () {
        // Clean-up
        for (var i = 0; i < $scope.keystore_uploader.queue.length; i++) {
            $scope.keystore_uploader.queue[i].remove();
        }
    };

    $scope.initKeystoreUploader = function () {
        $scope.keystore_uploader = new FileUploader({
            url: Url.get("application/backoffice_view/uploadkeystore", {appId: $scope.application.id})
        });

        $scope.keystore_uploader.filters.push({
            name: 'limit',
            fn: function (item, options) {
                return this.queue.length < 1;
            }
        });

        $scope.keystore_uploader.onWhenAddingFileFailed = function (item, filter, options) {
            if (filter.name === 'limit') {
                $scope.message.setText(Label.uploader.error.only_one_at_a_time).isError(true).show();
            }
        };

        $scope.keystore_uploader.onSuccessItem = function (fileItem, response, status, headers) {
            $scope.keystoreLoader = false;
            if (angular.isObject(response) && response.success) {
                $scope
                    .message
                    .setText(response.message)
                    .isError(false)
                    .show();
            } else {
                $scope
                    .message
                    .setText(response.message)
                    .isError(true)
                    .show();
            }

            $scope.cleanKeystoreQueue();
        };

        $scope.keystore_uploader.onErrorItem = function (fileItem, response, status, headers) {
            $scope.keystoreLoader = false;
            $scope
                .message
                .setText(response.message)
                .isError(true)
                .show();

            $scope.cleanKeystoreQueue();
        };
    };

    $scope.keystoreLoader = false;
    $scope.uploadKeystore = function () {
        if ($scope.keystore_uploader.queue.length < 1) {
            window.alert('You must upload a package first!');
            return;
        }
        console.log($scope.keystore_uploader.queue[0]);
        if ($scope.keystore_uploader.queue[0].file.name.split('.').pop() !== 'zip') {
            window.alert('Package must be a zip archive!');
            $scope.cleanKeystoreQueue();
            return;
        }
        $scope.keystoreLoader = true;
        $scope.keystore_uploader.queue[0].upload();
    };

    $scope.saveDeviceInfo = function(confirm) {

        $scope.device_form_loader_is_visible = true;

        var localConfirm = confirm === undefined ? false : confirm;

        delete $scope.application_banner.confirm;
        if (localConfirm === true) {
            $scope.application_banner.confirm = localConfirm;
        }

        Application
            .saveDeviceInfo($scope.application_banner)
            .success(function(data) {

                if(angular.isObject(data)) {
                    $scope.message.setText(data.message)
                        .isError(false)
                        .show()
                    ;
                    $scope.application_banner.devices[1].versionCode = data.versionCode;
                } else {
                    $scope.message.setText(Label.save.error)
                        .isError(true)
                        .show()
                    ;
                }

            }).error(function(data) {

                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;

                setTimeout(function () {
                    if (data.hasOwnProperty('confirm')) {
                        if (window.confirm(data.confirmText)) {
                            $scope.saveDeviceInfo(true);
                        }
                    }
                }, 1200);

            }).finally(function() {
                $scope.device_form_loader_is_visible = false;
            });
    };

    $scope.saveBannerInfo = function() {

        $scope.device_form_loader_is_visible = true;

        Application.saveBannerInfo($scope.application_banner).success(function(data) {

            if(angular.isObject(data)) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            } else {
                $scope.message.setText(Label.save.error)
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
            $scope.device_form_loader_is_visible = false;
        });
    };

    $scope.saveAdvertisingInfo = function() {

        $scope.device_form_loader_is_visible = true;

        Application.saveAdvertisingInfo($scope.application_admob).success(function(data) {

            if(angular.isObject(data)) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            } else {
                $scope.message.setText(Label.save.error)
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
            $scope.device_form_loader_is_visible = false;
        });
    };

    $scope.keystore_uploader = new FileUploader({
        url: Url.get("application/backoffice_view/uploadcertificate")
    });

    $scope.certificate_uploader = new FileUploader({
        url: Url.get("application/backoffice_view/uploadcertificate")
    });

    $scope.certificate_uploader.onWhenAddingFileFailed = function(item, filter, options) {
        if(filter.name == "pem") $scope.message.setText(Label.uploader.error.type.pem).isError(true).show();
    };

    $scope.certificate_uploader.onAfterAddingFile = function(item, filter, options) {
        item.upload();
    };

    $scope.certificate_uploader.onSuccessItem = function(fileItem, response, status, headers) {

        if(angular.isObject(response) && response.success) {
            $scope.message.setText(response.message)
                .isError(false)
                .show()
            ;

            $scope.application.just_sent_the_certificate = true;
            $scope.application.pem_infos = response.pem_infos;

        } else {
            $scope.message.setText(Label.uploader.error.general)
                .isError(true)
                .show()
            ;
        }
    };

    $scope.certificate_uploader.onErrorItem = function(fileItem, response, status, headers) {
        $scope.message.setText(response.message)
            .isError(true)
            .show()
        ;
    };

    $scope.certificate_uploader.formData.push({app_id: $routeParams.app_id});

}).controller("ApplicationEditController", function($scope, $routeParams, Header, Application) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    Application.loadEditData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    $scope.params = {
        "app_id": $routeParams.app_id,
        "admin_id": $routeParams.admin_id
    };

    Application.findAdminAccess($scope.params).success(function(data) {
        $scope.username = data.user_name;
        $scope.appname = data.app_name;
        $scope.can_add_page = data.can_add_page;
        $scope.options = data.options;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.setCanAddPage = function(can_add_page) {
        $scope.params.can_add_page = can_add_page;
        Application.setCanAddPage($scope.params).success(function(data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
        });
    };

    $scope.saveAccess = function() {
        var params_opt = new Array();
        angular.forEach($scope.options, function(option) {
            if(!option.is_allowed) {
                params_opt.push({
                    "value_id": option.value_id,
                    "code": option.code
                });
            }
        });

        var params = {
            "options": params_opt,
            "app_id": $scope.params.app_id,
            "admin_id": $scope.params.admin_id
        };

        Application.saveAccess(params).success(function(data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
        });
    };

}).filter('appToPublish', function() {

    return function( items, show_app_to_publish_only) {

        var filtered = [];
        if(!angular.isDefined(show_app_to_publish_only)) {
            show_app_to_publish_only = false;
        }
        angular.forEach(items, function(item) {
            if(!show_app_to_publish_only || item.can_be_published) {
                filtered.push(item);
            }
        });

        return filtered;
    };

});
