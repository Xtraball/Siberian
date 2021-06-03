"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.AdBase = exports.waitEvent = exports.fireDocumentEvent = exports.execAsync = exports.NativeActions = exports.Events = void 0;
var core_1 = require("@admob-plus/core");
Object.defineProperty(exports, "Events", { enumerable: true, get: function () { return core_1.Events; } });
Object.defineProperty(exports, "NativeActions", { enumerable: true, get: function () { return core_1.NativeActions; } });
var cordova_1 = require("cordova");
function execAsync(action, args) {
    return new Promise(function (resolve, reject) {
        cordova_1.exec(resolve, reject, core_1.NativeActions.Service, action, args);
    });
}
exports.execAsync = execAsync;
function fireDocumentEvent(eventName, data) {
    if (data === void 0) { data = null; }
    var event = new CustomEvent(eventName, { detail: data });
    document.dispatchEvent(event);
}
exports.fireDocumentEvent = fireDocumentEvent;
function waitEvent(successEvent, failEvent) {
    if (failEvent === void 0) { failEvent = ''; }
    return new Promise(function (resolve, reject) {
        document.addEventListener(successEvent, function (event) {
            resolve(event);
        }, false);
        if (failEvent) {
            document.addEventListener(failEvent, function (failedEvent) {
                reject(failedEvent);
            }, false);
        }
    });
}
exports.waitEvent = waitEvent;
var AdBase = /** @class */ (function () {
    function AdBase(state) {
        this.state = state;
    }
    Object.defineProperty(AdBase.prototype, "testAdUnitID", {
        get: function () {
            switch (this.state.platform) {
                case "android" /* android */:
                    return this.testIdForAndroid;
                case "ios" /* ios */:
                    return this.testIdForIOS;
                default:
                    return "test" /* dummy */;
            }
        },
        enumerable: false,
        configurable: true
    });
    AdBase.prototype.resolveAdUnitID = function (adUnitID) {
        if (adUnitID === "test" /* dummy */ || this.state.devMode) {
            return this.testAdUnitID;
        }
        if (!adUnitID) {
            throw new Error('adUnitID is missing');
        }
        if (typeof adUnitID === 'string') {
            return adUnitID;
        }
        switch (this.state.platform) {
            case "android" /* android */:
            case "ios" /* ios */:
                return adUnitID[this.state.platform];
            default:
                return "test" /* dummy */;
        }
    };
    return AdBase;
}());
exports.AdBase = AdBase;
