"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.TrackingAuthorizationStatus = exports.MaxAdContentRating = exports.MobileAd = exports.NativeActions = exports.Events = exports.AdSizeType = void 0;
var generated_1 = require("./generated");
Object.defineProperty(exports, "AdSizeType", { enumerable: true, get: function () { return generated_1.AdSizeType; } });
Object.defineProperty(exports, "Events", { enumerable: true, get: function () { return generated_1.Events; } });
Object.defineProperty(exports, "NativeActions", { enumerable: true, get: function () { return generated_1.NativeActions; } });
/** @internal */
var MobileAd = /** @class */ (function () {
    function MobileAd(opts) {
        var _a;
        this.opts = opts;
        this.id = (_a = opts.id) !== null && _a !== void 0 ? _a : MobileAd.nextId();
        MobileAd.allAds[this.id] = this;
    }
    MobileAd.getAdById = function (id) {
        return this.allAds[id];
    };
    MobileAd.nextId = function () {
        MobileAd.idCounter += 1;
        return MobileAd.idCounter;
    };
    Object.defineProperty(MobileAd.prototype, "adUnitId", {
        get: function () {
            return this.opts.adUnitId;
        },
        enumerable: false,
        configurable: true
    });
    MobileAd.allAds = {};
    MobileAd.idCounter = 0;
    return MobileAd;
}());
exports.MobileAd = MobileAd;
var MaxAdContentRating;
(function (MaxAdContentRating) {
    MaxAdContentRating["G"] = "G";
    MaxAdContentRating["MA"] = "MA";
    MaxAdContentRating["PG"] = "PG";
    MaxAdContentRating["T"] = "T";
    MaxAdContentRating["UNSPECIFIED"] = "";
})(MaxAdContentRating = exports.MaxAdContentRating || (exports.MaxAdContentRating = {}));
var TrackingAuthorizationStatus;
(function (TrackingAuthorizationStatus) {
    TrackingAuthorizationStatus[TrackingAuthorizationStatus["notDetermined"] = 0] = "notDetermined";
    TrackingAuthorizationStatus[TrackingAuthorizationStatus["restricted"] = 1] = "restricted";
    TrackingAuthorizationStatus[TrackingAuthorizationStatus["denied"] = 2] = "denied";
    TrackingAuthorizationStatus[TrackingAuthorizationStatus["authorized"] = 3] = "authorized";
})(TrackingAuthorizationStatus = exports.TrackingAuthorizationStatus || (exports.TrackingAuthorizationStatus = {}));
