"use strict";

App.directive("sbVideo", function($timeout, $window) {
    return {
        restrict: "A",
        replace:true,
        scope: {
            video: "="
        },
        template:
            '<div class="card">'
                + '<div class="item item-image" ng-click="play()">'
                    + '<div ng-hide="show_player" ng-style="height_style">'
                        + '<img ng-src="{{ video.cover_url }}" />'
                        + '<div class="sprite"></div>'
                    + '</div>'
                    + '<div ng-show="show_player">'
                        +'<div ng-if="use_iframe">'
                            +'<iframe type="text/html" width="100%" height="200" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                        +'</div>'
                        +'<div ng-if="!use_iframe">'
                            +'<div id="video_player_view" class="player">'
                                +'<video src="" type="video/mp4" controls preload="none" width="100%" height="200px"></video>'
                            +'</div>'
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

                if(ionic.Platform.isAndroid()) {
                    if (/(vimeo)/.test(scope.video.url)) {
                      $window.open(scope.video.url, '_system');
                    } else {
                      VideoPlayer.play(scope.video.url);
                    }
                } else {
                    $timeout(function() {
                        if (/(youtube)|(vimeo)/.test(scope.video.url_embed)) {
                            element.find('iframe').attr('src', scope.video.url_embed + "?autoplay=1&autopause=1");
                        } else {
                            element.find('video').attr('src', scope.video.url_embed);
                        }

                        scope.show_player = true;
                    });
                }
            };
        },
        controller: function($scope) {
            $scope.show_player = false;
            $scope.use_iframe = /(youtube)|(vimeo)/.test($scope.video.url);
        }
    };
});