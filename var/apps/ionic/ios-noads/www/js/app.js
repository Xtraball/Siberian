/*global
    angular, localStorage, DOMAIN, console, cordova, StatusBar, BASE_PATH, device, ionic, chcp
*/

console.info((new Date()).getTime(), "start.");

var isOverview = (window.parent.location.href !== window.location.href); /** isOverview stands for the App overview in the Browser. */
var isNativeApp = (ionic.Platform.isIOS() || ionic.Platform.isAndroid()); /** isNativeApp stands for Android or iOS native application only */

// WARNING: If angular module name is changed here, change it also in utils/features.js
var App = angular.module("starter", [
        "ionic", "ion-gallery", "ngCordova", "ngIOS9UIWebViewPatch", "angular-carousel",
        "lodash", "ngImgCrop", "ionic-zoom-view", "ngSanitize", "tmh.dynamicLocale", "ngQueue"
    ])
    .constant("$ionicLoadingConfig", {
        template: "<ion-spinner></ion-spinner>"
    })
    .config(function ($compileProvider, $httpProvider, $ionicConfigProvider, $logProvider, $sbhttpProvider, UrlProvider,
                      tmhDynamicLocaleProvider) {

        var Url = UrlProvider.$get();

        var locale_url = Url.get("/app/sae/modules/Application/resources/angular-i18n/angular-locale_{{locale}}.js", {
            remove_key: true
        });

        tmhDynamicLocaleProvider.localeLocationPattern(locale_url);
        tmhDynamicLocaleProvider.storageKey((+new Date())*Math.random()+""); // don't remember locale

        $sbhttpProvider.alwaysCache = true;

        $logProvider.debugEnabled(false);

        //Add hook on HTTP transactions
        $httpProvider.interceptors.push(function ($q, $injector, $log) {
            return {
                request: function (config) {
                    var sid = localStorage.getItem("sb-auth-token");
                    if (sid && (config.url.indexOf(".html") === -1) && $injector.get('Connection').isOnline) {
                        //Force cookie
                        if (config.url.indexOf(DOMAIN) > -1 && config.noSbToken !== true) {
                            config.url = config.url + "?sb-token=" + sid;
                        }
                    }
                    return config;
                },
                responseError: function (response) {
                    if(response.config.url.match(/(templates|layout\/home)\/.*\.html$/) && (response.config.url !== "templates/home/l6/view.html")) {
                        response.config.url = "templates/home/l6/view.html";
                        $log.debug("System: An error occured while loading your Layout template, fallback on Layout 6.");
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
    .run(function ($sbhttp, $ionicConfig, $ionicHistory, $ionicPlatform, $ionicPopup, $ionicSlideBoxDelegate,
                   $ionicScrollDelegate, $injector, $log, $location, $rootScope, $state, $templateCache, $timeout,
                   $translate, $window, AdmobService, Analytics, Application, Connection, Customer, Dialog, FacebookConnect, Facebook,
                   Padlock, Push, Url, tmhDynamicLocale, AUTH_EVENTS, PUSH_EVENTS) {

        $log.debug((new Date()).getTime(), "run start");

        // native app, or webview/browser
        Dialog.is_webview       = !isNativeApp;
        Application.is_webview  = !isNativeApp;
        $rootScope.isNativeApp  = isNativeApp;

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

        $ionicPlatform.ready(function() {

            $log.debug((new Date()).getTime(), "$ionicPlatform.ready");

            var sid = localStorage.getItem("sb-auth-token");

            if ($window.device) {
                Push.device_uid = device.uuid;
            }

            $window.cordova = $window.cordova || {};
            $window.device = $window.device || {};

            $window.Connection = Connection;

            $log.debug((new Date()).getTime(), "start: front/mobile/loadv2");
            $sbhttp.get(
                Url.get("front/mobile/loadv2", {
                    add_language: true,
                    sid: sid,
                    device_uid: Push.device_uid
                }), {
                    timeout: 10000,
                    cache: !isOverview
                }).then(function (response) {

                    $log.debug((new Date()).getTime(), "end: front/mobile/loadv2");

                    var data = response.data.load;
                    /** Translations & locale */
                    $translate.translations = response.data.translation;
                    tmhDynamicLocale.set($translate.translations._locale);

                    var Pages = $injector.get("Pages");
                    Pages.data = response.data.homepage;
                    Application.default_background = data.homepage_image; /** Small base64 default image, while loading the real deal */

                    var HomepageLayout = $injector.get("HomepageLayout");

                    if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
                        cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
                    }

                    if (window.StatusBar) {
                        StatusBar.styleDefault();
                    }

                    Push.startBackgroundGeolocation();

                    $rootScope.app_is_loaded        = true;
                    $rootScope.has_popup            = false;
                    $rootScope.app_is_bo_locked     = false;

                    /** WebRTC for iOS */
                    if (window.device.platform === 'iOS') {
                        cordova.plugins.iosrtc.registerGlobals();
                    }

                    $rootScope.fetchupdatetimer = null;

                    $ionicPlatform.on('resume', function (result) {

                        /** If app goes live too fast, cancel the update */
                        $timeout.cancel($rootScope.fetchupdatetimer);

                        $log.info("-- app is resumed --");
                        Analytics.storeOpening().then(function (result) {
                            Analytics.data.storeClosingId = result.id;
                        });
                    });

                    // hello
                    $ionicPlatform.on('pause', function (result) {
                        $log.info("-- app is on pause --");
                        Analytics.storeClosing();

                        /** When app goes in pause, try to install if required. */
                        if(typeof chcp !== "undefined") {

                            $rootScope.fetchupdatetimer = $timeout(function() {
                                if (localStorage.getItem("install-update" === true)) {

                                    chcp.isUpdateAvailableForInstallation(function (error, data) {
                                        if (error) {
                                            $log.info("CHCP: Nothing to install");
                                            $log.info("CHCP: " + error.description);
                                            return;
                                        }

                                        // update is in cache and can be installed - install it
                                        $log.info("CHCP: Current version: " + data.currentVersion);
                                        $log.info("CHCP: About to install: " + data.readyToInstallVersion);
                                        chcp.installUpdate(function (error) {
                                            if (error) {
                                                $log.info("CHCP: Something went wrong with the update, will retry later.");
                                                $log.info("CHCP: " + error.description);
                                            } else {
                                                return;
                                            }
                                        });
                                    });

                                } else {
                                    chcp.fetchUpdate(function (error, data) {
                                        if (error) {
                                            $log.info("CHCP: Failed to load the update with error code: " + error.code);
                                            $log.info("CHCP: " + error.description);
                                            localStorage.setItem("install-update", false);
                                        } else {
                                            $log.info("CHCP: Update success, trying to install.");

                                            // update is in cache and can be installed - install it
                                            $log.info("CHCP: Current version: " + data.currentVersion);
                                            $log.info("CHCP: About to install: " + data.readyToInstallVersion);
                                            chcp.installUpdate(function (error) {
                                                if (error) {
                                                    $log.info("CHCP: Something went wrong with the update, will retry later.");
                                                    $log.info("CHCP: " + error.description);
                                                } else {
                                                    $log.info("CHCP: Update successfully install, restarting new files.");
                                                    localStorage.setItem("install-update", false);
                                                    return;
                                                }
                                            });
                                        }
                                    });
                                }

                            }, 5000);
                        }
                    });

                    if (!!data.application.is_bo_locked) {
                        $rootScope.app_is_bo_locked = true;

                        $ionicHistory.nextViewOptions({
                            disableBack: true
                        });
                        $state.go("locked");
                    }

                    if((data.application.ios_status_bar_is_hidden && ionic.Platform.isIOS()) || (data.application.android_status_bar_is_hidden && ionic.Platform.isAndroid())) {
                        if(typeof window.StatusBar !== "undefined") {
                            window.StatusBar.hide();
                        }
                    }

                    /** Append custom CSS/SCSS to the page. */
                    if (response.data.css && response.data.css.css) {
                        var css = document.createElement("style");
                        css.type = "text/css";
                        css.innerHTML = response.data.css.css;
                        document.body.appendChild(css);
                    }

                    Customer.id                             = data.customer.id;
                    Customer.can_access_locked_features     = data.customer.can_access_locked_features;
                    Customer.can_connect_with_facebook      = data.customer.can_connect_with_facebook;
                    Customer.saveCredentials(data.customer.token);

                    Application.app_id              = data.application.id;
                    Application.app_name            = data.application.name;
                    Application.privacy_policy      = data.application.privacy_policy;
                    Application.googlemaps_key      = data.application.googlemaps_key;
                    Application.is_locked           = data.application.is_locked;
                    Application.offline_content     = data.application.offline_content;

                    if ($rootScope.isNativeApp) {
                        if(!$window.localStorage.getItem("first_running")) {
                            $window.localStorage.setItem("first_running", "true");
                            Analytics.storeInstallation();
                        }

                        if(Application.offline_content) {
                            Application.showCacheDownloadModalOrUpdate();
                        }
                    }

                    Analytics.storeOpening().then(function (result) {
                        if (result && result.id) {
                            Analytics.data.storeClosingId = result.id;
                        }
                    });

                    $rootScope.app_is_locked = Application.is_locked && !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode);

                    $window.colors = data.application.colors;

                    if (data.application.facebook.id) {
                        FacebookConnect.permissions = (!Array.isArray(data.application.facebook.scope)) ?
                            new Array(data.application.facebook.scope) : data.application.facebook.scope;
                        FacebookConnect.app_id = data.application.facebook.id;
                    }

                    try {
                        AdmobService.init(data.application.admob_v2);
                    } catch(error) {
                        $log.error("Unable to init AdMob.");
                    }


                    if (Customer.isLoggedIn()) {
                        $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                    } else {
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
                    }); // !push & in-app message


                    /** Finalize init */

                    /** rootScope features */
                    $rootScope._getLastId = function (collection) {
                        var last_id = null;
                        for (var i = 0; i < collection.length; i++) {
                            if (!last_id || collection[i].id > last_id) {
                                last_id = collection[i].id;
                            }
                        }
                        return last_id;
                    };

                    $rootScope._getFirstId = function (collection) {
                        var first_id = null;
                        for (var i = 0; i < collection.length; i++) {
                            if (!first_id || collection[i].id < first_id) {
                                first_id = collection[i].id;
                            }
                        }
                        return first_id;
                    };

                    //cyril: RIDICULOUS CODE, in browser we use _system that is not accepted value
                    //in application we open with _blank that open with inAppBrowser without control...
                    $rootScope.getTargetForLink = function () {
                        return !$rootScope.isNativeApp ? "_system" : "_blank";
                    };

                    /** Handler for overview */
                    $rootScope.$on('$stateChangeSuccess', function (event, toState, toStateParams, fromState, fromStateParams) {
                        if(parent && (typeof parent.postMessage === "function") && (parent !== window)) {
                            parent.postMessage("state.go", DOMAIN);
                        }

                        if($ionicHistory.currentStateName() === "home") {
                            $timeout(function() {
                                HomepageLayout.callHooks();
                            }, 250);
                        }
                    });

                    /** Event to catch state-go from source code */
                    var eventMethod     = window.addEventListener ? "addEventListener" : "attachEvent";
                    var eventer         = window[eventMethod];
                    var messageEvent    = (eventMethod === "attachEvent") ? "onmessage" : "message";

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

                        $rootScope.app_is_locked = Application.is_locked && !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode);
                        if ($rootScope.app_is_locked && (toState.name != "padlock-view")) {
                            event.preventDefault();
                            $state.go("padlock-view");

                        } else if (Customer.can_access_locked_features && (toState.name == "padlock-view")) {
                            event.preventDefault();

                        } else if (!$rootScope.isNativeApp && (toState.name == "codescan")) {
                            event.preventDefault();

                            $rootScope.showMobileFeatureOnlyError();
                        } else if (Connection.isOffline) {
                            // Check if app feature is accessible offline
                            void(0);
                        }
                    });

                    window.rootScope = $rootScope;

                    $window.addEventListener("online", function () {
                        $log.info('online');
                    });

                    $window.addEventListener("offline", function () {
                        $log.info('offline');
                    });

                    $rootScope.onlineOnly = function() {
                        Dialog.alert(
                            $translate.instant("Offline mode"),
                            $translate.instant("This feature is not available in offline mode!"),
                            $translate.instant("OK")
                        );
                    };

                    $rootScope.$on(AUTH_EVENTS.loginSuccess, function () {
                        $rootScope.app_is_locked = (Application.is_locked && !(Customer.can_access_locked_features || Padlock.unlocked_by_qrcode));
                        if (!$rootScope.app_is_locked && Application.is_locked) {
                            $state.go("home");
                        }
                    });

                    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function () {

                        $rootScope.app_is_locked = (Application.is_locked && !Padlock.unlocked_by_qr_code);

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
                        }, 2350);
                        return;
                    };

                    $rootScope.unlockUpdate = 0;
                    $rootScope.checkForUpdate = function() {

                        if(!$rootScope.isNativeApp) {
                            $log.info("Stop update, Android or iOS is required.");
                            return;
                        }

                        if($rootScope.unlockUpdate < 5) {
                            $rootScope.unlockUpdate += 1;
                            return;
                        }

                        $rootScope.unlockUpdate = 0;

                        chcp.fetchUpdate(function (error, data) {
                            if (error) {
                                $log.info("CHCP: Failed to load the update with error code: " + error.code);
                                $window.alert("CHCP: " + error.description);
                            } else {
                                $window.alert("CHCP: Update success, trying to install.");

                                // update is in cache and can be installed - install it
                                $log.info("CHCP: Current version: " + data.currentVersion);
                                $log.info("CHCP: About to install: " + data.readyToInstallVersion);
                                chcp.installUpdate(function (error) {
                                    if (error) {
                                        $log.info("CHCP: Something went wrong with the update, will retry later.");
                                        $window.alert("CHCP: " + error.description);
                                    } else {
                                        $window.alert("CHCP: Update successfully install, restarting new files.");
                                        return;
                                    }
                                });
                            }
                        });
                    };

                    /** OVERVIEW */
                    $rootScope.isOverview = isOverview;
                    if ($rootScope.isOverview) {

                        $window.isHomepage = function () {
                            return ($location.path() === BASE_PATH);
                        };

                        $window.clearCache = function (url) {
                            $templateCache.remove(BASE_PATH + "/" + url);
                        };

                        $window.reload = function (path) {

                            if (!path || (path === $location.path())) {
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
                            return ($location.path() === path);
                        };

                        $window.showHomepage = function () {
                            console.log("showHomepage");
                            if (HomepageLayout.properties.menu.visibility === "homepage") {
                                $window.setPath(BASE_PATH);
                            } else {
                                HomepageLayout.getFeatures().then(function (features) {
                                    $ionicHistory.nextViewOptions({
                                        historyRoot: true,
                                        disableAnimate: false
                                    });
                                    var feat_index = 0;
                                    for(var fi = 0; fi < features.options.length; fi++) {
                                        var feat = features.options[fi];
                                        /** Don't load unwanted features on first page. */
                                        if((feat.code !== "code_scan") && (feat.code !== "radio") && (feat.code !== "padlock")) {
                                            feat_index = fi;
                                            break;
                                        }
                                    }

                                    if (features.options[fi]) {
                                        $window.setPath(features.options[fi].path, true);
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
                        //Move the webapp config inside the cache
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

                    Application.loaded = true;
                    $log.info((new Date()).getTime(), "end.");


                    // Check for padlock
                    var currentState = $ionicHistory.currentStateName();
                    if ($rootScope.app_is_locked && (currentState !== "padlock-view")) {
                        $state.go("padlock-view");
                    }

                }).catch(function(error) {
                    $log.error("main promise caught error, ", error);
                }); // Main load, then

        });
    });

/***
 * @deprecated, do nothing to be replaced with $log
 */
sbLog = function (/** [...] */) {};
