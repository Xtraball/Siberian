App.config(function ($routeProvider) {
    $routeProvider.when(BASE_URL + '/installer/backoffice_module', {
        controller: 'ModuleController',
        templateUrl: BASE_URL + '/installer/backoffice_module/template'
    });

}).controller('ModuleController', function ($scope, $interval, $timeout, $q, Backoffice, Header, Installer, Url, Label,
                                            FileUploader, AdvancedTools) {

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
        error_message: '',
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

    // The single click-or-drop area: what it shows comes from zoneState(), derived from the uploader and the flow.
    $scope.zone = {
        file: null,
        sent: false,
        error: false,
        license: false
    };
    $scope.license_check = {
        running: false
    };

    // The file is sent as soon as it is chosen or dropped.
    $scope.uploader = new FileUploader({
        url: Url.get('installer/backoffice_module/upload'),
        autoUpload: true
    });

    /**
     * True while something is being sent, checked, copied or installed: the area ignores new files then.
     */
    $scope.zoneBusy = function () {
        return !!($scope.uploader.isUploading ||
            $scope.license_check.running ||
            $scope.permissions.running ||
            $scope.installation.copy.running ||
            $scope.installation.install.running);
    };

    $scope.zoneState = function () {
        if ($scope.uploader.isUploading) {
            return 'sending';
        }
        if ($scope.license_check.running) {
            return 'checking';
        }
        if ($scope.zone.error) {
            return 'error';
        }
        if ($scope.zone.license) {
            return 'license';
        }
        if ($scope.zone.sent) {
            return 'done';
        }
        return 'idle';
    };

    $scope.zoneIcon = function () {
        switch ($scope.zoneState()) {
            case 'sending':
            case 'checking':
                return 'fa-spinner fa-spin';
            case 'done':
                return 'fa-check';
            case 'license':
            case 'error':
                return 'fa-exclamation-triangle';
            default:
                return 'fa-cloud-upload';
        }
    };

    $scope.openFilePicker = function () {
        var input = document.getElementById('file_uploader');
        if (input && !$scope.zoneBusy()) {
            input.click();
        }
    };

    $scope.zoneKeydown = function ($event) {
        if ($event.which === 13 || $event.which === 32 || $event.key === 'Enter' || $event.key === ' ') {
            $event.preventDefault();
            $scope.openFilePicker();
        }
    };

    var scrollToSection = function (selector) {
        $timeout(function () {
            var element = document.querySelector(selector);
            if (element && element.scrollIntoView) {
                element.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        }, 150);
    };

    $scope.uploader.filters.push({
        name: 'limit',
        fn: function (item, options) {
            if (this.queue.length < 1) {
                return true;
            }
            // A file already chosen can be replaced, unless it is being sent or its installation has started.
            if (!$scope.zoneBusy() && !$scope.installation.copy.is_visible) {
                this.clearQueue();
                return true;
            }
            return false;
        }
    });

    /*/******** PAGE DATA **********/
    Installer.loadData().success(function (data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
        $scope.words = data.words;
        $scope.ini = data.ini;
    }).finally(function () {
        $scope.content_loader_is_visible = false;
    });

    /*/*** CHECK FOR UPDATES ******/
    $scope.checkForUpdates = function () {
        $scope.check_for_updates.loader_is_visible = true;

        Installer.checkForUpdates().success(function (data) {
            $scope.check_for_updates.check = false;
            if (data.url) {
                $scope.check_for_updates.next_version = data.version;
                $scope.check_for_updates.download = true;
            } else if (data.message) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                $scope.check_for_updates.no_updates_available = true;
            }

        }).error(function (data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function () {
            $scope.check_for_updates.loader_is_visible = false;
        });
    };

    $scope.downloadUpdate = function () {
        if ($scope.check_for_updates.loader_is_visible) {
            return;
        }
        $scope.check_for_updates.loader_is_visible = true;
        $scope.check_for_updates.result = null;
        $scope.check_for_updates.message = '';

        Installer.downloadUpdate().success(function (data) {
            $scope.package_full = data;

            if (data.filename) {
                $scope.package_details = data.package_details;
                $scope.showPackageDetails();
                Installer.filename = data.filename;
                $scope.check_for_updates.check = false;
                $scope.check_for_updates.result = 'downloaded';
            } else if (data.message) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                $scope.check_for_updates.no_updates_available = true;
                $scope.check_for_updates.result = 'up_to_date';
                $scope.check_for_updates.message = data.message;
            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;
                $scope.check_for_updates.result = 'error';
                $scope.check_for_updates.message = Label.uploader.error.general;
            }
        }).error(function (data) {
            var message = (angular.isObject(data) && data.message) ? data.message : Label.uploader.error.general;
            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
            $scope.check_for_updates.result = 'error';
            $scope.check_for_updates.message = message;
        }).finally(function () {
            $scope.check_for_updates.loader_is_visible = false;
        });
    };

    /*/******** MODULE UPDATES **********/
    $scope.module_updates = {
        loading: false,
        busy_code: null,
        updates: [],
        stale: false,
        fetched_at: null,
        terms_accepted: false,
        terms_hash: null,
        error: null
    };

    /**
     * The module update endpoints answer an error with HTTP 400 and the JSON payload (error, code, message, terms),
     * so it reaches the error callback of $http and never .success(). Success and error go through one handler,
     * and an answer that is not that payload (a proxy page, a network error) becomes a general error.
     */
    var moduleUpdatesRequest = function (request, handler) {
        return request.then(function (response) {
            handler(response.data);
        }, function (response) {
            var data = response && response.data;
            handler(angular.isObject(data) && data.error ?
                data : {error: true, code: 'general', message: Label.uploader.error.general});
        });
    };

    $scope.checkModuleUpdates = function (force) {
        $scope.module_updates.loading = true;
        $scope.module_updates.error = null;

        moduleUpdatesRequest(Installer.checkModuleUpdates(force), function (data) {
            if (data.error) {
                $scope.module_updates.error = data.message;
                return;
            }
            $scope.module_updates.updates = data.updates;
            $scope.module_updates.stale = data.stale;
            $scope.module_updates.fetched_at = data.fetched_at;
            $scope.module_updates.terms_accepted = data.terms_accepted;
            $scope.module_updates.terms_hash = data.terms_hash;
        }).finally(function () {
            $scope.module_updates.loading = false;
        });
    };

    // Shows the live terms, and records the acceptance only when the administrator confirms.
    $scope.showModuleUpdatesTerms = function (terms, nextAction) {
        swal({
            html: true,
            title: terms.version ? 'Terms of download (v' + terms.version + ')' : 'Terms of download',
            text: terms.html,
            showCancelButton: true,
            closeOnConfirm: false,
            confirmButtonText: 'I agree',
            cancelButtonText: 'Cancel'
        }, function (confirmed) {
            if (!confirmed) {
                return;
            }
            moduleUpdatesRequest(Installer.acceptModuleUpdatesTerms(terms.hash), function (data) {
                if (data.error && data.terms) {
                    // Changed while the page was open: show the new ones.
                    swal.close();
                    $scope.showModuleUpdatesTerms(data.terms, nextAction);
                } else if (data.error) {
                    swal.showInputError(data.message);
                } else {
                    swal.close();
                    $scope.module_updates.terms_accepted = true;
                    if (nextAction) {
                        nextAction();
                    }
                }
            });
        });
    };

    /**
     * Asks for the licence key of a module installed without one, then retries the update with it.
     */
    $scope.askModuleLicense = function (update, text) {
        swal({
            html: true,
            title: update.name,
            type: 'prompt',
            text: text,
            showCancelButton: true,
            closeOnConfirm: true,
            closeOnCancel: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: $scope.words.confirmLicense,
            cancelButtonText: $scope.words.cancelLicense
        }, function (value) {
            if (value === false || value === undefined) {
                return;
            }
            value = String(value).trim();
            if (value.length === 0) {
                $timeout(function () {
                    $scope.askModuleLicense(update, $scope.words.emptyLicense);
                });
                return;
            }
            $timeout(function () {
                $scope.updateModule(update, value);
            });
        });
    };

    $scope.updateModule = function (update, license) {
        if (!$scope.module_updates.terms_accepted) {
            moduleUpdatesRequest(Installer.moduleUpdatesTerms(), function (data) {
                if (data.error) {
                    $scope.message.setText(data.message).isError(true).show();
                    return;
                }
                $scope.showModuleUpdatesTerms(data.terms, function () {
                    $scope.updateModule(update, license);
                });
            });
            return;
        }

        $scope.module_updates.busy_code = update.code;
        $scope.check_for_updates.loader_is_visible = true;
        moduleUpdatesRequest(Installer.updateModule(update.code, license), function (data) {
            if (data.error && data.terms) {
                // The terms changed since they were accepted.
                $scope.module_updates.terms_accepted = false;
                $scope.showModuleUpdatesTerms(data.terms, function () {
                    $scope.updateModule(update, license);
                });
            } else if (data.error && data.code === 'license_required') {
                $scope.askModuleLicense(update, data.message);
            } else if (data.error && data.code === 'module_license') {
                // The key just typed, or the saved one, was refused: say so and ask for a new one.
                $scope.askModuleLicense(update, data.message);
            } else if (data.error) {
                $scope.message.setText(data.message).isError(true).show();
            } else if (data.filename) {
                // From here it is the regular installer: package details, permissions, copy, install.
                $scope.package_full = data;
                $scope.package_details = data.package_details;
                Installer.filename = data.filename;
                $scope.showPackageDetails();
                $scope.check_for_updates.check = false;
            }
        }).finally(function () {
            $scope.module_updates.busy_code = null;
            $scope.check_for_updates.loader_is_visible = false;
        });
    };

    $scope.checkModuleUpdates(false);

    /**
     * Checking for module license
     */

    $scope.checkModuleLicense = function (code, itemId) {
        var defer = $q.defer();

        Installer
            .checkModuleLicense(code, itemId)
            .success(function (success) {
                defer.resolve(true);
            }).error(function (error) {
            defer.reject(error.code);
        });

        return defer.promise;
    };

    /**
     * Checking for module license
     */

    /*/******** UPLOADER **********/
    $scope.uploader.onWhenAddingFileFailed = function (item, filter, options) {
        if (filter.name === 'zip_only') {
            $scope.message.setText(Label.uploader.error.type.zip).isError(true).show();
        }
        if (filter.name === 'limit') {
            $scope.message.setText(Label.uploader.error.only_one_at_a_time).isError(true).show();
        }
    };

    $scope.uploader.onAfterAddingFile = function (item) {
        $scope.zone.file = item.file.name;
        $scope.zone.sent = false;
        $scope.zone.error = false;
        $scope.zone.license = false;
        $scope.package_details.is_visible = false;
        $scope.permissions.is_visible = false;

        if ($scope.ini && $scope.ini.max_size > 0 && item.file.size > $scope.ini.max_size) {
            $scope.message.setText($scope.words.maxSize).isError(true).show();
            $scope.uploader.clearQueue();
            $scope.zone.error = true;
        }
    };

    $scope.uploader.onSuccessItem = function (fileItem, response, status, headers) {
        if (angular.isObject(response) && response.success) {
            $scope.zone.sent = true;
            $scope.package_details = response.package_details;
            $scope.package_full = response;
            $scope.showPackageDetails();
            Installer.filename = response.filename;
            Installer.reinstall = null;
        } else {
            $scope.zone.error = true;
            $scope.message.setText(Label.uploader.error.general)
                .isError(true)
                .show();

            for (var i = 0; i < $scope.uploader.queue.length; i++) {
                $scope.uploader.queue[i].remove();
            }
        }
    };

    $scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
        // A refusal of the server is JSON with a message, anything else (a proxy page, a size limit) is not.
        var message = (angular.isObject(response) && response.message) ? response.message : Label.uploader.error.general;
        $scope.zone.error = true;
        $scope.message.setText(message)
            .isError(true)
            .show();
    };

    /**
     * Same version already installed: ask before running it again, "No" cancels the whole install.
     */
    $scope.confirmReinstall = function (nextAction) {
        Installer.reinstall = null;
        if (!$scope.package_details.can_reinstall) {
            nextAction();
            return;
        }

        swal({
            html: true,
            title: $scope.words.reinstallTitle,
            type: 'warning',
            text: $scope.words.reinstallText,
            showCancelButton: true,
            closeOnConfirm: true,
            closeOnCancel: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: $scope.words.reinstallConfirm,
            cancelButtonText: $scope.words.reinstallCancel
        }, function (isConfirm) {
            if (isConfirm) {
                Installer.reinstall = $scope.package_details._name;
                nextAction();
            }
        });
    };

    $scope.showReleasenote = function (show_only) {
        if (typeof show_only === 'undefined') {
            $scope.confirmReinstall($scope.showReleasenoteConfirmed);
            return;
        }
        $scope.showReleasenoteConfirmed(show_only);
    };

    $scope.showReleasenoteConfirmed = function (show_only) {
        if (typeof show_only !== 'undefined') {
            $scope.show_release_note = true;
            $scope.show_only = true;
            document.getElementById('informations').src = $scope.package_full.release_note.url;
        } else if ($scope.package_full.release_note.show) {
            $scope.show_only = false;
            $scope.show_release_note = true;
            document.getElementById('informations').src = $scope.package_full.release_note.url;
        } else {
            $scope.checkPermissions();
        }
    };

    $scope.close = function () {
        $scope.show_only = false;
        $scope.show_release_note = false;
    };

    $scope.dismissInstall = function () {
        $scope.show_release_note = false;
    };

    /**
     * Confirm the update installation
     */
    $scope.confirmInstall = function () {
        var willContinue = true;
        try {
            if ($scope.package_full.release_note.is_major !== undefined &&
                $scope.package_full.release_note.is_major) {
                $scope.show_release_note = false;
                willContinue = $scope.confirmMajorUpdate(function () {
                    $scope.checkPermissions();
                });
            }
        } catch (e) {
            // Nope!
            console.error('Caught error.', e.message);
            $scope.show_release_note = true;
        }

        if (!willContinue) {
            console.info('Aborted installation.');
            return;
        }

        $scope.show_release_note = false;
        $scope.checkPermissions();
    };

    /*/******** PACKAGE DETAILS **********/
    $scope.showPackageDetails = function () {

        var code = $scope.package_details.code;
        var itemId = $scope.package_details.item_id;
        if (code !== false && itemId !== false) {
            $scope.license_check.running = true;
            var promise = $scope.checkModuleLicense(code, itemId);
            promise.then(function (success) {
                $scope.package_details.is_visible = true;
                scrollToSection('.package-details-section');
            }, function (error) {
                if (error === 'license_empty') {
                    // Prompt to set license
                    $scope.setLicense(code, itemId);
                }
                if (error === 'license_error') {
                    // Prompt to change license
                    $scope.setLicense(code, itemId);
                }
                if (error === 'license_replaceable') {
                    // Expired or refused key: a new key can replace it, cancelling keeps the install going
                    $scope.setLicense(code, itemId, true);
                }
            }).finally(function () {
                $scope.license_check.running = false;
            });
        } else {
            $scope.package_details.is_visible = true;
            scrollToSection('.package-details-section');
        }
    };

    $scope.setLicense = function (code, itemId, isReplaceable) {
        swal({
            html: true,
            title: code,
            type: 'prompt',
            text: isReplaceable ? $scope.words.replaceLicense : $scope.words.setLicense,
            showCancelButton: true,
            closeOnConfirm: false,
            closeOnCancel: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: $scope.words.confirmLicense,
            cancelButtonText: isReplaceable ? $scope.words.keepLicense : $scope.words.cancelLicense,
            buttons: {
                confirm: {
                    value: ''
                }
            }
        }, function (value) {
            if (isReplaceable && value === false) {
                // Keep the current key, the installation goes on as it always did
                $scope.package_details.is_visible = true;
                scrollToSection('.package-details-section');
                return;
            }

            if (value === false) {
                // Cancelled: the package stays hidden, say why instead of leaving the page silent
                $scope.zone.license = true;
                $scope.message.setText($scope.words.licenseNeeded).isError(true).show();
                return;
            }

            if (value.length > 0) {
                // Trying to validate the license
                $scope.content_loader_is_visible = true;
                Installer
                    .setModuleLicense(code, itemId, value)
                    .success(function (success) {
                        $scope
                            .message
                            .setText(success.message)
                            .isError(false)
                            .show();

                        $scope.package_details.is_visible = true;
                        $scope.zone.license = false;
                        scrollToSection('.package-details-section');
                        swal.close();

                    }).error(function (error) {
                    swal.showInputError('<code>' + error.message + '</code>');
                }).finally(function () {
                    $scope.content_loader_is_visible = false;
                });

                return true;
            }

            swal.showInputError('<code>' + $scope.words.emptyLicense + '</code>');
            return false;
        });
    };

    /*/******** PERMISSIONS **********/
    $scope.checkPermissions = function () {
        $scope.permissions.is_visible = true;
        $scope.permissions.running = true;
        $scope.permissions.success = false;
        $scope.permissions.error = false;
        $scope.permissions.error_message = null;
        $scope.permissions.progress = 0;

        $scope.permissions.interval_id = $interval(function () {
            $scope.permissions.progress += 5;
        }, 500, 18);

        Installer.checkPermissions().success(function (data) {
            if (angular.isObject(data) && data.success) {

                $scope.permissions.success = true;
                $scope.showModuleInstallation();
                $scope.copyModule();

            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show();

                $scope.permissions.error = true;
            }
        }).error(function (data) {
            if (!data || !data.message) {
                // Seems we got an issue, try to rebuild manifest once
                Backoffice.clearCache("app_manifest")
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
            } else {
                $scope.permissions.error_message = data.message;
                $scope.permissions.error = true;
            }
        }).finally(function () {
            $interval.cancel($scope.permissions.interval_id);
            $scope.permissions.running = false;
            $scope.permissions.progress = 100;
        });
    };

    $scope.saveFtp = function () {
        if ($scope.ftp.loader_is_visible) {
            return;
        }

        $scope.ftp.error_message = '';
        $scope.ftp.error = false;
        $scope.ftp.loader_is_visible = true;
        $scope.ftp.error_from_info = false;
        $scope.ftp.error_from_path = false;

        Installer.saveFtp($scope.ftp.credentials).success(function () {
            $scope.permissions.error = false;
            $scope.permissions.error_message = '';
            $scope.showModuleInstallation();
            $scope.copyModule();
        }).error(function (data) {
            var message = 'An error occurred while trying to install the module. Please, reload the page and try again.';
            if (angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.ftp.error = true;
            $scope.ftp.error_message = message;
            if (data.code == 1) {
                $scope.ftp.error_from_info = true;
            } else if (data.code == 2) {
                $scope.ftp.error_from_path = true;
            }
        }).finally(function () {
            $scope.ftp.loader_is_visible = false;
        });
    };

    /*/******** INSTALLATION **********/
    $scope.increaseProgressBar = function (state, step) {
        step = (typeof step === 'undefined') ? 5 : step;
        $scope.installation[state].progress += step;
    };

    $scope.copyModule = function () {
        if (!Installer.filename) {
            $scope.message
                .setText('An error occurred while trying to install the module. Please, reload the page and try again.')
                .isError(true)
                .show();
            return;
        }

        $scope.installation.copy.progress = 0;
        $scope.installation.copy.success = false;
        $scope.installation.copy.error = false;
        $scope.installation.copy.running = true;

        $scope.installation.copy.interval_id = $interval(function () {
            $scope.increaseProgressBar('copy');
        }, 500, 18);

        Installer.copy().success(function (data) {
            if (angular.isObject(data) && data.success) {
                $scope.installation.copy.success = true;
                $scope.installModule();
            } else {
                $scope.message
                    .setText(Label.uploader.error.general)
                    .isError(true)
                    .show();

                $scope.installation.copy.error = true;
            }
        }).error(function (data) {
            var message = (angular.isObject(data) && data.message) ? data.message : Label.uploader.error.general;
            $scope.message
                .setText(message)
                .isError(true)
                .show();

            $scope.installation.copy.error = true;
        }).finally(function () {
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
                // Continue update, recall itself!
                $scope.installPoller();
                $scope.installRetry += 1;
            } else if (angular.isObject(data) && data.success) {
                $scope.message
                    .setText(data.message)
                    .isError(false)
                    .show();

                $scope.installation.install.success = true;
                $scope.content_loader_is_visible = true;

                if (($scope.package_details !== undefined) &&
                    ($scope.package_details.restore_apps !== undefined) &&
                    ($scope.package_details.restore_apps === true)) {
                    AdvancedTools.restoreapps()
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
                $scope.message
                    .setText(Label.uploader.error.general)
                    .isError(true)
                    .show();

                $scope.installation.install.error = true;
            }
        }).error(function (data) {
            var message = ((data !== undefined) && (data.message !== undefined)) ?
                data.message : 'An unknown error occurred.';

            $scope.message
                .setText(message)
                .isError(true)
                .show();

            $scope.installation.install.error = true;
        }).finally(function () {
            $interval.cancel($scope.installation.install.interval_id);
            $scope.content_loader_is_visible = true;
            $scope.installation.install.running = false;
            $scope.installation.install.progress = 100;
        });
    };

    $scope.installModule = function () {
        $scope.installRetry = 0;
        $scope.installation.install.is_visible = true;
        $scope.installation.install.progress = 0;
        $scope.installation.install.success = false;
        $scope.installation.install.error = false;
        $scope.installation.install.running = true;

        $interval(function () {
            $scope.increaseProgressBar('install');
        }, 500, 1);

        $scope.installation.install.interval_id = $interval(function () {
            $scope.increaseProgressBar('install', 1);
        }, 500, 90);

        /** Installation poller with multiple retries. */
        $scope.installPoller();
    };

    $scope.showModuleInstallation = function () {
        $scope.installation.copy.is_visible = true;
        scrollToSection('.installation-section');
    };

    $scope.toggleLoader = function () {
        $scope.header.loader_is_visible = !$scope.header.loader_is_visible;
    };

    // Confirmation handler for major updates with warning!
    $scope.confirmMajorUpdate = function (nextAction) {
        $scope.confirmKey = $scope.words.confirmKey;
        var majorMessage = $scope.words.majorMessage
            .replace(/#VERSION#/gmi, $scope.package_details.version);

        swal({
            html: true,
            title: $scope.words.titleMajor,
            type: 'prompt',
            text: majorMessage,
            showCancelButton: true,
            closeOnConfirm: false,
            closeOnCancel: true,
            confirmButtonColor: '#ff3a2e',
            confirmButtonText: $scope.words.confirmDelete,
            cancelButtonText: $scope.words.cancelDelete,
            buttons: {
                confirm: {
                    value: ''
                }
            }
        }, function (value) {
            if (value === $scope.confirmKey.replace(/#VERSION#/gmi, $scope.package_details.version)) {
                swal.close();
                nextAction();
                return true;
            }

            swal.showInputError('<code>' + $scope.words.mismatch + '</code>');
            return false;
        });
    };
});
