/**
 * LinkService
 *
 * @author Xtraball SAS
 */
angular
.module('starter')
.service('LinkService', function ($rootScope, $translate, $window, SB) {
    return {
        openLink: function (url, options) {
            // Disable when offline!
            if ($rootScope.isNotAvailableOffline()) {
                return;
            }

            options = angular.extend({}, {
                'hide_navbar': true,
                'use_external_app': false,
                'toolbarcolor': $window.colors.header.statusBarColor
            }, options);

            var target = '_blank';
            var inAppBrowserOptions = [];

            if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                target = '_system';
            }

            switch (true) {

                // On android, tel link are opened in current app
                case (/^(tel:).*/.test(url) && (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID)) :
                    target = '_self';
                    break;

                case options.use_external_app:

                // if PDF, we force use of external application
                case (/.*\.pdf($|\?)/).test(url):

                // On iOS, you cannot hidenavbar and show inappbrowser
                case (options.hide_navbar && (DEVICE_TYPE === SB.DEVICE.TYPE_IOS)):
                    target = '_system';
                    inAppBrowserOptions.push('EnableViewPortScale=yes');
                    break;

                default:
                    if (options && (options.hide_navbar)) {
                        inAppBrowserOptions.push('location=no');
                        inAppBrowserOptions.push('toolbar=no');
                    } else { //else use standard inAppBrowser with navbar
                        inAppBrowserOptions.push('location=yes');
                        inAppBrowserOptions.push('closebuttoncaption=' + $translate.instant('Done'));
                        inAppBrowserOptions.push('transitionstyle=crossdissolve');
                        inAppBrowserOptions.push('toolbar=yes');
                    }

            }
            return cordova.InAppBrowser.open(url, target, inAppBrowserOptions.join(','));
        }
    };
});
