App.directive('sbLoader', function() {
    return {
        restrict: 'E',
        scope: {
            is_visible: '=isVisible',
            size: '=',
            type: '=',
            animation: '=animation'
        },
        template:
            '<div class="{{ animation_class }} loader {{ type }} {{ size }}" ng-show="is_visible">' +
                // '<img ng-src="{{ loader_src }}" width="{{ size }}" />' +
            '</div>',
        replace: true,
        controller: function($scope) {
            $scope.loaders = {
                inner_content: IMAGE_PATH+"/loader/inner_content.gif",
                area: IMAGE_PATH+"/loader/area.gif"
            };
            $scope.loader_src = $scope.loaders[$scope.type];

            var animation = $scope.animation;
            if(!animation) animation = "toggle";
            $scope.animation_class = animation;

        }
    };
});