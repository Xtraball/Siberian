"use strict";

App.directive("sbVideo", function($window, Application) {
    return {
        restrict: "A",
        replace:true,
        scope: {
            video: "="
        },
        template:
            '<div class="video">'
                +'<div ng-if="!show_player">'
                    +'<div class="play_video">'
                        +'<div class="sprite"></div>'
                        +'<div class="youtube_preview cover" image-src="video.cover_url" sb-image></div>'
                    +'</div>'
                    +'<div class="background title" ng-if="video.title">'
                        +'<div>'
                            +'<img ng-src="{{ video.icon_url }}" width="20" class="icon left" />'
                            +'<p class="title_video">{{ video.title }}</p>'
                        +'</div>'
                    +'</div>'
                +'</div>'
                +'<div ng-if="use_iframe" ng-show="show_player">'
                    +'<iframe type="text/html" width="100%" height="200" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                +'</div>'
                +'<div ng-if="!use_iframe" ng-show="show_player">'
                    +'<div id="video_player_view" class="player">'
                        +'<video src="" type="video/mp4" controls preload="none" width="100%" height="200px">'
                        +'</video>'
                    +'</div>'
                +'</div>'
            +'</div>'
        ,
        link: function(scope, element) {

//            var video = element.find("video");
//            if(video.length) {
//                video.attr("poster", video.cover_url);
//            }
            element.bind("click", function() {

                var show_player = true;

                if(Application.handle_media_player) {
                    if (/youtube/.test(scope.video.url)) {
                        Application.call("openYoutubePlayer", scope.video.video_id);
                        return;
                    } else if (/vimeo/.test(scope.video.url)) {
                        Application.call("openVimeoPlayer", scope.video.video_id);
                        return;
                    } else if(Application.is_android) {
                        Application.call("openVideoPlayer", scope.video.url);
                        return;
                    }
                } else if(/(youtube)|(vimeo)/.test(scope.video.url)) {
                    element.find('iframe').attr('src', scope.video.url+"?autoplay=1");
                } else {
                    element.find('video').attr('src', scope.video.url);
                }

                if(show_player) {
                    scope.show_player = true;
                    scope.$apply();

                    element.unbind("click");
                }
            });
        },
        controller: function($scope) {
            $scope.show_player = false;
            $scope.use_iframe = /(youtube)|(vimeo)/.test($scope.video.url);
        }
    };
});