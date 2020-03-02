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
            var supportedOptions = [
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

            var _deviceOptions = {};
            try {
                switch (DEVICE_TYPE) {
                    case SB.DEVICE.TYPE_ANDROID:
                        _deviceOptions = options['android'];
                        break;
                    case SB.DEVICE.TYPE_IOS:
                        _deviceOptions = options['ios'];
                        break;
                }
            } catch (e) {
                _deviceOptions = {};
            }
            var _options = angular.extend({}, {
                'toolbarcolor': $window.colors.header.backgroundColorHex,
                'location': 'no',
                'toolbar': 'yes',
                'zoom': 'no',
                'enableViewPortScale': 'yes',
                'closebuttoncaption': $translate.instant('Done'),
                'transitionstyle': 'crossdissolve'
            }, _deviceOptions);

            // Determining the browser type!
            var _external_browser = (external_browser === undefined) ? false : external_browser;
            var _in_app_browser, _custom_tab = false;
            if (_options && _options.global && _options.global.browser) {
                switch (_options.global.browser) {
                    case 'in_app_browser':
                        _in_app_browser = true;
                        break;
                    case 'custom_tab':
                        _custom_tab = true;
                        break;
                    case 'external_browser':
                        _external_browser = true;
                        break;
                }
            } else {
                if (!_external_browser) {
                    _in_app_browser = true;
                }
            }


            for (var key in _options) {
                // Push only allowed options!
                if (supportedOptions.indexOf(key) > -1) {
                    var value = _options[key];
                    inAppBrowserOptions.push(key + '=' + value);
                }
            }
            var finalOptions = inAppBrowserOptions.join(',');

            // It's overview, so we must go with new window/tab
            if (isOverview && (_external_browser || _custom_tab)) {
                return $window.open(url, 'link-service-popup', finalOptions);
            }

            // HTML5 App
            if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                if (_external_browser ||
                    /.*\.(pdf|mp3|wav|mp4|avi)($|\?)/.test(url)) {
                    target = '_system';
                }
                // Enforce inAppBrowser fallback with location!
                return cordova.InAppBrowser.open(url, target, 'location=yes');
            }

            // External browser or media URI
            if (_external_browser || /.*\.(pdf|mp3|wav|mp4|avi)($|\?)/.test(url)) {
                return cordova.InAppBrowser.open(url, '_system', '');
            }

            // CustomTab
            var customTabOptions = {
                'tabColor': $window.colors.header.backgroundColorHex,
                'secondaryToolbarColor': $window.colors.header.backgroundColorHex,
                'showTitle': true,
                'instantAppsEnabled': false,
                'enableUrlBarHiding': false,
                'selectBrowser': false,
            };

            // CustomTab option
            if (_custom_tab) {
                return cordova.plugins.browsertab.openUrl(url, customTabOptions);
            }

            // Enforcing target '_self' for Android tel: links!
            if (/^(tel:).*/.test(url) &&
                (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID)) {
                target = '_self';
            }

            return cordova.InAppBrowser.open(url, target, finalOptions);
        }
    };
});
