cordova.define("Admob.AdMob", function(require, exports, module) {
'use strict';

var cordova$1 = require('cordova');
var channel = require('cordova/channel');
var exec = require('cordova/exec');

function _interopNamespaceDefault(e) {
    var n = Object.create(null);
    if (e) {
        Object.keys(e).forEach(function (k) {
            if (k !== 'default') {
                var d = Object.getOwnPropertyDescriptor(e, k);
                Object.defineProperty(n, k, d.get ? d : {
                    enumerable: true,
                    get: function () { return e[k]; }
                });
            }
        });
    }
    n.default = e;
    return Object.freeze(n);
}

var cordova__namespace = /*#__PURE__*/_interopNamespaceDefault(cordova$1);

/******************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */
/* global Reflect, Promise, SuppressedError, Symbol */

var extendStatics = function(d, b) {
    extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
    return extendStatics(d, b);
};

function __extends(d, b) {
    if (typeof b !== "function" && b !== null)
        throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
    extendStatics(d, b);
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
}

var __assign = function() {
    __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};

function __awaiter(thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
}

function __generator(thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
}

function __spreadArray(to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
}

typeof SuppressedError === "function" ? SuppressedError : function (error, suppressed, message) {
    var e = new Error(message);
    return e.name = "SuppressedError", e.error = error, e.suppressed = suppressed, e;
};

var CordovaService = 'AdMob';
var Events;
(function (Events) {
    Events["adClick"] = "admob.ad.click";
    Events["adDismiss"] = "admob.ad.dismiss";
    Events["adImpression"] = "admob.ad.impression";
    Events["adLoad"] = "admob.ad.load";
    Events["adLoadFail"] = "admob.ad.loadfail";
    Events["adReward"] = "admob.ad.reward";
    Events["adShow"] = "admob.ad.show";
    Events["adShowFail"] = "admob.ad.showfail";
    Events["bannerSize"] = "admob.banner.size";
    Events["ready"] = "admob.ready";
})(Events || (Events = {}));
/** @internal */
function execAsync(action, args) {
    return new Promise(function (resolve, reject) {
        cordova.exec(resolve, reject, CordovaService, action, args);
    });
}

/** @internal */
var MobileAd = /** @class */ (function () {
    function MobileAd(opts) {
        var _a;
        this.opts = opts;
        this.id = (_a = opts.id) !== null && _a !== void 0 ? _a : opts.adUnitId;
        MobileAd.allAds[this.id] = this;
    }
    Object.defineProperty(MobileAd, "allAds", {
        get: function () {
            var win = window;
            if (typeof win.admobAds === 'undefined')
                win.admobAds = {};
            return win.admobAds;
        },
        enumerable: false,
        configurable: true
    });
    MobileAd.getAdById = function (id) {
        return this.allAds[id];
    };
    Object.defineProperty(MobileAd.prototype, "adUnitId", {
        get: function () {
            return this.opts.adUnitId;
        },
        enumerable: false,
        configurable: true
    });
    MobileAd.prototype.on = function () {
        var _this = this;
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var eventName = args[0], cb = args[1], rest = args.slice(2);
        var type = "admob.ad.".concat(eventName.toLowerCase());
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        var listener = function (evt) {
            if (evt.adId === _this.id) {
                cb(evt);
            }
        };
        document.addEventListener.apply(document, __spreadArray([type, listener], rest, false));
        return function () {
            document.removeEventListener.apply(document, __spreadArray([type, listener], rest, false));
        };
    };
    MobileAd.prototype.isLoaded = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.init()];
                    case 1:
                        _a.sent();
                        return [2 /*return*/, execAsync('adIsLoaded', [{ id: this.id }])];
                }
            });
        });
    };
    MobileAd.prototype.load = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.init()];
                    case 1:
                        _a.sent();
                        // TODO read `opts` in native code?
                        return [4 /*yield*/, execAsync('adLoad', [__assign(__assign({}, this.opts), { id: this.id })])];
                    case 2:
                        // TODO read `opts` in native code?
                        _a.sent();
                        return [2 /*return*/];
                }
            });
        });
    };
    MobileAd.prototype.show = function (opts) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.init()];
                    case 1:
                        _a.sent();
                        return [2 /*return*/, execAsync('adShow', [__assign(__assign({}, opts), { id: this.id })])];
                }
            });
        });
    };
    MobileAd.prototype.hide = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, this.init()];
                    case 1:
                        _a.sent();
                        return [2 /*return*/, execAsync('adHide', [{ id: this.id }])];
                }
            });
        });
    };
    MobileAd.prototype.init = function () {
        var _a;
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_b) {
                return [2 /*return*/, ((_a = this._initPromise) !== null && _a !== void 0 ? _a : (this._initPromise = this._init()))];
            });
        });
    };
    MobileAd.prototype._init = function () {
        var _a;
        return __awaiter(this, void 0, void 0, function () {
            var cls;
            return __generator(this, function (_b) {
                switch (_b.label) {
                    case 0: return [4 /*yield*/, admob.start()];
                    case 1:
                        _b.sent();
                        cls = (_a = this.constructor.cls) !== null && _a !== void 0 ? _a : this.constructor.name;
                        return [2 /*return*/, execAsync('adCreate', [__assign(__assign({}, this.opts), { id: this.id, cls: cls })])];
                }
            });
        });
    };
    return MobileAd;
}());

