var App = angular.module('starter', ['ionic', 'ion-gallery', 'ngCordova', 'ngIOS9UIWebViewPatch', 'angular-carousel', 'lodash', 'ngImgCrop', 'ionic-zoom-view', 'ngSanitize', "tmh.dynamicLocale", "ngQueue"])
//Add spinner template
        .constant("$ionicLoadingConfig", {
            template: "<ion-spinner></ion-spinner>"
        })
        .config(function ($compileProvider, $httpProvider, $ionicConfigProvider, tmhDynamicLocaleProvider, UrlProvider, $sbhttpProvider, $logProvider) {

            var Url = UrlProvider.$get();

            tmhDynamicLocaleProvider.localeLocationPattern(Url.get('/app/sae/modules/Application/resources/angular-i18n/angular-locale_{{locale}}.js', {remove_key: true}));
            tmhDynamicLocaleProvider.storageKey((+new Date())*Math.random()+""); // don't remember locale

            $sbhttpProvider.alwaysCache = true;
            $sbhttpProvider.debug = false;

            $logProvider.debugEnabled(false);

            //Add hook on HTTP transactions
            $httpProvider.interceptors.push(function ($q, $injector) {
                return {
                    request: function (config) {
                        var sid = localStorage.getItem("sb-auth-token");
                        if (sid && config.url.indexOf(".html") == -1 && $injector.get('Connection').isOnline) {
                            //Force cookie
                            if (config.url.indexOf(DOMAIN) > -1 && config.noSbToken !== true) {
                                config.url = config.url + "?sb-token=" + sid;
                            }
                        }
                        return config;
                    },
                    responseError: function (response) {
                        if(response.config.url.match(/(templates|layout\/home)\/.*\.html$/) && (response.config.url != "templates/home/l6/view.html")) {
                            response.config.url = "templates/home/l6/view.html";
                            console.log("System: An error occured while loading your Layout template, fallback on Layout 6.");
                            return $injector.get('$sbhttp')(response.config);
                        }
                        return $q.reject(response);
                    }
                };
            });

            $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|map|geo|skype|tel|file|smsto):/);

            $httpProvider.defaults.withCredentials = true;

            if (isOverview) {
                $ionicConfigProvider.views.maxCache(0);
            }
        })
        .run(function ($sbhttp, $ionicConfig, $ionicHistory, $ionicPlatform, $ionicPopup, $ionicSlideBoxDelegate, $ionicScrollDelegate, $location, $rootScope, $state, $templateCache, $timeout, $translate, $window, Analytics, Application, Connection, Customer, Dialog, FacebookConnect, Facebook, HomepageLayout, Push, Url, AUTH_EVENTS, PUSH_EVENTS) {
            $ionicPlatform.ready(function() {
                Object.defineProperty($rootScope, "isOnline", {
                    get: function() {
                        return Connection.isOnline;
                    }
                });

                Object.defineProperty($rootScope, "isOffline", {
                    get: function() {
                        return Connection.isOffline;
                    }
                });

                //Load translation is mandatory to any process
                $translate.findTranslations().finally(function () {
                    $ionicPlatform.ready(function () {
                        $window.cordova = $window.cordova || {};
                        $window.device = $window.device || {};

                        $window.Connection = Connection;

                        if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
                            cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
                        }
                        if (window.StatusBar) {
                            StatusBar.styleDefault();
                        }

                        Dialog.is_webview = Application.is_webview = (ionic.Platform.device().platform == "browser");

                        if ($window.device) {
                            Push.device_uid = device.uuid;
                        }

                        Push.startBackgroundGeolocation();

                        $rootScope.app_is_loaded = true;
                        $rootScope.has_popup = false;
                        $rootScope.app_is_bo_locked = false;

                        /** WebRTC for iOS */
                        if (window.device.platform === 'iOS') {
                            cordova.plugins.iosrtc.registerGlobals();
                        }

                        $ionicPlatform.on('resume', function (result) {
                            sbLog("## App is resumed ##");
                            Analytics.storeOpening().then(function (result) {
                                Analytics.data.storeClosingId = result.id;
                            });
                        });

                        // hello
                        $ionicPlatform.on('pause', function (result) {
                            sbLog("## App is on pause ##");
                            Analytics.storeClosing();
                        });

                    });

                    $rootScope._getLastId = function (collection) {
                        var last_id = null;
                        for (var i = 0; i < collection.length; i++) {
                            if (!last_id || collection[i].id > last_id) last_id = collection[i].id;
                        }
                        return last_id;
                    };

                    $rootScope._getFirstId = function (collection) {
                        var first_id = null;
                        for (var i = 0; i < collection.length; i++) {
                            if (!first_id || collection[i].id < first_id) first_id = collection[i].id;
                        }
                        return first_id;
                    };

                    $rootScope.getTargetForLink = function () {
                        return Application.is_webview ? "_system" : "_blank";
                    };

                    /** Handler for overview */
                    $rootScope.$on('$stateChangeSuccess', function (event, toState, toStateParams, fromState, fromStateParams) {
                        if(parent && (typeof parent.postMessage == "function") && (parent != window)) {
                            parent.postMessage("state.go", DOMAIN);
                        }

                        if($ionicHistory.currentStateName() == "home") {
                            $timeout(function() {
                                HomepageLayout.callHooks();
                            }, 100);
                        }
                    });

                    /** Event to catch state-go from source code */
                    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
                    var eventer = window[eventMethod];
                    var messageEvent = (eventMethod === "attachEvent") ? "onmessage" : "message";

                    // Listen to message from child window
                    eventer(messageEvent, function(e) {
                        var parts = e.data.split("=");
                        var action = parts[0];
                        var params = {};
                        if(parts.length >= 2) {
                            action = parts[0];
                            params = parts[1].replace(/(^\?)/,'').split(",").map(function(n){return n = n.split(":"),this[n[0].trim()] = n[1],this}.bind({}))[0];
                        }

                        var offline = (typeof params.offline !== "undefined") ? (params.offline === "true") : false;

                        switch(action) {
                            case "state-go":
                                var state = params.state;
                                delete params.state;
                                delete params.offline;
                                if(!offline && $rootScope.isOffline) {
                                    $rootScope.onlineOnly();
                                } else {
                                    $state.go(state, params);
                                }
                        }
                    }, false);

                    $rootScope.$on('$stateChangeStart', function (event, toState, toStateParams, fromState, fromStateParams) {

                        if ($rootScope.app_is_locked && toState.name != "padlock-view") {
                            event.preventDefault();
                            $state.go("padlock-view");
                        } else if (Customer.can_access_locked_features && toState.name == "padlock-view") {
                            event.preventDefault();
                        } else if (Application.is_webview && toState.name == "codescan") {
                            event.preventDefault();
                        } else if (Connection.isOffline) {
                            // Check if app feature is accessible offline
                        }
                    });

                    window.rootScope = $rootScope;

                    $window.addEventListener("online", function () {
                        sbLog('online');
                    });

                    $window.addEventListener("offline", function () {
                        sbLog('offline');
                    });

                    $rootScope.onlineOnly = function() {
                        Dialog.alert(
                            $translate.instant("Offline mode"),
                            $translate.instant("This feature is not available in offline mode!"),
                            $translate.instant("OK")
                        );
                    };

                    $rootScope.$on(AUTH_EVENTS.loginSuccess, function () {
                        $rootScope.app_is_locked = Application.is_locked && !Customer.can_access_locked_features;
                        if (!$rootScope.app_is_locked && Application.is_locked) {
                            $state.go("home");
                        }
                    });

                    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function () {

                        $rootScope.app_is_locked = Application.is_locked;

                        if ($rootScope.app_is_locked) {
                            $ionicHistory.nextViewOptions({
                                disableBack: true
                            });
                            $state.go("padlock-view");
                        }

                    });

                    $rootScope.$on('$ionicView.beforeEnter', function () {
                        Analytics.storeClosing();
                    });

                    $rootScope.$on(PUSH_EVENTS.notificationReceived, function (event, data) {

                        if (!$rootScope.has_popup) {

                            if (data.additionalData.cover || data.additionalData.action_value) {

                                var dialog_data = {
                                    okText: $translate.instant("View"),
                                    cancelText: $translate.instant("Cancel"),
                                    cssClass: "push-popup",
                                    title: data.title,
                                    template: '<div class="list card">' +
                                        '   <div class="item item-image' + (data.additionalData.cover ? '' : ' ng-hide') + '">' +
                                        '       <img src="' + (DOMAIN + data.additionalData.cover) + '">' +
                                        '   </div>' +
                                        '   <div class="item item-custom">' +
                                        '       <span>' + data.message + '</span>' +
                                        '   </div>' +
                                        '</div>'
                                };

                                if (data.additionalData.action_value) {
                                    $ionicPopup.confirm(dialog_data).then(function (res) {
                                        if (res) {
                                            if($rootScope.isOffline) {
                                                return $rootScope.onlineOnly();
                                            }
                                            if (data.additionalData.open_webview == true || data.additionalData.open_webview == "true") {
                                                window.open(data.additionalData.action_value, "_blank", "location=yes");
                                            } else {
                                                $location.path(data.additionalData.action_value);
                                            }
                                        }

                                        $rootScope.has_popup = false;
                                    });

                                    $rootScope.has_popup = true;
                                } else {
                                    $ionicPopup.alert(dialog_data);
                                }

                            } else {
                                Dialog.alert($translate.instant("Notification"), data.message, $translate.instant("OK"));
                            }

                            $rootScope.$broadcast(PUSH_EVENTS.unreadPushs, data.count);
                        }
                    });

                    $rootScope.showMobileFeatureOnlyError = function () {
                        var popup = $ionicPopup.show({
                            title: $translate.instant("Error"),
                            subTitle: $translate.instant("This feature is available from the application only")
                        });
                        $timeout(function () {
                            popup.close();
                        }, 4000);
                        return;
                    };

                    var sid = localStorage.getItem("sb-auth-token");

                    //get & process app data
                    $sbhttp.get(Url.get("front/mobile/load", {add_language: true, sid: sid}), {timeout: 10000}).then(function (response) {
                        var data = response.data;

                        if (data.application.is_bo_locked == 1) {
                            $rootScope.app_is_bo_locked = true;

                            $ionicHistory.nextViewOptions({
                                disableBack: true
                            });
                            $state.go("locked");
                        }

                        if((data.application.ios_status_bar_is_hidden && ionic.Platform.isIOS()) || (data.application.android_status_bar_is_hidden && ionic.Platform.isAndroid())) {
                            window.StatusBar.hide();
                        }

                        if (data.css) {
                            var link = document.createElement("link");
                            link.rel = "stylesheet";
                            link.href = data.css + "?t="+(+new Date());
                            document.head.appendChild(link);
                        }

                        Customer.id = data.customer.id;
                        Customer.can_access_locked_features = data.customer.can_access_locked_features;
                        Customer.can_connect_with_facebook = data.customer.can_connect_with_facebook;
                        Customer.saveCredentials(data.customer.token);

                        Application.app_id = data.application.id;
                        Application.app_name = data.application.name;
                        Application.privacy_policy = data.application.privacy_policy;
                        Application.googlemaps_key = data.application.googlemaps_key;
                        Application.is_locked = data.application.is_locked == 1;
                        Application.offline_content = (data.application.offline_content);

                        if (!Application.is_webview) {
                            if(!$window.localStorage.getItem("first_running")) {

                                if(Application.offline_content) {
                                    Application.showCacheDownloadModal();
                                }

                                $window.localStorage.setItem("first_running", "true");
                                Analytics.storeInstallation();
                            } else {
                                if(Application.offline_content) {
                                    Application.updateCache();
                                }
                            }
                        }

                        Analytics.storeOpening().then(function (result) {
                            if (result && result.id) {
                                Analytics.data.storeClosingId = result.id;
                            }
                        });

                        $rootScope.app_is_locked = Application.is_locked && !Customer.can_access_locked_features;

                        $window.colors = data.application.colors;

                        if (data.application.facebook.id) {
                            FacebookConnect.permissions = (!Array.isArray(data.application.facebook.scope)) ? new Array(data.application.facebook.scope) : data.application.facebook.scope;
                            FacebookConnect.app_id = data.application.facebook.id;
                        }

                        var admob = data.application.admob;

                        if (!Application.is_webview && admob.id && $window.AdMob) {
                            if (admob.type == "banner") {
                                $window.AdMob.createBanner({
                                    adId: admob.id,
                                    position: $window.AdMob.AD_POSITION.BOTTOM_CENTER,
                                    autoShow: true
                                });
                            } else {
                                $window.AdMob.prepareInterstitial({
                                    adId: admob.id,
                                    autoShow: true
                                });
                            }
                        }

                        if (Customer.isLoggedIn()) {
                            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                        }
                        else {
                            $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
                        }

                        // Set push senderID
                        Push.setSenderID(data.application.gcm_senderid);
                        Push.setIconColor(data.application.gcm_iconcolor);
                        Push.register();
                        Push.getLastMessages().success(function (data) {
                            if (data && !$rootScope.has_popup) {

                                //Loading last push
                                if (data.push_message) {

                                    if (data.push_message.cover || data.push_message.action_value) {

                                        var dialog_data = {
                                            title: data.push_message.title,
                                            cssClass: "push-popup",
                                            template: '<div class="list card">' +
                                                '   <div class="item item-image' + (data.push_message.cover ? '' : ' ng-hide') + '">' +
                                                '       <img src="' + data.push_message.cover + '">' +
                                                '   </div>' +
                                                '   <div class="item item-custom">' +
                                                '       <span>' + data.push_message.text + '</span>' +
                                                '   </div>' +
                                                '</div>'
                                        };

                                        if (data.push_message.action_value) {
                                            dialog_data.okText = $translate.instant("View");

                                            $ionicPopup.confirm(dialog_data).then(function (res) {
                                                if (res) {
                                                    if($rootScope.isOffline) {
                                                        return $rootScope.onlineOnly();
                                                    }
                                                    if (data.push_message.open_webview == true || data.push_message.open_webview == "true") {
                                                        window.open(data.push_message.action_value, "_blank", "location=yes");
                                                    } else {
                                                        $location.path(data.push_message.action_value);
                                                    }
                                                }

                                                $rootScope.has_popup = false;
                                            });

                                            $rootScope.has_popup = true;
                                        } else {
                                            $ionicPopup.alert(dialog_data);
                                        }

                                    }

                                }

                                //Loading last InappMessage
                                if (data.inapp_message) {
                                    $ionicPopup.show({
                                        title: data.inapp_message.title,
                                        cssClass: "push-popup",
                                        template: '<div class="list card">' +
                                            '   <div class="item item-image' + (data.inapp_message.cover ? '' : ' ng-hide') + '">' +
                                            '       <img src="' + data.inapp_message.cover + '">' +
                                            '   </div>' +
                                            '   <div class="item item-custom">' +
                                            '       <span>' + data.inapp_message.text + '</span>' +
                                            '   </div>' +
                                            '</div>'
                                        ,

                                        buttons: [
                                            {
                                                text: $translate.instant("OK"),
                                                type: "button-custom",
                                                onTap: function () {
                                                    Push.markInAppAsRead();
                                                }
                                            }
                                        ]
                                    });
                                }
                            }
                        });
                    });

                    Application.loaded = true;

                    /** OVERVIEW */
                    $rootScope.isOverview = isOverview;
                    if ($rootScope.isOverview) {

                        $window.isHomepage = function () {
                            return $location.path() == BASE_PATH;
                        };

                        $window.clearCache = function (url) {
                            $templateCache.remove(BASE_PATH + "/" + url);
                        };

                        $window.reload = function (path) {

                            if (!path || path == $location.path()) {
                                $ionicHistory.clearCache();
                                $state.reload();
                            }
                        };

                        $window.reloadTabbar = function () {
                            HomepageLayout.unsetData();
                        };

                        $window.setPath = function (path, replace) {
                            if ($window.isSamePath(path)) {
                                $window.reload();
                            } else if (path.length) {
                                $timeout(function () {
                                    $location.path(path);
                                    if (replace) {
                                        $location.replace();
                                    }
                                });
                            }
                        };

                        $window.getPath = function () {
                            return $location.path();
                        };

                        $window.isSamePath = function (path) {
                            return $location.path() == path;
                        };

                        $window.showHomepage = function () {
                            if (HomepageLayout.properties.menu.visibility == "homepage") {
                                $window.setPath(BASE_PATH);
                            } else {
                                HomepageLayout.getFeatures().then(function (features) {
                                    if (features.options[0]) {
                                        $window.setPath(features.options[0].path);
                                    }
                                });
                            }
                        };

                        $window.back = function () {
                            /** If go back is home */
                            $ionicHistory.goBack();
                        };

                        $window.setLayoutId = function (value_id, layout_id) {
                            HomepageLayout.setLayoutId(value_id, layout_id);
                        };

                    }

                    /** Web apps manifest */
                    if (!$rootScope.isOverview && Application.is_webview) {
                        //Here, we are in webapp mode
                        //So we can generate all webapp meta and manifest for android
                        Application.generateWebappConfig().success(function (data) {
                            var head = angular.element(document.querySelector('head'));
                            var last_meta = $window.document.getElementById('last_meta');
                            var url_root = DOMAIN;

                            if (data.icon_url) {
                                head.append('<link rel="apple-touch-icon" href="' + url_root + data.icon_url + '" />');
                                head.append('<link rel="icon" sizes="192x192" href="' + url_root + data.icon_url + '" />');
                            }

                            if (data.manifest_url) {
                                head.append('<link rel="manifest" href="' + url_root + data.manifest_url + '">');
                            }

                            if (data.startup_image_url) {
                                head.append('<link rel="apple-touch-startup-image" href="' + url_root + data.startup_image_url + '" />');
                            }
                        });
                    }

                });

            });
        });

/** Dev helpers */
/**
 * Log || Throw errors
 *
 * @param message
 * @param level value are ''
 * @private
 */

var isOverview = window.parent.location.href != window.location.href;

sbLog = function (/** [...] */) {
    var debug = true;
    /** set to false in prod */
    if (!debug) {
        return;
    }

    var args = arguments;
    var levels = new Array('info', 'debug', 'warning', 'error', 'exception', 'throw');
    var log = Function.prototype.bind.call(console.log, console);

    /** Assuming the last parameter could be the log level */
    var level = 'info';
    if (levels.indexOf(args[args.length - 1]) != -1) {
        var level = args[args.length - 1];
    }

    switch (level) {
    case 'exception':
    case 'throw':
        throw level + " >> " + args;
        break;
    case 'error':
    case 'warning':
    case 'debug':
    case 'info':
    default:
        log.apply(console, args);
        break;
    }
};
