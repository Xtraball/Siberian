App.service("Application", function($rootScope, $window, $http, $q, $timeout, $interval, $location, Push, modalManager, Url, Translator) {

    var service = {};

    service.is_native = false;
    service.is_android = false;
    service.is_ios = false;
    service.is_preview = false;
    service.is_locked = false;
    service.android = {};
    service.app_store_url = null;
    service.is_loaded = false;
    service.number_of_running_functions = 0;

    if($window.android) {
        service.android = $window.android;
    }

    service.callbacks = {
        success: new Array(),
        error: new Array(),
        reset: function(name) {
            this.success[name] = null;
            this.error[name] = null;
        }
    };

    service.socialShareData = function(params) {
        params["store_url"] = service.app_store_url;
        $window.sharing_data = JSON.stringify(params);
        service.call("openSharing",params);
    };

    service.takeSnapshots = function() {
        if(service.is_native && service.is_loaded && service.device_uid) {

            var nb_max_features = 3;

            var i = 1;
            if(service.layout_visibility == "homepage") {
                i = 0;
            }

            service.snapshot_tabbar_items = [];
            do {
                if(!service.options[i].is_link) {
                    service.snapshot_tabbar_items.push(service.options[i]);
                }
                i++;
            } while (service.snapshot_tabbar_items.length < nb_max_features);

            $rootScope.$broadcast('processSnapshots');

        }
    };

    service.isLoaded = function() {

        if(service.is_loaded) return;
        service.is_loaded = true;

        service.showMessages();

        service.call("appIsLoaded");

    };

    service.showMessages = function() {

        if(service.is_native && service.is_loaded && service.device_uid) {

            Push.getPushs(service.device_uid);

            Push.getLastMessages(service.device_uid).success(function(data) {
                if(data) {
                    //Loading last push
                    if (data.push_message) {
                        if (data.push_message.cover || data.push_message.action_value) {

                            var ok_label = null;
                            if(data.push_message.action_value) {
                                ok_label = Translator.get("View");
                            }
                            var push_modal = {
                                "title": data.push_message.title,
                                "cover": data.push_message.cover,
                                "content": data.push_message.text,
                                "show_cancel": true,
                                "ok_label": ok_label
                            };

                            if(data.push_message.open_webview) {
                                push_modal.url = data.push_message.action_value;
                            } else {
                                push_modal.confirmAction = function() {$location.path(data.push_message.action_value)};
                            }

                            modalManager.addToQueue(push_modal);
                        }
                    }

                    //Loading last InappMessage
                    if (data.inapp_message) {
                        var inapp_modal = {
                            "title": data.inapp_message.title,
                            "cover": data.inapp_message.cover,
                            "content": data.inapp_message.text,
                            "confirmAction": function() {Push.markInAppAsRead(service.device_uid, service.is_ios ? 1 : 2);}
                        };
                        modalManager.addToQueue(inapp_modal);
                    }
                    modalManager.show();
                }
            });
        }

    };

    service.call = function(method, params) {

        if(!this.is_native) return;


        var url = ["app", method];

        // Android JsInterface
        if(this.is_android && angular.isFunction(this.android[method])) {

            if(angular.isObject(params)) {
                this.android[method](JSON.stringify(params));
            } else if(angular.isDefined(params)) {
                this.android[method](params.toString());
            } else {
                this.android[method]();
            }

        // Android old way
        } else if(this.is_android || method == "setIsOnline") {

            if(angular.isObject(params)) {
                angular.forEach(params, function (value) {
                    url.push(value);
                });
            } else if(angular.isDefined(params)) {
                url.push(params);
            }

            url = url.join(":");
            $http({method: "HEAD", url: "/" + url});

        // iOS
        } else if(this.is_ios) {

            url = url.join(":");
            // Deffer the call to the native part if another one is already runnings
            $timeout(function() {
                $window.location = url;
                service.number_of_running_functions--;
            }, 100 * service.number_of_running_functions);

            service.number_of_running_functions++;
        }

    };

    service.addDataToContact = function(data, success, error) {

        this.callbacks.success["addDataToContact"] = success;
        this.callbacks.error["addDataToContact"] = error;

        $window.contact_data = JSON.stringify(data);
        $window.addToContactCallback = function(type, code) {
            service.fireCallback(type, "addDataToContact", {code: code});
        };

        this.call("addToContact", data);
    };

    service.openScanCamera = function(data, success, error) {
        this.callbacks.success["openScanCamera"] = success;
        this.callbacks.error["openScanCamera"] = error;

        this.call("openScanCamera", data);
    };

    service.storeData = function(data, success, error) {
        this.callbacks.success["storeData"] = success;
        this.callbacks.error["storeData"] = error;

        this.call("storeData", data);
    };

    service.getStoredData = function(data, success, error) {
        this.callbacks.success["getStoredData"] = success;
        this.callbacks.error["getStoredData"] = error;

        this.call("getStoredData", data);
    };

    service.getLocation = function(success, error) {

        // @todo Need to find a better solution to handle multiple getLocation requests.
        var id = new Date().getTime();

        this.callbacks.success["getLocation_"+id] = success;
        this.callbacks.error["getLocation_"+id] = error;

        if(this.is_ios && this.is_native) {

            $window.setCoordinates = function (type, latitude, longitude) {
                service.fireCallback(type, "getLocation_"+id, {latitude: latitude, longitude: longitude});
            };

            this.call("getLocation");

        } else {

            navigator.geolocation.getCurrentPosition(function(position) {
                service.fireCallback("success", "getLocation_"+id, {latitude: position.coords.latitude, longitude: position.coords.longitude});
            }, function(error) {
                service.fireCallback("error", "getLocation_"+id, error);
            }, { timeout: 7000 });

        }

    };

    service.openCamera = function(success, error) {
        this.callbacks.success["openCamera"] = success;
        this.callbacks.error["openCamera"] = error;

        this.call("openCamera");
    };

    service.addHandler = function(handler) {
        service["handle_"+handler] = true;

        if(handler == "code_scan") {
            $rootScope.$broadcast("ready_for_code_scan");
        }
    };

    service.fireCallback = function(type, name, params) {
        if(angular.isDefined(this.callbacks[type]) && angular.isFunction(this.callbacks[type][name])) {
            this.callbacks[type][name](params);
            this.callbacks.reset(name);
        }
    };

    service.isNative = function() {
        return !!this.is_native;
    };

    service.setDeviceUid = function(device_uid) {
        service.device_uid = device_uid;
        service.showMessages();
    };

    service.showCacheDownloadModal = function() {
        $rootScope.progressBarPercent = 0;
        $rootScope.showProgressBar = false;

        var push_modal = {
            "title": Translator.get("Offline content"),
            "content": "<p>" + Translator.get("Do you want to download all the contents now to access it when offline? If you do, we recommend you to use a WiFi connection.") + "</p>",
            "show_cancel": true,
            "confirmAction": function() {
                $rootScope.showProgressBar = true;

                $http({
                    method: 'GET',
                    url: Url.get("application/mobile_data/findall"),
                    cache: !$rootScope.isOverview,
                    responseType: 'json'
                }).success(function (data) {

                    var data = Object.keys(data).map(function (key) {return data[key]});
                    var image_codes = ['"image_url":', '"validated_image_url":', '"icon":', '"picture":', '"image":', '"picto":', '"picto_url":', '"flag_icon":', '"url":'];

                    var progress = 0;
                    var total = data.length;

                    angular.forEach(data, function (value) {

                        if(value.indexOf("http://", "https://") != -1) {
                            var e = $window.document.createElement("iframe");
                            e.src = value;

                            var timer = $interval(function() {
                                if (e.contentWindow.document.readyState == 'complete') {
                                    $interval.cancel(timer);
                                    $window.document.body.removeChild(e);
                                    progress++;
                                    $rootScope.progressBarPercent = parseInt(progress * 100 / total);

                                    if ($rootScope.progressBarPercent >= 100) $timeout(function () {
                                        $rootScope.showProgressBar = false;
                                    }, 500);
                                }
                            }, 1000);

                            $window.document.body.appendChild(e);
                        } else {

                            //caching each data uris
                            $http({
                                method: 'GET',
                                url: value,
                                cache: !$rootScope.isOverview,
                            }).success(function(result) {
                                if (typeof result == "object") {

                                    var data = JSON.stringify(result);

                                    angular.forEach(image_codes, function(image_code) {

                                        var begin_index = 0;

                                        while(begin_index != -1) {
                                            begin_index = data.indexOf(image_code, begin_index+1);

                                            if (begin_index != -1) {
                                                var image_url = data.substr(begin_index);
                                                image_url = image_url.substr(0, image_url.indexOf(',')).replace(image_code, '').replace(/["}{\]]/g, '');

                                                if (angular.isDefined(image_url) && image_url != "null" && image_url != "" && (/png|jpg|jpeg|gif/i).test(image_url)) {
                                                    $http({
                                                        method: 'GET',
                                                        url: image_url,
                                                        cache: !$rootScope.isOverview
                                                    });
                                                }
                                            }
                                        }
                                    });
                                }
                            }).finally(function() {
                                progress++;
                                $rootScope.progressBarPercent = parseInt(progress * 100 / total);

                                if ($rootScope.progressBarPercent >= 100) $timeout(function () {
                                    $rootScope.showProgressBar = false;
                                }, 500);
                            });
                        }
                    });

                });
            }
        };

        modalManager.addToQueue(push_modal);
        modalManager.show();
    };

    return service;

});