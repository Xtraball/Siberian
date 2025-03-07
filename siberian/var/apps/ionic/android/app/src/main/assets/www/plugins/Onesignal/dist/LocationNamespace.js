cordova.define("Onesignal.LocationNamespace", function(require, exports, module) {
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var Location = /** @class */ (function () {
    function Location() {
    }
    /**
     * Location
     */
    /**
     * Prompts the user for location permissions to allow geotagging from the OneSignal dashboard.
     * @returns void
     */
    Location.prototype.requestPermission = function () {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "requestLocationPermission", []);
    };
    ;
    /**
     * Disable or enable location collection (defaults to enabled if your app has location permission).
     * @param  {boolean} shared
     * @returns void
     */
    Location.prototype.setShared = function (shared) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setLocationShared", [shared]);
    };
    ;
    /**
     * Whether location is currently shared with OneSignal.
     * @returns {Promise<boolean>}
     */
    Location.prototype.isShared = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "isLocationShared", []);
        });
    };
    ;
    return Location;
}());
exports.default = Location;

});
