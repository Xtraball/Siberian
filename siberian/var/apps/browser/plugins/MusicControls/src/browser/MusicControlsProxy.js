cordova.define("MusicControls.MusicControlsProxy", function(require, exports, module) { /**
 * Empty proxy for browser
 */
var MusicControls = {
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

module.exports = MusicControls;

require('cordova/exec/proxy').add('MusicControls', module.exports);
});
