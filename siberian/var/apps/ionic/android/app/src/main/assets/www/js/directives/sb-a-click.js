/**
 * sb-a-click
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.17
 */
angular
.module('starter')
.directive('sbAClick', function($rootScope, $timeout, $window, $state, InAppLinks, Pages, LinkService) {
    return {
        restrict: 'A',
        scope: {},
        priority: -10,
        link: function (scope, element) {
            $timeout(function () {
                // A links
                var collection = angular.element(element).find('a');
                angular.forEach(collection, function (_element) {
                    if (_element.attributes.hasOwnProperty('data-state')) {
                        InAppLinks.handlerLink(_element);
                    } else {
                        angular.element(_element).bind('click', function (event) {
                            event.preventDefault();
                            LinkService.openLink(_element.href, {}, true);
                        });
                    }
                });
            });
        }
    };
});
