cordova.define("com.keosu.cordova.stream.Stream", function(require, exports, module) {
/*
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
*/

var argscheck = require('cordova/argscheck'),
    utils = require('cordova/utils'),
    exec = require('cordova/exec');

var mediaObjects = {};

/**
 * This class provides access to the device media, interfaces to both sound and video
 *
 * @constructor
 * @param src                   The file name or url to play
 * @param successCallback       The callback to be called when the file is done playing or recording.
 *                                  successCallback()
 * @param errorCallback         The callback to be called if there is an error.
 *                                  errorCallback(int errorCode) - OPTIONAL
 * @param statusCallback        The callback to be called when media status has changed.
 *                                  statusCallback(int statusCode) - OPTIONAL
 */
var Stream = function(src, successCallback, errorCallback, statusCallback) {
    argscheck.checkArgs('SFFF', 'Stream', arguments);
    this.id = utils.createUUID();
    mediaObjects[this.id] = this;
    this.src = src;
    this.successCallback = successCallback;
    this.errorCallback = errorCallback;
    this.statusCallback = statusCallback;
    exec(null, this.errorCallback, "Stream", "create", [this.id, this.src]);
};


// "static" function to return existing objs.
Stream.get = function(id) {
    return mediaObjects[id];
};

/**
 * Start or resume playing audio file.
 */
Stream.prototype.play = function(options) {
    exec(null, null, "Stream", "startPlayingAudio", [this.id, this.src, options]);
};

/**
 * Stop playing audio file.
 */
Stream.prototype.stop = function() {
    var me = this;
    exec(function() {
    }, this.errorCallback, "Stream", "stopPlayingAudio", [this.id]);
};


module.exports = Stream;

});
