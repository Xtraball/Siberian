module.exports = {
  updateCallback: function () {},

  create: function (data, successCallback, errorCallback) {
    data.artist = !isUndefined(data.artist) ? data.artist : '';
    data.track = !isUndefined(data.track) ? data.track : '';
    data.album = !isUndefined(data.album) ? data.album : '';
    data.cover = !isUndefined(data.cover) ? data.cover : '';
    data.ticker = !isUndefined(data.ticker) ? data.ticker : '';
    data.duration = !isUndefined(data.duration) ? data.duration : 0;
    data.elapsed = !isUndefined(data.elapsed) ? data.elapsed : 0;
    data.isPlaying = !isUndefined(data.isPlaying) ? data.isPlaying : true;
    data.hasPrev = !isUndefined(data.hasPrev) ? data.hasPrev : true;
    data.hasNext = !isUndefined(data.hasNext) ? data.hasNext : true;
    data.hasClose = !isUndefined(data.hasClose) ? data.hasClose : false;
    data.dismissable = !isUndefined(data.dismissable) ? data.dismissable : false;

    cordova.exec(successCallback, errorCallback, 'MusicControls', 'create', [data]);
  },

  updateIsPlaying: function (isPlaying, successCallback, errorCallback) {
    cordova.exec(successCallback, errorCallback, 'MusicControls', 'updateIsPlaying', [{isPlaying: isPlaying}]);
  },
  updateDismissable: function (dismissable, successCallback, errorCallback) {
    cordova.exec(successCallback, errorCallback, 'MusicControls', 'updateDismissable', [{dismissable: dismissable}]);
  },

  destroy: function (successCallback, errorCallback) {
    cordova.exec(successCallback, errorCallback, 'MusicControls', 'destroy', []);
  },

  // Register callback
  subscribe: function (onUpdate) {
    module.exports.updateCallback = onUpdate;
  },
  // Start listening for events
  listen: function () {
    cordova.exec(module.exports.receiveCallbackFromNative, function (res) {
    }, 'MusicControls', 'watch', []);
  },
  receiveCallbackFromNative: function (messageFromNative) {
    module.exports.updateCallback(messageFromNative);
    cordova.exec(module.exports.receiveCallbackFromNative, function (res) {
    }, 'MusicControls', 'watch', []);
  }

};

function isUndefined(val) {
  return val === undefined;
}
