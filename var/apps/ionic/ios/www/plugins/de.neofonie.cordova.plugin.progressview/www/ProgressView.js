cordova.define("de.neofonie.cordova.plugin.progressview.ProgressView", function(require, exports, module) {
//
//
//  ProgressView.js
//  Cordova ProgressView
//
//  Created by Sidney Bofah on 2014-12-01.
//

var exec = require('cordova/exec');

module.exports = {

    /**
     * Shows a native determinate progress dialog.
     *
     * @param {String} viewLabel - Dialog Title, defaults to 'Please Wait...'
     * @param {String} viewShape - "CIRCLE", "BAR"
     * @param {String} isIndeterminate - True / False
     * @param {String} themeAndroid -  (Android only) "TRADITIONAL", "DEVICE_DARK", "DEVICE_LIGHT", "HOLO_DARK", "HOLO_LIGHT"
     * @param successCallback
     * @param errorCallback
     * @returns {*}
     */
    show: function (viewLabel, viewShape, isIndeterminate, themeAndroid, successCallback, errorCallback) {
        label = viewLabel || "Please Wait...";
        shape =  viewShape || "CIRCLE";
        indeterminate = isIndeterminate || false;
        theme = themeAndroid || "DEVICE_LIGHT";

        return exec(successCallback, errorCallback, 'ProgressView', 'show', [label, shape, indeterminate, theme]);
    },
    

    /**
     * Sets progress percentage via float-based fraction.
     *
     * @param {float} progress -  Progress as a fraction of zero (float)
     * @param successCallback
     * @param errorCallback
     * @returns {*}
     */
    setProgress: function (progressPercentage, successCallback, errorCallback) {
        value = parseFloat(progressPercentage);

        return exec(successCallback, errorCallback, 'ProgressView', 'setProgress', [value]);
    },
   

   /**
    * Updates the text label of an existing progress view, e.g. for feedback to the user.
    *
    * @param {String} viewLabel - Text Label of Progress View
    * @param successCallback
    * @param errorCallback
    * @returns {*}
    */
   setLabel: function (viewLabel, successCallback, errorCallback) {
       label = viewLabel || "";

       return exec(successCallback, errorCallback, 'ProgressView', 'setLabel', [label]);
   },


    /**
     * Hides native determinate progress dialog.
     *
     * @param successCallback
     * @param errorCallback
     * @returns {*}
     */
    hide: function (successCallback, errorCallback) {
        return exec(successCallback, errorCallback, 'ProgressView', 'hide', '');
    }
};

});
