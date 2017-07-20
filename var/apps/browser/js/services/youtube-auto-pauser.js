/*global
 angular
 */
angular.module("starter").service('YouTubeAutoPauser', function($ionicPlatform, $window) {
  var iframes = [];
  var players = [];
  var initialized = false;
  var loaded = false;
  var service = {};

  function initialize() {
    initialized = true;
    $window.onYouTubeIframeAPIReady = function() {
      loaded = true;
      players = players.concat(iframes.map(function(iframe) {
        return new YT.Player(iframe, {});
      }));

      $ionicPlatform.on("pause", function (result) {
          var filtered_players = [];
          players.forEach(function(item) {
            if(
              angular.isObject(item) &&  // YT.Player
              angular.isFunction(item.pauseVideo) && // check function exists
              angular.isObject(item.a) && // iframe element
              angular.isObject(item.a.parentElement) // check if still in DOM
            ) {
              item.pauseVideo();
              filtered_players.push(item);
            }
          });
          players = filtered_players; // replace players with checked and filtered
      });
    };

    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  }

  service.register = function(iframe) {
    iframe = angular.element(iframe)[0];

    if(loaded) {
      players.push(new YT.Player(iframe, {}));
    } else {
      if(!initialized) {
        initialize();
      }
      iframes.push(iframe);
    }
  };

  return service;
});
