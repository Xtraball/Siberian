/**
 * LinkService
 *
 * @author Xtraball SAS
 * @version 4.18.3
 */
angular
.module('starter')
.service('LinkService', function ($rootScope, $translate, $window, SB) {

    var service = {
        supportedOptions: [
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
        ]
    };

    service.testMedia = function (url) {
        return /.*\.(pdf|mp3|wav|ogg|mp4|avi|jpe?g|bmp|png|mkv|webp|txt|gif|docx?|xlsx?)($|\?)/.test(url);
    };

    service.testIntent = function (url) {
        return /^(tel|sms|mailto|whatsapp|instagram|google\.navigation|waze|geo|fb):.*/.test(url);
    };

    service.openLink = function (url, options, external_browser) {

        // Handles external URL for native without un-necessary code
        if (service.testMedia(url) ||
            service.testIntent(url)) {
            if ([SB.DEVICE.TYPE_ANDROID, SB.DEVICE.TYPE_IOS].indexOf(DEVICE_TYPE) >= 0) {
                return cordova.InAppBrowser.open(url, '_system', '');
            }
        }

        var target = '_blank';
        var inAppBrowserOptions = [];

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
            if (service.supportedOptions.indexOf(key) > -1) {
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
        if (isOverview || DEVICE_TYPE === SB.DEVICE.TYPE_BROWSER) {
            // External app & custom tab are treated the same
            if (service.testIntent(url)) {
                return window.open(url);
            }

            if (service.testMedia(url) ||
                _external_browser ||
                _custom_tab) {
                target = '_system';
                if (isOverview) {
                    target = 'link-service-popup';
                }
                return parent.window.open(url, target, 'width=480,height=800');
            }

            // InAppBrowser simulated
            return cordova.InAppBrowser.open(url, target, 'location=yes');
        } else if ([SB.DEVICE.TYPE_ANDROID, SB.DEVICE.TYPE_IOS].indexOf(DEVICE_TYPE) >= 0) {

            if (_external_browser) {
                return cordova.InAppBrowser.open(url, '_system', 'location=yes');
            }

            if (_custom_tab) {
                return cordova.plugins.browsertab.openUrl(url, customTabOptions);
            }
        }

        // Latest fallback in all cases!
        return cordova.InAppBrowser.open(url, target, finalOptions);
    };

    return service;
});