var AppOpenAd = /** @class */ (function (_super) {
    __extends(AppOpenAd, _super);
    function AppOpenAd() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    AppOpenAd.prototype.isLoaded = function () {
        return _super.prototype.isLoaded.call(this);
    };
    AppOpenAd.prototype.load = function () {
        return _super.prototype.load.call(this);
    };
    AppOpenAd.prototype.show = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, _super.prototype.show.call(this)];
            });
        });
    };
    AppOpenAd.cls = 'AppOpenAd';
    return AppOpenAd;
}(MobileAd));

var AdSizeType;
(function (AdSizeType) {
    AdSizeType[AdSizeType["BANNER"] = 0] = "BANNER";
    AdSizeType[AdSizeType["LARGE_BANNER"] = 1] = "LARGE_BANNER";
    AdSizeType[AdSizeType["MEDIUM_RECTANGLE"] = 2] = "MEDIUM_RECTANGLE";
    AdSizeType[AdSizeType["FULL_BANNER"] = 3] = "FULL_BANNER";
    AdSizeType[AdSizeType["LEADERBOARD"] = 4] = "LEADERBOARD";
    AdSizeType[AdSizeType["SMART_BANNER"] = 5] = "SMART_BANNER";
})(AdSizeType || (AdSizeType = {}));
var colorToRGBA = (function () {
    var canvas = document.createElement('canvas');
    canvas.width = canvas.height = 1;
    var ctx = canvas.getContext('2d');
    if (!ctx)
        return function () { return undefined; };
    return function (col) {
        ctx.clearRect(0, 0, 1, 1);
        // In order to detect invalid values,
        // we can't rely on col being in the same format as what fillStyle is computed as,
        // but we can ask it to implicitly compute a normalized value twice and compare.
        ctx.fillStyle = '#000';
        ctx.fillStyle = col;
        var computed = ctx.fillStyle;
        ctx.fillStyle = '#fff';
        ctx.fillStyle = col;
        if (computed !== ctx.fillStyle) {
            return; // invalid color
        }
        ctx.fillRect(0, 0, 1, 1);
        var data = ctx.getImageData(0, 0, 1, 1).data;
        return { r: data[0], g: data[1], b: data[2], a: data[3] };
    };
})();
var BannerAd = /** @class */ (function (_super) {
    __extends(BannerAd, _super);
    function BannerAd(opts) {
        var _this = _super.call(this, __assign({ position: 'bottom', size: AdSizeType.SMART_BANNER }, opts)) || this;
        _this._loaded = false;
        return _this;
    }
    BannerAd.config = function (opts) {
        if (cordova.platformId === "ios" /* Platform.ios */) {
            var bgColor = opts.backgroundColor;
            return execAsync('bannerConfig', [
                __assign(__assign({}, opts), { backgroundColor: bgColor ? colorToRGBA(bgColor) : bgColor }),
            ]);
        }
        return false;
    };
    BannerAd.prototype.load = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, _super.prototype.load.call(this)];
                    case 1:
                        _a.sent();
                        this._loaded = true;
                        return [2 /*return*/];
                }
            });
        });
    };
    BannerAd.prototype.show = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        if (!!this._loaded) return [3 /*break*/, 2];
                        return [4 /*yield*/, this.load()];
                    case 1:
                        _a.sent();
                        _a.label = 2;
                    case 2: return [2 /*return*/, _super.prototype.show.call(this)];
                }
            });
        });
    };
    BannerAd.prototype.hide = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, _super.prototype.hide.call(this)];
            });
        });
    };
    BannerAd.cls = 'BannerAd';
    return BannerAd;
}(MobileAd));

