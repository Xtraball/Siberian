var App = angular.module('starter', ['ionic', 'ion-gallery', 'ngCordova', 'ngIOS9UIWebViewPatch', 'angular-carousel', 'lodash', 'ngImgCrop', 'ionic-zoom-view'])
    //Add spinner template
    .constant("$ionicLoadingConfig", {
        template: "<ion-spinner></ion-spinner>"
    })
    .config(function ($compileProvider, $httpProvider, $ionicConfigProvider) {
        //Add hook on HTTP transactions
        $httpProvider.interceptors.push(function($q, $injector) {
            return {
                request: function(config) {
                    var sid = localStorage.getItem("sb-auth-token");
                    if(sid && config.url.indexOf(".html") == -1 && $injector.get('Connection').isOnline) {
                        //Force cookie
                        if(config.url.indexOf(DOMAIN) > -1) {
                            config.url = config.url + "?sb-token=" + sid;
                        }
                    }
                    return config;
                },
                responseError: function(response) {
                    if((response.status == 0) && (typeof OfflineMode == "object") && !$injector.get('Connection').check()) {
                        $injector.get('Connection').check();
                    }
                    return $q.reject(response);
                }
            };
        });

        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|map|geo|skype|tel|file|smsto):/);

        $httpProvider.defaults.withCredentials = true;

        if(isOverview) {
            $ionicConfigProvider.views.maxCache(0);
        }
    })
    .run(function($http, $ionicConfig, $ionicHistory, $ionicPlatform, $ionicPopup, $ionicSlideBoxDelegate, $ionicScrollDelegate, $location, $rootScope, $state, $templateCache, $timeout, $translate, $window, Analytics, Application, Connection, Customer, Dialog, FacebookConnect, Facebook, HomepageLayout, Push, Url, AUTH_EVENTS, PUSH_EVENTS) {
        //Load translation is mandatory to any process
        $translate.findTranslations().success(function () {}).finally(function(){
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

                if($window.device) {
                    Push.device_uid = device.uuid;
                }

                Push.startBackgroundGeolocation();

                $rootScope.app_is_loaded = true;
                $rootScope.has_popup = false;
                $rootScope.app_is_bo_locked = false;

                if(!Application.is_webview) {
                    Connection.check();
                }

                $ionicPlatform.on('resume', function(result) {
                    sbLog("## App is resumed ##");
                    Analytics.storeOpening().then(function(result) {
                        Analytics.data.storeClosingId = result.id;
                    });
                });

                // hello

                $ionicPlatform.on('pause', function(result) {
                    sbLog("## App is on pause ##");
                    Analytics.storeClosing();
                });

            });

            // Utility for layout 17.
            $rootScope.resizeLayout17 = function() {

                $timeout(function() {
                    var scrollview = document.getElementById('metro-scroll');
                    if(scrollview) {
                        scrollview.style.display = "block";
                    }
                    var element = document.getElementById('metro-line-2');
                    if(element) {
                        var positionInfo = element.getBoundingClientRect();
                        element.style.height = positionInfo.width/4+"px";
                    }
                    /// In case 100 ms was too short.
                    $timeout(function() {
                        var scrollview = document.getElementById('metro-scroll');
                        if(scrollview) {
                            scrollview.style.display = "block";
                        }
                    }, 1000);
                }, 1500);

            };

            window.l17 = $rootScope.resizeLayout17;

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

            $rootScope.getTargetForLink = function() {
                return Application.is_webview ? "_system" : "_blank";
            };

            $rootScope.$on('$stateChangeStart', function (event, toState, toStateParams, fromState, fromStateParams) {

                if($rootScope.app_is_locked && toState.name != "padlock-view") {
                    event.preventDefault();
                    $state.go("padlock-view");
                } else if(Customer.can_access_locked_features && toState.name == "padlock-view") {
                    event.preventDefault();
                } else if(Application.is_webview && toState.name == "codescan") {
                    event.preventDefault();
                }
            });

            $window.addEventListener("online", function() {
                sbLog('online');
                Connection.check();
            });

            $window.addEventListener("offline", function() {
                sbLog('offline');
                Connection.check();
            });

            $rootScope.$on(AUTH_EVENTS.loginSuccess, function() {
                $rootScope.app_is_locked = Application.is_locked && !Customer.can_access_locked_features;
                if(!$rootScope.app_is_locked && Application.is_locked) {
                    $state.go("home");
                }
            });

            $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() {

                $rootScope.app_is_locked = Application.is_locked;

                if($rootScope.app_is_locked) {
                    $ionicHistory.nextViewOptions({
                        disableBack: true
                    });
                    $state.go("padlock-view");
                }

            });

            $rootScope.$on('$ionicView.beforeEnter', function() {
                if($location.path() == ("/" + APP_KEY)) {
                    $ionicSlideBoxDelegate.update();
                } else {
                    Analytics.storeClosing();
                }
            });

            $rootScope.$on('$ionicView.afterEnter', function() {
                if($location.path() == ("/" + APP_KEY)) {
                    $rootScope.resizeLayout17();
                }
            });

            $rootScope.$on(PUSH_EVENTS.notificationReceived, function(event, data) {

                if(!$rootScope.has_popup) {

                    if (data.additionalData.cover || data.additionalData.action_value) {

                        var dialog_data = {
                            okText: $translate.instant("View"),
                            cancelText: $translate.instant("Cancel"),
                            cssClass: "push-popup",
                            title: data.title,
                            template:
                            '<div class="list card">' +
                            '   <div class="item item-image' + (data.additionalData.cover ? '' : ' ng-hide') + '">' +
                            '       <img src="' + (DOMAIN + data.additionalData.cover) + '">' +
                            '   </div>' +
                            '   <div class="item item-custom">' +
                            '       <span>' + data.message + '</span>' +
                            '   </div>' +
                            '</div>'
                        };

                        if(data.additionalData.action_value) {
                            $ionicPopup.confirm(dialog_data).then(function (res) {
                                if (res) {
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

            $rootScope.showMobileFeatureOnlyError = function() {
                var popup = $ionicPopup.show({
                    title: $translate.instant("Error"),
                    subTitle: $translate.instant("This feature is available from the application only")
                });
                $timeout(function() {
                    popup.close();
                }, 4000);
                return;
            };

            var sid = localStorage.getItem("sb-auth-token");

            //get & process app data
            $http.get(Url.get("front/mobile/load", { add_language: true, sid: sid })).success(function (data) {

                if(data.application.is_bo_locked == 1) {
                    $rootScope.app_is_bo_locked = true;
                }

                if (data.css) {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = data.css;
                    document.head.appendChild(link);
                }

                Customer.id = data.customer.id;
                Customer.can_access_locked_features = data.customer.can_access_locked_features;
                Customer.can_connect_with_facebook = data.customer.can_connect_with_facebook;
                Customer.saveCredentials(data.customer.token);

                Application.app_id = data.application.id;
                Application.app_name = data.application.name;
                Application.is_locked = data.application.is_locked == 1;

                if(!Application.is_webview && !$window.localStorage.getItem("first_running")) {
                    Application.showCacheDownloadModal(); /** @TODO Make this popup optional */
                    $window.localStorage.setItem("first_running", "true");
                    Analytics.storeInstallation();
                }

                Analytics.storeOpening().then(function(result) {
                    if(result && result.id) {
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

                if(!Application.is_webview && admob.id && $window.AdMob) {
                    sbLog("admob, ", admob);
                    if(admob.type == "banner") {
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
                Push.register();
                Push.getLastMessages().success(function (data) {
                    if (data && !$rootScope.has_popup) {

                        //Loading last push
                        if (data.push_message) {

                            if (data.push_message.cover || data.push_message.action_value) {

                                var dialog_data = {
                                    title: data.push_message.title,
                                    template:
                                    '<div class="list card">' +
                                    '   <div class="item item-image' + (data.push_message.cover ? '' : ' ng-hide') + '">' +
                                    '       <img src="' + data.push_message.cover + '">' +
                                    '   </div>' +
                                    '   <div class="item item-custom">' +
                                    '       <h2>' + data.push_message.text + '</h2>' +
                                    '   </div>' +
                                    '</div>'
                                };

                                if(data.push_message.action_value) {
                                    dialog_data.okText = $translate.instant("View");

                                    $ionicPopup.confirm(dialog_data).then(function (res) {
                                        if (res) {
                                            if (data.push_message.open_webview == true) {
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
                                template:
                                '<div class="list card">' +
                                '<div class="item item-image' + (data.inapp_message.cover ? '' : ' ng-hide') + '">' +
                                '<img src="' + data.inapp_message.cover + '">' +
                                '</div>' +
                                '<div class="item item-custom">' +
                                '<h2>' + data.inapp_message.text + '</h2>' +
                                '</div>' +
                                '</div>'
                                ,
                                buttons: [
                                    {
                                        text: $translate.instant("OK"),
                                        type: "button-custom",
                                        onTap: function() {
                                            Push.markInAppAsRead();
                                        }
                                    }
                                ]
                            });
                        }
                    }
                });

                // Avoid horizontal layout bad sizing
                $ionicSlideBoxDelegate.update();

            });


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
                        $ionicHistory.clearCache()
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
                    $ionicHistory.goBack();
                };

                $window.setLayoutId = function (value_id, layout_id) {
                    HomepageLayout.setLayoutId(value_id, layout_id);
                };

            } else {
                if(Application.is_webview) {
                    //Here, we are in webapp mode
                    //So we can generate all webapp meta and manifest for android
                    Application.generateWebappConfig().success(function(data) {
                        var head = angular.element(document.querySelector('head'));
                        var last_meta = $window.document.getElementById('last_meta');
                        var url_root = DOMAIN;

                        if(data.icon_url) {
                            head.append('<link rel="apple-touch-icon" href="' + url_root + data.icon_url + '" />');
                            head.append('<link rel="icon" sizes="192x192" href="' + url_root + data.icon_url + '" />');
                        }

                        if(data.manifest_url) {
                            head.append('<link rel="manifest" href="' + url_root + data.manifest_url + '">');
                        }

                        if(data.startup_image_url) {
                            head.append('<link rel="apple-touch-startup-image" href="' + url_root + data.startup_image_url + '" />');
                        }
                    });
                }
            }
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

sbLog = function(/** [...] */) {
    var debug = true; /** set to false in prod */
    if(!debug) { return; }

    var args = arguments;
    var levels = new Array('info', 'debug', 'warning', 'error', 'exception', 'throw');
    var log = Function.prototype.bind.call(console.log, console);

    /** Assuming the last parameter could be the log level */
    var level = 'info';
    if(levels.indexOf(args[args.length-1]) != -1) {
        var level = args[args.length-1];
    }

    switch (level) {
        case 'exception': case 'throw':
        throw level+" >> "+args;
        break;
        case 'error': case 'warning': case 'debug': case 'info': default:
        log.apply(console, args);
        break;
    }
};
