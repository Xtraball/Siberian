/*global
 angular, DEVICE_TYPE
 */

/**
 * LinkService
 *
 * @author Xtraball SAS
 */
angular.module("starter").service("LinkService", function ($rootScope, $translate, $window, SB) {
    return {
        openLink: function(url, options) {

            if($rootScope.isNotAvailableInOverview() || $rootScope.isNotAvailableOffline()) {
                return;
            }

            //set default options (inapp + navbar)
            /**
             * @todo maybe extend ?
             */
            if(options === undefined) {
                options = {
                    "hide_navbar"       : true,
                    "use_external_app"  : false
                };
            }

            //by default use inappbrowser
            var target = "_blank";
            var inAppBrowserOptions = [];

            switch(true) {

                //On android, tel link are opened in current app
                case (/^(tel:).*/.test(url) && (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID)) :
                    target = "_self";
                    break;

                case options.use_external_app:

                //if PDF, we force use of external application
                case (/.*\.pdf($|\?)/).test(url):

                //On iOS, you cannot hidenavbar and show inappbrowser
                case (options.hide_navbar && (DEVICE_TYPE === SB.DEVICE.TYPE_IOS)):
                    target = "_system";
                    inAppBrowserOptions.push("EnableViewPortScale=yes");
                    break;

                default: 
                    if(options && (options.hide_navbar)) {
                        inAppBrowserOptions.push("location=no");
                        inAppBrowserOptions.push("toolbar=no");
                    } else { //else use standard inAppBrowser with navbar
                        inAppBrowserOptions.push("location=no");
                        inAppBrowserOptions.push("closebuttoncaption=" + $translate.instant("Done"));
                        inAppBrowserOptions.push("transitionstyle=crossdissolve");
                        inAppBrowserOptions.push('toolbar=yes');
                    }

            }
            $window.open(url, target, inAppBrowserOptions.join(","));
        }
    };
});
