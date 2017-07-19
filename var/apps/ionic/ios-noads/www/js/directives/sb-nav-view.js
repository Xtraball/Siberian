angular.module("starter").directive('sbNavView', function ($rootScope, SB) {
    return {
        restrict: 'E',
        replace: true,
        template: "<ion-nav-view></ion-nav-view>",
        link: function(scope, element) {
            $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.SHOW, function() {
                element.addClass("has-mini-player-controls");
            });
            $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.HIDE, function() {
                element.removeClass("has-mini-player-controls");
            });
        }
    };
});