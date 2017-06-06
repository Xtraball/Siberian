App.service("LinkService", function ($ionicPlatform, $rootScope, $translate, $window, Application) {
    return {
        openLink: function(url, options) {

            if($rootScope.isOverview) {
                $rootScope.showMobileFeatureOnlyError();
                return false;
            }
            if($rootScope.isOffline) {
                $rootScope.onlineOnly();
                return false;
            }

            //set default options (inapp + navbar)
            if(options === undefined) {
                options = {
                    'hide_navbar':true,
                    'use_external_app':false
                }
            }

            //by default use inappbrowser
            var target = "_blank";
            var inAppBrowserOptions = [];

            switch(true) {

                //On android, tel link are opened in current app
                case (/^(tel:).*/.test(url) && ionic.Platform.isAndroid()) :
                    target = "_self";
                    break;

                case options.use_external_app:
                //if PDF, we force use of external application
                case /.*\.pdf($|\?)/.test(url):
                //On iOS, you cannot hidenavbar and show inappbrowser
                case (options.hide_navbar && ionic.Platform.isIOS()):
                    target = "_system";
                    inAppBrowserOptions.push("EnableViewPortScale=yes");
                    break;

                default: 
                   if(options && (options.hide_navbar)) {
                        inAppBrowserOptions.push('location=no');
                        inAppBrowserOptions.push('toolbar=no');
                    } else { //else use standard inAppBrowse with navbar
                        inAppBrowserOptions.push('location=no');
                        inAppBrowserOptions.push('closebuttoncaption='+$translate.instant("Done"));
                        inAppBrowserOptions.push('transitionstyle=crossdissolve');
                        inAppBrowserOptions.push('toolbar=yes');
                    }

            }
            $window.open(url, target, inAppBrowserOptions.join(","));
        }
    };
});
