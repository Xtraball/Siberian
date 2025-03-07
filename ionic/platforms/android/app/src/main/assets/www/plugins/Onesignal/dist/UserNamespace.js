cordova.define("Onesignal.UserNamespace", function(require, exports, module) {
"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
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
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
var PushSubscriptionNamespace_1 = __importDefault(require("./PushSubscriptionNamespace"));
var User = /** @class */ (function () {
    function User() {
        // The push subscription associated to the current user.
        this.pushSubscription = new PushSubscriptionNamespace_1.default();
        this._userStateObserverList = [];
    }
    User.prototype._processFunctionList = function (array, param) {
        for (var i = 0; i < array.length; i++) {
            array[i](param);
        }
    };
    /**
     * Explicitly set a 2-character language code for the user.
     * @param  {string} language
     * @returns void
     */
    User.prototype.setLanguage = function (language) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "setLanguage", [language]);
    };
    ;
    /**
     * Aliases
     */
    /**
     * Set an alias for the current user. If this alias label already exists on this user, it will be overwritten with the new alias id.
     * @param  {string} label
     * @param  {string} id
     * @returns void
     */
    User.prototype.addAlias = function (label, id) {
        var _a;
        var jsonKeyValue = (_a = {}, _a[label] = id, _a);
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addAliases", [jsonKeyValue]);
    };
    ;
    /**
     * Set aliases for the current user. If any alias already exists, it will be overwritten to the new values.
     * @param {object} aliases
     * @returns void
     */
    User.prototype.addAliases = function (aliases) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addAliases", [aliases]);
    };
    ;
    /**
     * Remove an alias from the current user.
     * @param  {string} label
     * @returns void
     */
    User.prototype.removeAlias = function (label) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeAliases", [label]);
    };
    ;
    /**
     * Remove aliases from the current user.
     * @param  {string[]} labels
     * @returns void
     */
    User.prototype.removeAliases = function (labels) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeAliases", labels);
    };
    ;
    /**
     * Email
     */
    /**
     * Add a new email subscription to the current user.
     * @param  {string} email
     * @returns void
     */
    User.prototype.addEmail = function (email) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addEmail", [email]);
    };
    ;
    /**
     * Remove an email subscription from the current user. Returns false if the specified email does not exist on the user within the SDK, and no request will be made.
     * @param {string} email
     * @returns void
     */
    User.prototype.removeEmail = function (email) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeEmail", [email]);
    };
    ;
    /**
     * SMS
     */
    /**
     * Add a new SMS subscription to the current user.
     * @param  {string} smsNumber
     * @returns void
     */
    User.prototype.addSms = function (smsNumber) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addSms", [smsNumber]);
    };
    ;
    /**
     * Remove an SMS subscription from the current user. Returns false if the specified SMS number does not exist on the user within the SDK, and no request will be made.
     * @param {string} smsNumber
     * @returns void
     */
    User.prototype.removeSms = function (smsNumber) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeSms", [smsNumber]);
    };
    ;
    /**
     * Tags
     */
    /**
     * Add a tag for the current user. Tags are key:value string pairs used as building blocks for targeting specific users and/or personalizing messages. If the tag key already exists, it will be replaced with the value provided here.
     * @param  {string} key
     * @param  {string} value
     * @returns void
     */
    User.prototype.addTag = function (key, value) {
        var _a;
        var jsonKeyValue = (_a = {}, _a[key] = value, _a);
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addTags", [jsonKeyValue]);
    };
    ;
    /**
     * Add multiple tags for the current user. Tags are key:value string pairs used as building blocks for targeting specific users and/or personalizing messages. If the tag key already exists, it will be replaced with the value provided here.
     * @param  {object} tags
     * @returns void
     */
    User.prototype.addTags = function (tags) {
        var convertedTags = tags;
        Object.keys(tags).forEach(function (key) {
            // forces values to be string types
            if (typeof convertedTags[key] !== "string") {
                convertedTags[key] = JSON.stringify(convertedTags[key]);
            }
        });
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "addTags", [convertedTags]);
    };
    ;
    /**
     * Remove the data tag with the provided key from the current user.
     * @param  {string} key
     * @returns void
     */
    User.prototype.removeTag = function (key) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeTags", [key]);
    };
    ;
    /**
     * Remove multiple tags with the provided keys from the current user.
     * @param  {string[]} keys
     * @returns void
     */
    User.prototype.removeTags = function (keys) {
        window.cordova.exec(function () { }, function () { }, "OneSignalPush", "removeTags", keys);
    };
    ;
    /** Returns the local tags for the current user.
     * @returns Promise<{ [key: string]: string }>
     */
    User.prototype.getTags = function () {
        return new Promise(function (resolve, reject) {
            window.cordova.exec(resolve, reject, "OneSignalPush", "getTags", []);
        });
    };
    ;
    /**
     * Add a callback that fires when the OneSignal User state changes.
     * Important: When using the observer to retrieve the onesignalId, check the externalId as well to confirm the values are associated with the expected user.
     * @param  {(event: UserChangedState)=>void} listener
     * @returns void
     */
    User.prototype.addEventListener = function (event, listener) {
        var _this = this;
        this._userStateObserverList.push(listener);
        var userCallBackProcessor = function (state) {
            _this._processFunctionList(_this._userStateObserverList, state);
        };
        window.cordova.exec(userCallBackProcessor, function () { }, "OneSignalPush", "addUserStateObserver", []);
    };
    /**
     * Remove a User State observer that has been previously added.
     * @param  {(event: UserChangedState)=>void} listener
     * @returns void
     */
    User.prototype.removeEventListener = function (event, listener) {
        var index = this._userStateObserverList.indexOf(listener);
        if (index !== -1) {
            this._userStateObserverList.splice(index, 1);
        }
    };
    /**
     * Get the nullable OneSignal Id associated with the current user.
     * @returns {Promise<string | null>}
     */
    User.prototype.getOnesignalId = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, new Promise(function (resolve, reject) {
                        window.cordova.exec(resolve, reject, "OneSignalPush", "getOnesignalId", []);
                    })];
            });
        });
    };
    /**
     * Get the nullable External Id associated with the current user.
     * @returns {Promise<string | null>}
     */
    User.prototype.getExternalId = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, new Promise(function (resolve, reject) {
                        window.cordova.exec(resolve, reject, "OneSignalPush", "getExternalId", []);
                    })];
            });
        });
    };
    return User;
}());
exports.default = User;

});
