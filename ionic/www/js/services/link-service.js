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
            var _deviceOptions = {};
            switch (DEVICE_TYPE) {
                case SB.DEVICE.TYPE_ANDROID:
                    _deviceOptions = options['android'];
                    break;
                case SB.DEVICE.TYPE_IOS:
                    _deviceOptions = options['ios'];
                    break;
            }
            var _options = angular.extend({}, {
                'toolbarcolor': $window.colors.header.backgroundColorHex,
                'location': 'no',
                'toolbar': 'no',
                'zoom': 'no',
                'enableViewPortScale': 'yes',
                'closebuttoncaption': $translate.instant('Done'),
                'transitionstyle': 'crossdissolve'
            }, _deviceOptions);

            // HTML5 forced on Browser devices
            if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                if (_external_browser ||
                    /.*\.pdf($|\?)/.test(url)) {
                    target = '_system';
                }
                // Enforce inAppBrowser fallback with location!
                return cordova.InAppBrowser.open(url, target, 'location=yes');
            }

            // External browser
            if (_external_browser || /.*\.pdf($|\?)/.test(url)) {
                return cordova.plugins.browsertab.openUrl(url, {});
            }

            // Enforcing target '_self' for Android tel: links!
            if (/^(tel:).*/.test(url) &&
                (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID)) {
                target = '_self';
            }

            for (let [key, value] of Object.entries(_options)) {
                // Push only allowed options!
                if (supportOptions.indexOf(key) > -1) {
                    inAppBrowserOptions.push(`${key}=${value}`);
                }
            }
            var finalOptions = inAppBrowserOptions.join(',');

            return cordova.InAppBrowser.open(url, target, finalOptions);
        }
    };
});
