angular.module('starter')
    .directive('sbNavView', function ($rootScope, SB) {
        return {
            restrict: 'E',
            replace: true,
            template: '<ion-nav-view></ion-nav-view>',
            link: function (scope, element) {
                $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.SHOW, function (event, args) {
                    element.addClass('has-mini-player-controls');
                    if (args.isRadio) {
                        element.addClass('mini-radio'); // 75px
                    } else {
                        element.addClass('mini-audio'); // 95px
                    }
                });
                $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.HIDE, function () {
                    element.removeClass('has-mini-player-controls');
                    element.removeClass('mini-radio');
                    element.removeClass('mini-audio');
                });
            }
        };
    });

angular.module('starter')
    .directive('miniPlayer', function ($rootScope, SB) {
        return {
            restrict: 'A',
            link: function (scope, element) {
                $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.SHOW, function (event, args) {
                    element.addClass('has-mini-player-controls');
                    if (args.isRadio) {
                        element.addClass('mini-radio'); // 75px
                    } else {
                        element.addClass('mini-audio'); // 95px
                    }
                });
                $rootScope.$on(SB.EVENTS.MEDIA_PLAYER.HIDE, function () {
                    element.removeClass('has-mini-player-controls');
                    element.removeClass('mini-radio');
                    element.removeClass('mini-audio');
                });
            }
        };
    });
