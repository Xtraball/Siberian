/**
 * Empty proxy for browser
 */
var Toast = {
    updateCallback: function() {},
    create: function (successCallback, errorCallback, datas) {},
    destroy: function (successCallback, errorCallback, datas) {},
    watch: function (_onUpdate, errorCallback, datas) {},
    updateIsPlaying: function (successCallback, errorCallback, par) {},
    updateElapsed: function(args, successCallback, errorCallback) {},
    updateDismissable: function(dismissable, successCallback, errorCallback) {},
    disableBatteryOptimization: function(successCallback, errorCallback) {},
    subscribe: function(onUpdate) {},
    listen: function() {},
    receiveCallbackFromNative: function(messageFromNative) {},
};

module.exports = Toast;

require('cordova/exec/proxy').add('Toast', module.exports);
