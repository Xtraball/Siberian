"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var AdMobState = /** @class */ (function () {
    function AdMobState() {
        this.devMode = false;
        this.nextId = 100;
        this.adUnits = {};
        this.platform = typeof cordova !== 'undefined' ? cordova.platformId : '';
    }
    AdMobState.prototype.getAdId = function (adUnitId) {
        if (this.adUnits[adUnitId]) {
            return this.adUnits[adUnitId];
        }
        this.adUnits[adUnitId] = this.nextId;
        this.nextId += 1;
        return this.adUnits[adUnitId];
    };
    return AdMobState;
}());
exports.default = AdMobState;
