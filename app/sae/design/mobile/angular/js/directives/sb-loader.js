App.directive('sbLoader', function() {
    return {
        restrict: 'E',
        scope: {
            is_loading: '=isLoading',
            size: '=size',
            block: '=block'
        },
        template:
        '<div class="relative full_width" ng-show="is_loading" ng-class="animation">' +
            '<div class="loader" ng-class="{small: size == 32}">' +
                '<div class="{{block}}_floatingCirclesG_{{ size }} floatingCirclesG_{{ size }}"><div class="f_circleG frotateG_01"></div><div class="f_circleG frotateG_02"></div><div class="f_circleG frotateG_03"></div><div class="f_circleG frotateG_04"></div><div class="f_circleG frotateG_05"></div><div class="f_circleG frotateG_06"></div><div class="f_circleG frotateG_07"></div><div class="f_circleG frotateG_08"></div></div>' +
            '</div>' +
        '</div>',
        replace: true,
        link: function(scope, element, attrs) {
            var animation = attrs.animation;
            if(!animation) animation = "toggle";
            scope.animation = animation;
        }
    };
});