var InterstitialAd = /** @class */ (function (_super) {
    __extends(InterstitialAd, _super);
    function InterstitialAd() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    InterstitialAd.prototype.isLoaded = function () {
        return _super.prototype.isLoaded.call(this);
    };
    InterstitialAd.prototype.load = function () {
        return _super.prototype.load.call(this);
    };
    InterstitialAd.prototype.show = function () {
        return _super.prototype.show.call(this);
    };
    InterstitialAd.cls = 'InterstitialAd';
    return InterstitialAd;
}(MobileAd));

var NativeAd = /** @class */ (function (_super) {
    __extends(NativeAd, _super);
    function NativeAd() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    NativeAd.prototype.isLoaded = function () {
        return _super.prototype.isLoaded.call(this);
    };
    NativeAd.prototype.hide = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, _super.prototype.hide.call(this)];
            });
        });
    };
    NativeAd.prototype.load = function () {
        return _super.prototype.load.call(this);
    };
    NativeAd.prototype.show = function (opts) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, _super.prototype.show.call(this, __assign({ x: 0, y: 0, width: 0, height: 0 }, opts))];
            });
        });
    };
    NativeAd.prototype.showWith = function (elm) {
        return __awaiter(this, void 0, void 0, function () {
            var update, observer;
            var _this = this;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        update = function () { return __awaiter(_this, void 0, void 0, function () {
                            var r;
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0:
                                        r = elm.getBoundingClientRect();
                                        return [4 /*yield*/, this.show({
                                                x: r.x,
                                                y: r.y,
                                                width: r.width,
                                                height: r.height,
                                            })];
                                    case 1:
                                        _a.sent();
                                        return [2 /*return*/];
                                }
                            });
                        }); };
                        observer = new MutationObserver(update);
                        observer.observe(document.body, {
                            attributes: true,
                            childList: true,
                            subtree: true,
                        });
                        document.addEventListener('scroll', update);
                        window.addEventListener('resize', update);
                        return [4 /*yield*/, update()];
                    case 1:
                        _a.sent();
                        return [2 /*return*/];
                }
            });
        });
    };
    NativeAd.cls = 'NativeAd';
    return NativeAd;
}(MobileAd));

var RewardedAd = /** @class */ (function (_super) {
    __extends(RewardedAd, _super);
    function RewardedAd() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    RewardedAd.prototype.isLoaded = function () {
        return _super.prototype.isLoaded.call(this);
    };
    RewardedAd.prototype.load = function () {
        return _super.prototype.load.call(this);
    };
    RewardedAd.prototype.show = function () {
        return _super.prototype.show.call(this);
    };
    RewardedAd.cls = 'RewardedAd';
    return RewardedAd;
}(MobileAd));

