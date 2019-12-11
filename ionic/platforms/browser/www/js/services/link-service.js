/**
 * LinkService
 *
 * @author Xtraball SAS
 * @version 4.18.3
 */
angular
.module('starter')
.service('LinkService', function ($rootScope, $translate, $window, SB) {
    return {
        openLink: function (url, options, external_browser) {
            var supportOptions = [
                'location',
                'hidden',
                'beforeload',
                'clearcache',
                'clearsessioncache',
                'closebuttoncaption',
                'closebuttoncolor',
                'footer',
                'footercolor',
                'hardwareback',
                'hidenavigationbuttons',
                'hideurlbar',
                'navigationbuttoncolor',
                'toolbarcolor',
                'lefttoright',
                'zoom',
                'mediaPlaybackRequiresUserAction',
                'shouldPauseOnSuspend',
                'useWideViewPort',
                'cleardata',
                'disallowoverscroll',
                'toolbar',
                'toolbartranslucent',
                'lefttoright',
                'enableViewportScale',
                'allowInlineMediaPlayback',
                'keyboardDisplayRequiresUserAction',
                'suppressesIncrementalRendering',
                'presentationstyle',
                'transitionstyle',
                'toolbarposition',
                'hidespinner',
            ];
            var target = '_blank';
            var inAppBrowserOptions = [];
            var _external_browser = (external_browser === undefined) ? false : external_browser;
            var _options = angular.extend({}, {
                'toolbarcolor': $window.colors.header.backgroundColorHex,
                'location': 'no',
                'toolbar': 'no',
                'enableViewPortScale': 'yes',
                'closebuttoncaption': $translate.instant('Done'),
                'transitionstyle': 'crossdissolve'
            }, options);

            // HTML5
            if (DEVICE_TYPE !== SB.DEVICE.TYPE_BROWSER) {
                // Enforce inAppBrowser fallback with location!
                return cordova.InAppBrowser.open(url, target, 'location=yes');
            }

            // External browser
            if (_external_browser) {
                if (DEVICE_TYPE !== SB.DEVICE.TYPE_BROWSER) {
                    return cordova.plugins.browsertab.openUrl(url, {});
                }
                // Enforce inAppBrowser fallback with location!
                return cordova.InAppBrowser.open(url, target, 'location=yes');
            }

            // Enforcing target for Android tel: links!
            if (/^(tel:).*/.test(url) &&
                (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID)) {
                target = '_self';
            }

            // PDF file, open the system PDF reader (for now)
            if (/.*\.pdf($|\?)/.test(url) &&
                (DEVICE_TYPE !== SB.DEVICE.TYPE_BROWSER)) {
                return cordova.plugins.browsertab.openUrl(url, {});
            }

            for (let [key, value] of Object.entries(_options)) {
                // Push only allowed options!
                if (supportOptions.indexOf(key) > -1) {
                    inAppBrowserOptions.push(`${key}=${value}`);
                }
            }

            return cordova.InAppBrowser.open(url, target, inAppBrowserOptions.join(','));
        }
    };
});
