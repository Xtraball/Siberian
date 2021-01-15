cordova.define("WebView.webview", function(require, exports, module) {
/*global cordova, module */
'use strict';

module.exports = (function () {
    var _loadApp = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'loadApp', []);
    };

    var _show = function (url, successCallback, errorCallback, showLoading) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'show', [url, (typeof showLoading !== 'undefined')]);
    };

    var _reload = function () {
        cordova.exec(null, null, 'WebViewPlugin', 'reload', []);
    };

    var _hide = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'hide', []);
    };

    var _hideLoading = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'hideLoading', []);
    };

    var _subscribeCallback = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'subscribeCallback', []);
    };

    var _subscribeDebugCallback = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'subscribeDebugCallback', []);
    };

    var _subscribeResumeCallback = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'subscribeResumeCallback', []);
    };

    var _subscribePauseCallback = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'subscribePauseCallback', []);
    };

    var _subscribeUrlCallback = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'subscribeUrlCallback', []);
    };

    var _subscribeExitCallback = function (successCallback, errorCallback) {
        cordova.exec(successCallback, errorCallback, 'WebViewPlugin', 'subscribeExitCallback', []);
    };

    var _exitApp = function () {
        cordova.exec(function () {
        }, function () {
        }, 'WebViewPlugin', 'exitApp', []);
    };

    var _setWebViewBehavior = function () {
        cordova.exec(function () {
        }, function () {
        }, 'WebViewPlugin', 'webViewAdjustmenBehavior', []);
    };

    return {
        loadApp: _loadApp,
        show: _show,
        reload: _reload,
        hide: _hide,
        close: _hide,
        subscribeCallback: _subscribeCallback,
        subscribeDebugCallback: _subscribeDebugCallback,
        subscribeResumeCallback: _subscribeResumeCallback,
        subscribePauseCallback: _subscribePauseCallback,
        subscribeUrlCallback: _subscribeUrlCallback,
        subscribeExitCallback: _subscribeExitCallback,
        exitApp: _exitApp,
        hideLoading: _hideLoading,
        setWebViewBehavior: _setWebViewBehavior
    };
})();

});
