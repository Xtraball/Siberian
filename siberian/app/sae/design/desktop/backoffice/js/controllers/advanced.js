/*global
    App, BASE_URL, Label, angular
 */
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
    }).when(BASE_URL+"/backoffice/advanced_store", {
        controller: 'BackofficeAdvancedStoreController',
        templateUrl: BASE_URL+"/backoffice/advanced_store/template"
    });

}).controller("BackofficeAdvancedController", function($log, $scope, $interval, Header, Advanced) {

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
        $scope.templates = data.templates;
        $scope.features = data.features;
        $scope.icons = data.icons;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.moduleAction = function (module, action) {
        $scope.form_loader_is_visible = true;

        Advanced.moduleAction(module, action).success(function (data) {
            var message = '';
            if (angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            }

            $scope.message.setText(message)
                .show()
            ;
        }).error(function (data) {
            var message = '';
            if (angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
        }).finally(function () {
            $scope.form_loader_is_visible = false;
        });
    };

    $scope.toggleFeature = function (feature) {
        $scope.form_loader_is_visible = true;
        Advanced
            .toggleFeature(feature.id, feature.is_enabled)
            .success(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                    $scope.message.isError(false);
                }

                $scope.message.setText(message)
                    .show()
                ;
            }).error(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                }

                $scope.message.setText(message)
                    .isError(true)
                    .show()
                ;
            }).finally(function () {
                $scope.form_loader_is_visible = false;
            });
    };

    $scope.toggleModule = function (module) {
        $scope.form_loader_is_visible = true;
        Advanced
            .toggleModule(module.id, module.is_enabled)
            .success(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                    $scope.message.isError(false);
                }

                $scope.message.setText(message)
                    .show()
                ;
            }).error(function (data) {
            var message = '';
            if (angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }

            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
        }).finally(function () {
            $scope.form_loader_is_visible = false;
        });
    };

}).controller("BackofficeAdvancedConfigurationController", function($log, $http, $scope, $timeout, $interval, $window,
                                                                    Label, Header, AdvancedConfiguration, FileUploader,
                                                                    Url, AdvancedTools) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.hostname = "";
    $scope.show_upload = false;
    $scope.report = {
        message: ""
    };

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

            $scope.message.onSuccess(data);

        }).error(function(data) {

            $scope.message.onError(data);

        }).finally(function() {
            $scope.form_loader_is_visible = false;

            $timeout(function() {
                location.reload();
            }, 3000);
        });
    };

    $scope.show_force = false;

    $scope.all_messages = null;

    $scope.submitReport = function() {

        AdvancedConfiguration.submitReport($scope.report.message)
            .success(function() {
                $scope.report.message = "";
                $window.alert("Thanks for your report.");
            })
            .error(function() {
                $window.alert("An error occurred while submit your report, please try again.");
            });

    };

    $scope.testSsl = function() {
        $scope.content_loader_is_visible = true;

        AdvancedConfiguration
            .testSsl()
            .success(function(data) {
                $scope.message.onSuccess(data);
                $scope.configs.testssl = data;
            })
            .error(function(data) {
                $scope.message.onError(data);
                $scope.configs.testssl = data;
            })
            .finally(function() {
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.generateSsl = function(hostname, force) {

        $http({
            method: 'GET',
            url: 'backoffice/advanced_configuration/checkhttp',
            cache: false,
            responseType:'json'
        }).then(function (response) {
            if (!$window.confirm("Are you sure ?")) {
                return;
            }

            $scope.content_loader_is_visible = true;
            AdvancedConfiguration.save($scope.configs).success(function(data) {

                $scope.message.onSuccess(data);

                /** When setting are ok, go for SSL */
                AdvancedConfiguration.generateSsl($scope.configs.current_domain, force).success(function(data) {

                    /** Now if it's ok, it's time for Panel  */
                    $scope.message.onSuccess(data);
                    $scope.all_messages = data.all_messages;
                    $log.info("SSL Ok, time to push to panel.");

                    $http({
                        method: 'GET',
                        url: 'backoffice/advanced_configuration/sendtopanel/hostname/'+$scope.configs.current_domain,
                        cache: false,
                        responseType:'json'
                    }).then(function (response) {
                        // This may never occurs but well .. :)
                        $scope.message.onSuccess(response);
                        $scope.poller('backoffice/advanced_configuration/checkssl');
                    }, function (response) {
                        $scope.poller('backoffice/advanced_configuration/checkssl');
                    });

                }).error(function(data) {

                    $scope.message.onError(data);
                    $scope.content_loader_is_visible = false;

                }).finally(function() {
                    // Nope!
                });

            }).error(function(data) {
                $scope.message.onError(data);
            }).finally(function() {
                // Nope!
            });

        }, function (response) {
            return $window.alert('HTTP must be reachable to renew the SSL/HTTPS certificate.');
        });

        return false;
    };

    $scope.uploadToPanel = function(hostname) {

        $http({
            method: 'GET',
            url: 'backoffice/advanced_configuration/checkhttp',
            cache: false,
            responseType:'json'
        }).then(function (response) {
            if (!$window.confirm("Are you sure ?")) {
                return;
            }

            $scope.content_loader_is_visible = true;

            AdvancedConfiguration.save($scope.configs)
                .success(function(data) {

                    $scope.message.onSuccess(data);

                    $log.info("SSL Ok, time to push to panel.");

                    $http({
                        method: 'GET',
                        url: 'backoffice/advanced_configuration/sendtopanel/hostname/'+hostname,
                        cache: false,
                        responseType:'json'
                    }).then(function (response) {
                        // This may never occurs but well .. :)
                        $scope.message.onSuccess(response);
                        $scope.poller('backoffice/advanced_configuration/checkssl');
                    }, function (response) {
                        $scope.poller('backoffice/advanced_configuration/checkssl');
                    });

                }).error(function(data) {

                $scope.message.onError(data);
            }).finally(function() {

                $scope.content_loader_is_visible = true;
            });

        }, function (response) {
            return $window.alert('HTTP is not available to renew the SSL/HTTPS certificate.');
        });

        return false;
    };

    $scope.poller = function(url) {
        var times = 0;
        var poller = $interval(function() {

            /** We hit the timeout, show an error */
            if(times++ > 10) {
                times = 0;
                $interval.cancel(poller);
                poller = undefined;

                $log.info("#01-Error: timeout reloading panel.");
                $scope.message.information($scope.all_messages.https_unreachable);
                $scope.content_loader_is_visible = false;
            }

            $log.info("#02-Retrying: n"+times+" poll.");

            $http({
                method: 'GET',
                url: url,
                cache: false,
                responseType:'json'
            }).then(function successCallback(response) {
                /** Clear poller on success */
                $interval.cancel(poller);
                poller = undefined;

                /** Try to get HTTPS for redirect. */
                if(typeof response.data.https_url != "undefined") {
                    $log.info('typeof response.data.https_url != "undefined"');
                    location = response.data.https_url+"/backoffice/advanced_configuration";
                } else {
                    $log.info('location.reload()');
                    location.reload();
                }

            }, function errorCallback(response) {
                $log.info("#03-Retry: not reachable yet.");
            });

            /**.Showing wait message */
            $scope.message.information($scope.all_messages.polling_reload);
        }, 3000);
    };

    $scope.form = {
        hostname: "",
        cert_path: "",
        ca_path: "",
        private_path: "",
        fullchain_path: "",
        upload: "0"
    };

    $scope.uploaders = [
        {type : "cert_path",    uploader : "cert_path"},
        {type : "ca_path",      uploader : "ca_path"},
        {type : "private_path", uploader : "private_path"},
        {type : "fullchain_path", uploader : "fullchain_path"}
    ];

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
            if(filter.name === "limit") {
                $scope.message.setText(Label.uploader.error.only_one_at_a_time).isError(true).show();
            }
        };

        $scope[code].onAfterAddingFile = function(item, filter, options) {
            item.upload();
        };

        $scope[code].onSuccessItem = function(fileItem, response, status, headers) {
            if(angular.isObject(response) && response.success) {

                $scope.form[response.code] = response.tmp_path;
                $log.info($scope.form);

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
                    fullchain_path: "",
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

            }).finally(function () {
                $scope.form_loader_is_visible = false;
            });
        }
    };

    $scope.migrate_sessions = function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (!window.confirm('You are about to migrate all MySQL to the configured Redis server, all existing sessions in Redis will be replaced, are you sure ?')) {
            return;
        }
        $scope.content_loader_is_visible = true;
        AdvancedTools.migrateSessions()
            .success(function (data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show();
            })
            .error(function (errorData) {
                $scope.message
                    .setText(errorData.message)
                    .isError(true)
                    .show();
                $scope.content_loader_is_visible = false;
            })
            .finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };



}).controller("BackofficeAdvancedToolsController", function($log, $scope, $interval, Header, AdvancedTools, Backoffice) {

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

    $scope.integrity = {
        scope: 'all',
        running: false,
        done: false,
        checked: 0,
        total: 0,
        rows: []
    };

    $scope.integrityCount = function (area, status) {
        return $scope.integrity.rows.filter(function (row) {
            return row.area === area && row.status === status;
        }).length;
    };

    // The check runs by slices, so that no request has to hash the whole installation
    $scope.test_integrity = function() {
        var integrity = $scope.integrity;
        var statuses = {hash: 'modified', missing: 'missing', unreadable: 'unreadable'};
        var messageEmpty = '';
        var messageIssues = '';

        integrity.running = true;
        integrity.done = false;
        integrity.checked = 0;
        integrity.total = 0;
        integrity.rows = [];

        var fail = function (data) {
            integrity.running = false;
            $scope.message.setText((data && data.message) ? data.message : 'Integrity check failed.')
                .isError(true)
                .show()
            ;
        };

        var step = function (offset) {
            AdvancedTools.runtest({offset: offset, scope: integrity.scope})
                .success(function (data) {
                    if (data.error) {
                        return fail(data);
                    }

                    messageEmpty = data.messageEmpty;
                    angular.forEach(statuses, function (status, key) {
                        angular.forEach(data[key], function (file) {
                            integrity.rows.push({
                                file: file,
                                status: status,
                                area: file.indexOf('var/apps/') === 0 ? 'apps' : 'core'
                            });
                        });
                    });

                    integrity.total = data.total;
                    integrity.checked = data.checked;

                    if (!data.done) {
                        return step(data.next);
                    }

                    integrity.running = false;
                    integrity.done = true;

                    if (integrity.rows.length === 0) {
                        $scope.message.setText(messageEmpty)
                            .isError(false)
                            .show()
                        ;
                    }
                })
                .error(fail);
        };

        step(0);
    };

    $scope.restore_apps = function () {
        if (!window.confirm('You are about to restore apps sources, are you sure ?')) {
            return;
        }
        $scope.content_loader_is_visible = true;
        AdvancedTools.restoreapps()
            .success(function (data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            })
            .error(function (errorData) {
                $scope.message
                    .setText(errorData.message)
                    .isError(false)
                    .show();
                $scope.content_loader_is_visible = false;
            })
            .finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.migrate_sessions = function () {
        if (!window.confirm('You are about to migrate all MySQL to the configured Redis server, all existing sessions in Redis will be replaced, are you sure ?')) {
            return;
        }
        $scope.content_loader_is_visible = true;
        AdvancedTools.migrateSessions()
            .success(function (data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show();
            })
            .error(function (errorData) {
                $scope.message
                    .setText(errorData.message)
                    .isError(true)
                    .show();
                $scope.content_loader_is_visible = false;
            })
            .finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };

}).controller("BackofficeAdvancedCronController", function($log, $scope, $interval, $timeout, Backoffice, Header,
                                                           AdvancedConfiguration, AdvancedCron) {

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

    $scope.loadContent = function () {
        AdvancedCron.findAll().success(function(data) {
            $scope.system_tasks = data.system_tasks;
            $scope.tasks = data.tasks;
            $scope.apk_queue = data.apk_queue;
            $scope.source_queue = data.source_queue;
        }).finally(function() {
            $scope.content_loader_is_visible = false;
        });
    };

    $scope.loadContent();

    $scope.androidSdkRestart = function() {
        $scope.content_loader_is_visible = true;
        Backoffice.clearCache("android_sdk").success(function (data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.content_loader_is_visible = false;
        });
    };

    $scope.restartApk = function (queueId) {
        $scope.content_loader_is_visible = true;
        AdvancedCron
            .restartApk(queueId)
            .success(function (data) {
                $scope.message.setText(data.message)
                .isError(false)
                .show()
                ;
                $scope.content_loader_is_visible = false;
                $scope.loadContent();
            });
    };

    $scope.save = function() {
        $scope.form_loader_is_visible = true;

        AdvancedConfiguration
            .save($scope.configs)
            .success(function(data) {
                $scope.message
                    .isError(false)
                    .setText(data.message)
                    .show();

                $timeout(function() {
                    location.reload();
                }, 1000);
            }).error(function(data) {
                $scope.form_loader_is_visible = false;

                $scope.message
                    .isError(true)
                    .setText(data.message)
                    .show();
            });
    };

});

/**
 * Settings > Advanced > Store account: connect to extensions.siberiancms.com, consult purchases and license keys.
 */
App.controller("BackofficeAdvancedStoreController", function ($log, $scope, $timeout, Header, AdvancedStore) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.status = {connected: false, pending: null};
    $scope.data = null;
    $scope.busy = false;
    $scope.error = null;
    $scope.last_state = null;

    var pollTimer = null;

    var fail = function (data) {
        $scope.error = (data && data.message) ? data.message : 'Unexpected error';
    };

    var stopPolling = function () {
        if (pollTimer) {
            $timeout.cancel(pollTimer);
            pollTimer = null;
        }
    };

    var loadPurchases = function (refresh) {
        $scope.busy = true;
        AdvancedStore.purchases(refresh).success(function (data) {
            if (data.error) {
                fail(data);
                return;
            }
            $scope.data = data;
            $scope.error = null;
            if (data.needs_reconnect) {
                $scope.status.connected = false;
                $scope.status.needs_reconnect = true;
            }
        }).error(function () {
            fail({message: 'The request failed.'});
        }).finally(function () {
            $scope.busy = false;
        });
    };

    var schedulePoll = function () {
        stopPolling();
        var seconds = ($scope.status.pending && $scope.status.pending.interval) ? $scope.status.pending.interval : 5;
        pollTimer = $timeout(function () {
            AdvancedStore.poll().success(function (data) {
                if (data.error) {
                    fail(data);
                    return;
                }
                if (data.state === 'pending') {
                    if ($scope.status.pending && data.interval) {
                        $scope.status.pending.interval = data.interval;
                    }
                    schedulePoll();
                } else if (data.state === 'connected') {
                    $scope.last_state = null;
                    load();
                } else {
                    $scope.last_state = data.state;
                    $scope.status.pending = null;
                }
            }).error(function () {
                schedulePoll();
            });
        }, seconds * 1000);
    };

    var load = function () {
        AdvancedStore.status().success(function (data) {
            if (data.error) {
                fail(data);
                return;
            }
            $scope.status = data;
            if (data.connected) {
                loadPurchases(false);
            } else if (data.pending) {
                schedulePoll();
            }
        }).error(function () {
            fail({message: 'The request failed.'});
        }).finally(function () {
            $scope.content_loader_is_visible = false;
        });
    };

    AdvancedStore.loadData().success(function (data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    $scope.connect = function () {
        $scope.busy = true;
        $scope.error = null;
        $scope.last_state = null;
        AdvancedStore.connect().success(function (data) {
            if (data.error) {
                fail(data);
                return;
            }
            $scope.status.pending = data.pending;
            $scope.status.needs_reconnect = false;
            schedulePoll();
        }).error(function () {
            fail({message: 'The request failed.'});
        }).finally(function () {
            $scope.busy = false;
        });
    };

    $scope.cancel = function () {
        stopPolling();
        $scope.status.pending = null;
    };

    $scope.refresh = function () {
        loadPurchases(true);
    };

    $scope.disconnect = function () {
        if (!window.confirm('Disconnect this install from your store account?')) {
            return;
        }
        $scope.busy = true;
        AdvancedStore.disconnect().success(function (data) {
            if (data.error) {
                fail(data);
                return;
            }
            $scope.status = {connected: false, pending: null, this_install_key: $scope.status.this_install_key};
            $scope.data = null;
            $scope.error = null;
        }).error(function () {
            fail({message: 'The request failed.'});
        }).finally(function () {
            $scope.busy = false;
        });
    };

    $scope.mask = function (key) {
        key = key || '';
        return key.length > 8 ? key.substr(0, 4) + '••••••••' + key.substr(-4) : '••••';
    };

    // Every download of every purchase, the ones the per-row eye buttons act on.
    var allDownloads = function () {
        var list = [];
        angular.forEach(($scope.data && $scope.data.purchases) || [], function (purchase) {
            angular.forEach(purchase.downloads || [], function (download) {
                list.push(download);
            });
        });
        return list;
    };

    $scope.allRevealed = function () {
        var list = allDownloads();
        return list.length > 0 && list.every(function (download) {
            return download.revealed;
        });
    };

    $scope.toggleAll = function () {
        var reveal = !$scope.allRevealed();
        angular.forEach(allDownloads(), function (download) {
            download.revealed = reveal;
        });
    };

    $scope.copy = function (text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
            return;
        }
        var area = document.createElement('textarea');
        area.value = text;
        document.body.appendChild(area);
        area.select();
        try {
            document.execCommand('copy');
        } catch (e) {
            $log.warn(e);
        }
        document.body.removeChild(area);
    };

    // The linked site of a module license is generally the Siberian license key of the install; the store keeps it lowercased.
    $scope.isThisInstall = function (site) {
        var mine = ($scope.status && $scope.status.this_install_key) ? $scope.status.this_install_key : '';
        return mine !== '' && String(site).toLowerCase() === mine;
    };

    $scope.$on('$destroy', stopPolling);

    load();
});
