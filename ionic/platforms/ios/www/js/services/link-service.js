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

            var testMedia = function (url) {
                return /.*\.(pdf|mp3|wav|mp4|avi)($|\?)/.test(url);
            };

            var testTelSms = function (url) {
                return /^(tel|sms|whatsapp):.*/.test(url);
            };

            // In case we do not have options!
            if (options === undefined) {
                options = {
                    'global': {},
                    'android': {},
                    'ios': {},
                };
            }
            var _globalOptions = options['global'];

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
            if (_globalOptions && _globalOptions.browser) {
                switch (_globalOptions.browser) {
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
            }

            for (var key in _options) {
                // Push only allowed options!
                if (supportedOptions.indexOf(key) > -1) {
                    var value = _options[key];
                    inAppBrowserOptions.push(key + '=' + value);
                }
            }
            var finalOptions = inAppBrowserOptions.join(',');

            // CustomTab
            var customTabOptions = {
                'tabColor': $window.colors.header.backgroundColorHex,
                'secondaryToolbarColor': $window.colors.header.backgroundColorHex,
                'showTitle': true,
                'instantAppsEnabled': false,
                'enableUrlBarHiding': false,
                'selectBrowser': false,
            };

            // Overview special case
            if (isOverview) {
                // External app & custom tab are treated the same
                if (testTelSms(url)) {
                    return window.open(url);
                }

                if (testMedia(url) ||
                    _external_browser ||
                    _custom_tab) {
                    return parent.window.open(url, 'link-service-popup', 'width=480,height=800');
                }

                // InAppBrowser simulated
                return cordova.InAppBrowser.open(url, target, 'location=yes');
            } else if (DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
                // External app & custom tab are treated the same
                if (testTelSms(url)) {
                    return window.open(url);
                }

                if (testMedia(url) ||
                    _external_browser ||
                    _custom_tab) {
                    return window.open(url, '_system', 'width=480,height=800');
                }

                return cordova.InAppBrowser.open(url, target, 'location=yes');
            } else if (DEVICE_TYPE === SB.DEVICE.TYPE_ANDROID) {
                // Second-First we check file type
                if (testMedia(url) ||
                    testTelSms(url)) {
                    return cordova.InAppBrowser.open(url, '_system', '');
                }

                if (_external_browser) {
                    return cordova.InAppBrowser.open(url, '_system', 'location=yes');
                }

                if (_custom_tab) {
                    return cordova.plugins.browsertab.openUrl(url, customTabOptions);
                }

                return cordova.InAppBrowser.open(url, target, finalOptions);
            } else if (DEVICE_TYPE === SB.DEVICE.TYPE_IOS) {
                // Second-First we check file type
                if (testMedia(url) ||
                    testTelSms(url)) {
                    return cordova.InAppBrowser.open(url, '_system', '');
                }

                if (_external_browser) {
                    return cordova.InAppBrowser.open(url, '_system', 'location=yes');
                }

                if (_custom_tab) {
                    return cordova.plugins.browsertab.openUrl(url, customTabOptions);
                }

                return cordova.InAppBrowser.open(url, target, finalOptions);
            }

            // Latest fallback in all cases!
            return cordova.InAppBrowser.open(url, target, finalOptions);
        }
    };
});
