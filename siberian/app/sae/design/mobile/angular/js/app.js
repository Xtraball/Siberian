var App = angular.module("Siberian", ['ngRoute', 'ngAnimate', 'ngTouch', 'angular-carousel', 'ngResource', 'ngSanitize', 'ngFacebook']);

App.run(function($rootScope, $window, $route, $location, $timeout, $interval, $templateCache, Connection, Customer, Application, Padlock, Message, $http, AUTH_EVENTS, Url, LayoutService, Translator) {

    Connection.check();

    FastClick.attach($window.document);

    $window.Connection = Connection;

    $window.refreshPageSize = function() {
        $rootScope.$broadcast("refreshPageSize");
    }

    $rootScope.isOverview = $window.parent.location.href != $window.location.href;

    if($rootScope.isOverview) {

        $window.isHomepage = function() {
            return $location.path() == ORIG_URL;
        };

        $window.clearCache = function(url) {
            $templateCache.remove(BASE_URL+"/"+url);
        };

        $window.reload = function(path) {

            if(!path || path == $location.path()) {
                if(angular.isObject($route.current.scope) && angular.isFunction($route.current.scope.reload)) {
                    $route.current.scope.reload();
                }
                $rootScope.direction = null;
                $route.reload();
            }
        };

        $window.reloadTabbar = function() {
            LayoutService.unsetData();
        };

        $window.setLayoutId = function (value_id, layout_id) {
            LayoutService.setLayoutId(value_id, layout_id);
        };

        $window.setPath = function(path, replace) {
            if($window.isSamePath(path)) {
                $window.reload();
            } else if(path.length) {
                $timeout(function() {
                    $location.path(path);
                    if(replace) {
                        $location.replace();
                    }
                });
            }
        };

        $window.getPath = function() {
            return $location.path();
        };

        $window.isSamePath = function(path) {
            return $location.path() == path;
        };

        $window.showHomepage = function() {
            if(LayoutService.properties.menu.visibility == "homepage") {
                $window.setPath(ORIG_URL);
            } else {
                LayoutService.getFeatures().then(function (features) {
                    if (features.options[0]) {
                        $window.setPath(features.options[0].path);
                    }
                });
            }
        };

        $window.back = function(path) {
            $window.history.back();
        };

        Translator.findTranslations();

    } else {
        $timeout(function() {
            console.log("URL: ", Url.get("application/mobile_template/findall"));
            $http({
                method: 'GET',
                url: Url.get("/application/mobile_template/findall"),
                cache: true,
                responseType:'json'
            }).success(function(templates) {
                for(var i in templates) {
                    $templateCache.put(i, templates[i]);
                }
            });

            Translator.findTranslations();
        }, 500);
    }

    if(!$rootScope.isOverview) {
        $rootScope.$on('$routeChangeStart', function (event, current, previous) {

            if(Application.is_locked) {
                $rootScope.current_page_is_locked = (!Customer.can_access_locked_features && !Padlock.unlock_by_qrcode)
                    && $location.path().indexOf(Url.get("customer/mobile_account")) == -1;

                if($rootScope.current_page_is_locked) {
                    Application.isLoaded();
                }

            }

        });
    }

    $rootScope.$on('$routeChangeStart', function(event, next, current) {
        $rootScope.app_loader_is_visible = true;
    });
    $rootScope.$on('$routeChangeError', function(event, next, current) {
        $rootScope.app_loader_is_visible = false;
    });

    $rootScope.$on('processSnapshots', function() {
        // Removing modals
        angular.element(document.getElementsByClassName('modal')).remove();

        var i = 0;
        $interval(function() {
            $location.path(Application.snapshot_tabbar_items[i].path);
            i++;
        }, 8000, 3);
    });

    $rootScope.$on('$locationChangeStart', function(event, newUrl, oldUrl) {

        if(newUrl.indexOf("mcommerce/mobile_sales_success") >= 0) {
            if(oldUrl == APP_URL) {
                event.preventDefault();
                return;
            }
        }

        $rootScope.previousLocation = oldUrl;
        $rootScope.nextLocation = newUrl;
        $rootScope.actualLocation = $location.path();
    });

    $rootScope.$on(AUTH_EVENTS.notAuthenticated, function() { $rootScope.customerIsLoggedIn = false; });
    $rootScope.$on(AUTH_EVENTS.logoutSuccess, function() { $rootScope.customerIsLoggedIn = false; });
    $rootScope.$on(AUTH_EVENTS.loginSuccess, function() { $rootScope.customerIsLoggedIn = true; });
    $rootScope.$on("application_state_changed", function() { $rootScope.current_page_is_locked = false; });

    $rootScope.$watch(function () {return $location.path()}, function (newLocation, oldLocation) {

        if(oldLocation.substr(oldLocation.length -1, 1) == "/") {
            oldLocation = oldLocation.substr(0, oldLocation.length -1);
        }
        if(newLocation.substr(newLocation.length -1, 1) == "/") {
            newLocation = newLocation.substr(0, newLocation.length -1);
        }

        //if(oldLocation == newLocation || (LayoutService.properties.layoutId == "l9" && LayoutService.properties.options.isRootPage)) {
        if(oldLocation == newLocation || LayoutService.properties.menu.visibility == "always" || Application.is_android) {
            $rootScope.direction = 'fade';
        } else if($rootScope.actualLocation === newLocation) {
            $rootScope.direction = 'to-right';
        } else {
            $rootScope.direction = 'to-left';
        }
    });

    $rootScope.$on('$routeChangeSuccess', function(event, current, previous) {

        $rootScope.app_loader_is_visible = false;
        $rootScope.code = current.code;
        LayoutService.app.fireLocationChanged();

        if($rootScope.isOverview) {
            $templateCache.removeAll();
        }

    });

    $window.addEventListener("online", function() {
        console.log('online');
        Connection.check();
    });

    $window.addEventListener("offline", function() {
        console.log('offline');
        Connection.check();
    });

    $rootScope.alertMobileUsersOnly = function() {
        this.message = new Message();
        this.message.isError(true)
            .setText("This section is unlocked for mobile users only")
            .show()
        ;
    };

}).config(function($routeProvider, $locationProvider, $httpProvider, $compileProvider) {

    $httpProvider.interceptors.push(function($q, $injector) {
        return {
            responseError: function(response) {
                if(response.status == 0) {
                    $injector.get('Connection').setIsOffline();
                }
                return $q.reject(response);
            }
        };
    });

    $locationProvider.html5Mode(true);

    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|geo|tel):/);

});

var ajaxComplete = function(data) {

};

window.getMaxScrollY = function() {
    return this.getHeight() - window.innerHeight;
};

window.getHeight = function() {
    return Math.max(
        document.body.scrollHeight, document.documentElement.scrollHeight,
        document.body.offsetHeight, document.documentElement.offsetHeight,
        document.body.clientHeight, document.documentElement.clientHeight
    );
};

if(typeof String.prototype.startsWith != 'function') {
    String.prototype.startsWith = function (str) {
        return this.substring(0, str.length) === str;
    }
}

if(typeof String.prototype.endsWith != 'function') {
    String.prototype.endsWith = function (str) {
        return this.substring(this.length - str.length, this.length) === str;
    }
}
