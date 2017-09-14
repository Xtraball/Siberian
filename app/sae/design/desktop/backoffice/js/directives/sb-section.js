/*global
    App
 */
App.directive('sbSection', function () {
    return {
        restrict: 'E',
        replace: true,
        scope: {
            title: '=',
            button: '='
        },
        transclude: true,
        template:
            '<div class="area">' +
                '<div class="title">' +
                    '<h2 ng-bind-html="title"></h2>' +
                    '<button ng-if="button" class="btn btn-primary right" ng-bind-html="button.text" ng-click="button.action()"></button>' +
                    '<div class="clear"></div>' +
                '</div>' +
                '<div class="content" ng-transclude></div>' +
            '</div>'
    };
});
