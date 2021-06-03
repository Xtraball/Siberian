"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var core_1 = require("@admob-plus/core");
var base_1 = require("./base");
var Banner = /** @class */ (function (_super) {
    __extends(Banner, _super);
    function Banner() {
        var _this = _super !== null && _super.apply(this, arguments) || this;
        _this.testIdForAndroid = "ca-app-pub-3940256099942544/6300978111" /* banner_android */;
        _this.testIdForIOS = "ca-app-pub-3940256099942544/2934735716" /* banner_ios */;
        return _this;
    }
    Banner.prototype.show = function (opts) {
        var adUnitID = this.resolveAdUnitID(opts.id);
        return base_1.execAsync(base_1.NativeActions.banner_show, [
            __assign(__assign({ position: 'bottom', size: core_1.AdSizeType.SMART_BANNER }, opts), { adUnitID: adUnitID, id: this.state.getAdId(adUnitID) }),
        ]);
    };
    Banner.prototype.hide = function (id) {
        var adUnitID = this.resolveAdUnitID(id);
        return base_1.execAsync(base_1.NativeActions.banner_hide, [
            { id: this.state.getAdId(adUnitID) },
        ]);
    };
    return Banner;
}(base_1.AdBase));
exports.default = Banner;
