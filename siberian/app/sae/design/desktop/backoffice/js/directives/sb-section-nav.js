/*global
    App, angular
 */

/**
 * Sections with a side navigation: the first pane is shown, the other ones are one click away in the left menu.
 *
 * <sb-section-nav>
 *     <sb-section-pane title="'Support'" icon="fa-life-ring"> ... </sb-section-pane>
 *     <sb-section-pane title="'SMTP'" icon="fa-server"> ... </sb-section-pane>
 * </sb-section-nav>
 *
 * A pane renders like an sb-section (same card, same title and optional button). Panes are only hidden, never
 * removed, so the fields of the hidden ones stay in their form and are saved with it.
 */
App.directive('sbSectionNav', function () {
    return {
        restrict: 'E',
        transclude: true,
        scope: {},
        controller: function ($scope) {
            var panes = $scope.panes = [];

            $scope.select = function (pane) {
                angular.forEach(panes, function (item) {
                    item.active = (item === pane);
                });
            };

            // The first pane is the one shown by default
            this.add = function (pane) {
                panes.push(pane);
                if (panes.length === 1) {
                    $scope.select(pane);
                }
            };

            this.remove = function (pane) {
                var index = panes.indexOf(pane);
                if (index === -1) {
                    return;
                }
                panes.splice(index, 1);
                if (pane.active && panes.length > 0) {
                    $scope.select(panes[0]);
                }
            };
        },
        template:
            '<div class="sb-section-nav">' +
                '<ul class="sb-section-nav-list" role="tablist">' +
                    '<li ng-repeat="pane in panes" ng-class="{active: pane.active}" role="presentation">' +
                        '<a href="" role="tab" aria-selected="{{ pane.active }}" ng-click="select(pane)">' +
                            '<i class="fa fa-fw {{ pane.icon }}" ng-if="pane.icon"></i>' +
                            '<span ng-bind="pane.title"></span>' +
                        '</a>' +
                    '</li>' +
                '</ul>' +
                '<div class="sb-section-nav-panes" ng-transclude></div>' +
            '</div>'
    };
}).directive('sbSectionPane', function () {
    return {
        restrict: 'E',
        require: '^sbSectionNav',
        replace: true,
        transclude: true,
        scope: {
            title: '=',
            icon: '@',
            button: '=',
            klass: '=?'
        },
        template:
            '<div class="sb-section-pane" role="tabpanel" ng-show="active">' +
                '<div class="area">' +
                    '<div class="title {{ klass }}">' +
                        '<h2 ng-bind-html="title"></h2>' +
                        '<button ng-if="button" class="btn btn-primary right" ng-bind-html="button.text" ng-click="button.action()"></button>' +
                        '<div class="clear"></div>' +
                    '</div>' +
                    '<div class="content" ng-transclude></div>' +
                '</div>' +
            '</div>',
        link: function (scope, element, attributes, nav) {
            element.removeAttr('title');

            scope.active = false;
            nav.add(scope);

            scope.$on('$destroy', function () {
                nav.remove(scope);
            });
        }
    };
});
