"use strict";

App.directive("sbVideo", function($timeout, $window, $ionicPlatform) {
    return {
        restrict: "A",
        replace:true,
        scope: {
            video: "="
        },
        template:
            '<div class="card">'
                + '<div class="item item-image" ng-click="play()">'
                    + '<div ng-hide="use_iframe || use_video_element" ng-style="height_style">'
                        + '<img ng-src="{{ video.cover_url }}" />'
                        + '<div class="sprite"></div>'
                    + '</div>'
                    +'<div ng-if="use_iframe">'
                        +'<iframe type="text/html" width="100%" height="200" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                    +'</div>'
                    +'<div ng-if="use_video_element">'
                        +'<div id="video_player_view" class="player">'
                            +'<video src="" type="video/mp4" controls preload="none" width="100%" height="200px"></video>'
                        +'</div>'
                    +'</div>'
                + '</div>'
                + '<div ng-if="video.title" class="item item-text-wrap item-custom">'
                    + '<p>{{ video.title }}</p>'
                + '</div>'
            + '</div>'
        ,
        link: function(scope, element) {
            scope.height_style = null;
            if(!scope.video.cover_url) {
                scope.height_style = {"min-height":"200px","width":"100%"};
            }

            scope.play = function() {
                //Open videos on external apps for android version prior 5
                //Indeed Android < 5 don't stop video played in background
                //And video player is bugged
                if((device.platform == "Android") && parseInt(device.version.substr(0,1)) < 5) {
                    $window.open(scope.video.url, '_system');
                } else { // iOS or Android >= 5
                    //check if it is a youtube/vimeo url
                    if(/^https?:\/\/(www.|player.)?(youtube|vimeo)\./.test(scope.video.url)) {
                        scope.use_iframe = true;
                        $timeout(function() {
                            element.find('iframe').attr('src', scope.video.url_embed + "?autoplay=1&autopause=1");
                        });
                    } else {
                        scope.use_video_element = true;
                        $timeout(function() {
                            element.find('video').attr('src', scope.video.url_embed);
                        });
                    }
                }
            };

        },
        controller: function($scope) {
            $scope.use_iframe = false;
            $scope.use_video_element = false;
        }
    };
});