var RewardedInterstitialAd = /** @class */ (function (_super) {
    __extends(RewardedInterstitialAd, _super);
    function RewardedInterstitialAd() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    RewardedInterstitialAd.prototype.isLoaded = function () {
        return _super.prototype.isLoaded.call(this);
    };
    RewardedInterstitialAd.prototype.load = function () {
        return _super.prototype.load.call(this);
    };
    RewardedInterstitialAd.prototype.show = function () {
        return _super.prototype.show.call(this);
    };
    RewardedInterstitialAd.cls = 'RewardedInterstitialAd';
    return RewardedInterstitialAd;
}(MobileAd));

var WebViewAd = /** @class */ (function (_super) {
    __extends(WebViewAd, _super);
    function WebViewAd(opts) {
        var _this = this;
        var _a, _b, _c, _d, _e, _f, _g;
        opts.adUnitId = '';
        _this = _super.call(this, opts) || this;
        _this._loaded = false;
        _this._src = '';
        _this._adsense = '';
        _this._originalHref = window.location.href || '';
        _this._historyCurrentHref = '';
        _this._adsense = opts.adsense;
        _this._src =
            opts.src ||
                'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
        if (typeof ((_a = window.gmaSdk) === null || _a === void 0 ? void 0 : _a.getQueryInfo) === 'function' ||
            typeof ((_d = (_c = (_b = window.webkit) === null || _b === void 0 ? void 0 : _b.messageHandlers) === null || _c === void 0 ? void 0 : _c.getGmaQueryInfo) === null || _d === void 0 ? void 0 : _d.postMessage) === 'function' ||
            typeof ((_g = (_f = (_e = window.webkit) === null || _e === void 0 ? void 0 : _e.messageHandlers) === null || _f === void 0 ? void 0 : _f.getGmaSig) === null || _g === void 0 ? void 0 : _g.postMessage) ===
                'function') {
            var html = "<script async src=\"".concat(_this._src, "\" crossorigin=\"anonymous\"></script>\n\n      ").concat(opts.npa
                ? '<script>(window.adsbygoogle = window.adsbygoogle || []).requestNonPersonalizedAds = 1</script>'
                : '', "\n\n      <script>\n        (window.adsbygoogle = window.adsbygoogle || []).push({google_ad_client: \"").concat(_this._adsense, "\", enable_page_level_ads: true, overlays: false});\n      </script>\n      ");
            var div = document.createElement('div');
            div.innerHTML = html;
            document.head.appendChild(div);
            _this.nodeScriptReplace(div);
            _this._loaded = true;
        }
        else {
            console.error('WebView does not appear to be setup correctly');
        }
        document.addEventListener('pause', function () {
            _this._historyCurrentHref = _this.historyCurrentHref();
            _this.historyRestoreOriginalHref();
        });
        document.addEventListener('resume', function () {
            if (_this._historyCurrentHref) {
                _this.historyReplaceState(_this._historyCurrentHref);
            }
        });
        return _this;
    }
    WebViewAd.checkIntegration = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, execAsync('webviewGoto', [
                            'https://webview-api-for-ads-test.glitch.me/',
                        ])];
                    case 1:
                        _a.sent();
                        return [2 /*return*/];
                }
            });
        });
    };
    WebViewAd.prototype.addAd = function (opts) {
        opts = __assign({ format: 'auto', fullWidth: true }, opts);
        if (this._loaded) {
            var html = opts.html || '';
            if (!opts.html) {
                html = "<script async src=\"".concat(this._src, "\" crossorigin=\"anonymous\"></script>\n\n        <ins class=\"adsbygoogle\" style=\"display:block\" data-ad-client=\"").concat(this._adsense, "\" data-ad-slot=\"").concat(opts.slot, "\" data-ad-format=\"").concat(opts.format, "\" data-full-width-responsive=\"").concat(opts.fullWidth ? 'true' : 'false', "\"></ins>\n\n        <script>(window.adsbygoogle = window.adsbygoogle || []).push({});</script>");
            }
            if (opts.element) {
                opts.element.innerHTML = html;
                this.nodeScriptReplace(opts.element);
                return true;
            }
        }
        return false;
    };
    WebViewAd.prototype.nodeScriptReplace = function (node) {
        if (this.isNodeScript(node) === true) {
            node.parentNode.replaceChild(this.nodeScriptClone(node), node);
        }
        else {
            var children = node.childNodes;
            for (var i = 0, len = children.length; i < len; i++) {
                this.nodeScriptReplace(children[i]);
            }
        }
        return node;
    };
    WebViewAd.prototype.nodeScriptClone = function (node) {
        var script = document.createElement('script');
        script.text = node.innerHTML;
        var attrs = node.attributes;
        for (var i = 0, len = attrs.length; i < len; i++) {
            script.setAttribute(attrs[i].name, attrs[i].value);
        }
        return script;
    };
    WebViewAd.prototype.isNodeScript = function (node) {
        return node.tagName === 'SCRIPT';
    };
    WebViewAd.prototype.historyReplaceState = function (url) {
        if (!this._originalHref) {
            this._originalHref = window.location.href;
        }
        if (this._loaded) {
            window.history.replaceState(null, '', url);
        }
    };
    WebViewAd.prototype.historySetPage = function (page, parameters) {
        if (parameters === void 0) { parameters = {}; }
        var _parameters = [];
        for (var name_1 in parameters) {
            _parameters.push(name_1 + '=' + encodeURI(parameters[name_1]));
        }
        var url = "".concat(page).concat(_parameters.length > 0 ? '?' + _parameters.join('&') : '');
        this.historyReplaceState(url);
        return url;
    };
    WebViewAd.prototype.historyOriginalHref = function () {
        return this._originalHref || window.location.href;
    };
    WebViewAd.prototype.historyCurrentHref = function () {
        return window.location.href;
    };
    WebViewAd.prototype.historyRestoreOriginalHref = function () {
        this.historyReplaceState(this.historyOriginalHref());
    };
    WebViewAd.prototype.show = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        if (!!this._loaded) return [3 /*break*/, 2];
                        return [4 /*yield*/, this.load()];
                    case 1:
                        _a.sent();
                        _a.label = 2;
                    case 2: return [2 /*return*/, _super.prototype.show.call(this)];
                }
            });
        });
    };
    WebViewAd.cls = 'WebViewAd';
    return WebViewAd;
}(MobileAd));

