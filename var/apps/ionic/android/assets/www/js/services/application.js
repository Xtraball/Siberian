/*global
    App, ionic, DOMAIN, _, window, localStorage
*/

App.service('Application', function ($sbhttp, $q, $rootScope, $timeout, $translate, $window, $queue, $log, Dialog, Url) {

    var service = {};

    service.is_webview = null;

    var _loaded = false;
    var _loaded_resolver = $q.defer();

    Object.defineProperty(service, "loaded", {
        get: function () {
            if (_loaded) {
                $log.info("Application loaded, resolving promise");
                return $q.resolve();
            }
            return _loaded_resolver.promise;
        },
        set: function (value) {
            _loaded = !!value;
            if (_loaded === true) {
                $log.info("Application loaded, resolving promise");
                _loaded_resolver.resolve();
            }
        }
    });

    service.app_id          = null;
    service.app_name        = null;
    service.googlemaps_key  = null;

    service.is_customizing_colors = ($window.location.href.indexOf("application/mobile_customization_colors/") >= 0);

    Object.defineProperty(service, "acceptedOfflineMode", {
        get: function () {
            return ($window.localStorage.getItem("sb-offline-mode") === "ok");
        }
    });

    service.showCacheDownloadModalOrUpdate = function () {


        $rootScope.progressBarPercent           = 0;
        $rootScope.showProgressBar              = false;

        var offlineResponse = $window.localStorage.getItem("sb-offline-mode");

        if(offlineResponse === "ok") {
            $log.debug("offline mode has been accepted, updating");
            service.updateCache();
        } else if(offlineResponse === "no") {
            $log.debug("offline mode has been refused in the past, not updating");
        } else {
            $log.debug("offline mode need to be asked");
            var title = $translate.instant("Offline content");
            var message = $translate.instant("Do you want to download all the contents now to access it when offline? If you do, we recommend you to use a WiFi connection.");
            var buttons = [$translate.instant("No"), $translate.instant("Yes")];

            Dialog.confirm(title, message, buttons, "text-center").then(function (res) {

                if (((typeof res === "number") && res === 2) || ((typeof res === "boolean") && res)) {

                    $window.localStorage.setItem("sb-offline-mode", "ok");

                    var progress_type = "CIRCLE";
                    if (ionic.Platform.isAndroid()) {
                        progress_type = "BAR";
                    }
                    $window.plugins.ProgressView.show($translate.instant("Downloading..."), progress_type, false, "DEVICE_DARK");

                    $rootScope.showProgressBar = true;

                    service.updateCache();
                } else {
                    $window.localStorage.setItem("sb-offline-mode", "no");
                }
            });
        }
    };

    var _updatingCache = false;

    var _replace_tokens = function(url) {
        return _.isString(url) ? url.replace("%DEVICE_UID%", $rootScope.device_uid).replace("%CUSTOMER_ID%", $rootScope.customer_id) : 0;
    };

    service.updateCache = function () {
        if(window.OfflineMode) window.OfflineMode.setCanCache();

        if (_updatingCache === true) {
            return;
        }

        var device_uid = null;
        if ($window.device) {
            device_uid = $window.device.uuid;
        }

        // Double tap for cache
        $sbhttp.get(Url.get("front/mobile/loadv2", {
                add_language: true,
                sid: localStorage.getItem("sb-auth-token"),
                device_uid: device_uid
            }), {
                cache: !$rootScope.isOverview,
                timeout: 15000
            });

        $sbhttp.get(Url.get("application/mobile_data/findall"), {
            cache: false,
            timeout: 15000
        }).success(function (data) {
            var total = data.paths.length + data.assets.length;
            if (isNaN(total)) {
                total = 100;
            }

            var progress = 0;
            var assets_done = JSON.parse($window.localStorage.getItem("sb-offline-mode-assets"));
            if (!_.isArray(assets_done)) {
                assets_done = [];
            }

            var fileQueue   = [];
            var retryQueue  = [];

            var delay = 100;
            var maxRequest = 15;
            if (!ionic.Platform.isAndroid()) {
                delay = 250;
                maxRequest = 3;
            }

            var requestCount = 0;
            var pathQueue = null;

            var updateFailed = function (asset) {

                requestCount -= 1;
                if (requestCount < 0) {
                    requestCount = 0;
                }

                /** Restart queue */
                if (pathQueue.paused && (requestCount <= maxRequest)) {
                    $log.debug("Start " + requestCount);
                    pathQueue.start();
                }

                retryQueue.push(asset);
            };

            var updateProgress = function () {
                progress += 1;

                requestCount -= 1;
                if (requestCount < 0) {
                    requestCount = 0;
                }

                /** Restart queue */
                if (pathQueue.paused && (requestCount <= maxRequest)) {
                    $log.debug("Start " + requestCount);
                    pathQueue.start();
                }

                var percent = (progress / total);

                // Change progress only if it's bigger. (don't go back ...)
                if(percent.toFixed(2) > $rootScope.progressBarPercent) {
                    $rootScope.progressBarPercent = percent.toFixed(2);
                }

                if (isNaN($rootScope.progressBarPercent)) {
                    $rootScope.progressBarPercent = 0;
                }

                $window.plugins.ProgressView.setProgress($rootScope.progressBarPercent);
                $window.localStorage.setItem("sb-offline-mode-assets", JSON.stringify(assets_done));

                if ($rootScope.progressBarPercent >= 1) {
                    _updatingCache = false;

                    $timeout(function () {
                        if ($rootScope.showProgressBar) {
                            $rootScope.showProgressBar = false;
                            $window.plugins.ProgressView.hide();
                        }
                    }, 1000);
                }
            };

            /** Force end */
            var endProgress = function() {
                progress = total;
                $rootScope.progressBarPercent = 1;
                $window.plugins.ProgressView.setProgress($rootScope.progressBarPercent);
                $window.localStorage.setItem("sb-offline-mode-assets", JSON.stringify(assets_done));

                _updatingCache = false;

                $timeout(function () {
                    if ($rootScope.showProgressBar) {
                        $rootScope.showProgressBar = false;
                        $window.plugins.ProgressView.hide();
                    }
                }, 1000);
            };

            // Check and add images not present in assets (useful for push which is device relative)
            var look_for_images = function (object) {
                _.forEach(object, function (obj, key) {
                    if (_.isString(obj) && (/\.(png|jpg|jpeg|gif)$/.test(obj))) {

                        var path = _replace_tokens(obj);

                        if (!/^https?:/.test(path)) {
                            path = (DOMAIN + "/" + path).replace(/([^:/])\/+/g, "$1/");
                        }

                        if (!_.includes(data.assets, path)) {
                            total += 1;
                            pathQueue.add({
                                type: "asset",
                                path: path
                            });
                        }
                    } else if (_.isArray(obj) || _.isObject(obj)) {
                        look_for_images(obj);
                    }
                });
            };

            var retry = true;
            var fetchAssets = function (asset) {
                if (asset.type === "path") {
                    requestCount += 1;
                    $sbhttp({
                        method: "GET",
                        url: asset.path,
                        cache: !$rootScope.isOverview
                    }).success(function (data) {
                        if(_.isObject(data)) {
                            look_for_images(data);
                        }
                        updateProgress(asset);
                    }).error(function () {
                        if (retry) {
                            updateFailed(asset);
                        } else {
                            updateProgress();
                        }
                    });
                } else if (asset.type === "asset") {
                    requestCount += 1;
                    $sbhttp.cache(asset.path).then(function () {
                        updateProgress();
                        assets_done.push(asset.path);
                    }, function () {
                        if (retry) {
                            updateFailed(asset);
                        } else {
                            updateProgress();
                            assets_done.push(asset.path);
                        }
                    });
                }

                if (requestCount >= maxRequest) {
                    $log.debug("Paused " + requestCount);
                    pathQueue.pause();
                }
            };

            var options = {
                delay: delay,
                paused: true,
                complete: function () {
                    $log.debug("Queue ends.");

                    updateProgress();
                    retry = false;
                    var _retryQueue = retryQueue;
                    if (retryQueue.length > 0) {
                        pathQueue = $queue.queue(fetchAssets, {
                            delay: 1000,
                            paused: true,
                            complete: function () {
                                $log.debug("Retry queue ends.");

                                updateProgress();
                                endProgress();
                            }
                        });
                        pathQueue.addEach(_retryQueue);
                        pathQueue.start();
                    } else {

                        endProgress();
                    }
                }
            };

            /** Rework objects */
            _.forEach(data.paths, function (path) {
                fileQueue.push({
                    type: 'path',
                    path: _replace_tokens(path)
                });
            });

            _.forEach(data.assets, function (asset) {
                var path = _replace_tokens(asset);
                if (!_.includes(assets_done, path)) {
                    fileQueue.push({
                        type: 'asset',
                        path: path
                    });
                }
            });

            pathQueue = $queue.queue(fetchAssets, options);
            pathQueue.addEach(fileQueue);
            pathQueue.start();

        }).error(function () {
            _updatingCache = false;
            $rootScope.showProgressBar = false;
            $window.plugins.ProgressView.hide();
        });
    };

    service.generateWebappConfig = function () {
        return $sbhttp({
            method: 'GET',
            url: Url.get("application/mobile/generatewebappconfig"),
            cache: false,
            responseType: 'json'
        });
    };

    return service;
});
