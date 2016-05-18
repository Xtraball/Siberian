App.directive('sbNavView', function ($rootScope) {
    return {
        restrict: 'E',
        replace: true,
        template: "<ion-nav-view></ion-nav-view>",
        link: function(scope, element) {
            $rootScope.$on("mediaPlayer.mini.show", function() {
                element.addClass("has-mini-player-controls");
            });
            $rootScope.$on("mediaPlayer.mini.hide", function() {
                element.removeClass("has-mini-player-controls");
            });
        }
    };
});