var AdMob = /** @class */ (function () {
    function AdMob() {
        this.AppOpenAd = AppOpenAd;
        this.BannerAd = BannerAd;
        this.InterstitialAd = InterstitialAd;
        this.NativeAd = NativeAd;
        this.RewardedAd = RewardedAd;
        this.RewardedInterstitialAd = RewardedInterstitialAd;
        this.WebViewAd = WebViewAd;
        this.Events = Events;
    }
    AdMob.prototype.configure = function (config) {
        return execAsync('configure', [config]);
    };
    AdMob.prototype.start = function () {
        var _a;
        return ((_a = this._startPromise) !== null && _a !== void 0 ? _a : (this._startPromise = this._start()));
    };
    AdMob.prototype._start = function () {
        return execAsync('start');
    };
    return AdMob;
}());

var admob$1 = new AdMob();
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function onMessageFromNative(event) {
    var data = event.data;
    if (data && data.adId) {
        data.ad = MobileAd.getAdById(data.adId);
    }
    cordova__namespace.fireDocumentEvent(event.type, data);
}
var feature = 'onAdMobPlusReady';
channel.createSticky(feature);
channel.waitForInitialization(feature);
channel.onCordovaReady.subscribe(function () {
    var action = 'ready';
    exec(onMessageFromNative, console.error, CordovaService, action, []);
    channel.initializationComplete(feature);
});

module.exports = admob$1;

});
