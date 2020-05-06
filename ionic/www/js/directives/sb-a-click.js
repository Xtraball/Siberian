/**
 * sb-a-click
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.16
 */
angular
.module('starter')
.directive('sbAClick', function($rootScope, $timeout, $window, $state, Pages, Dialog, LinkService, Customer, Codescan) {
    return {
        restrict: 'A',
        scope: {},
        priority: -10,
        link: function (scope, element) {
            $timeout(function () {
                // A links
                var collection = angular.element(element).find("a");
                angular.forEach(collection, function (elem) {
                    if(typeof elem.attributes["data-state"] !== "undefined") {
                        InAppLinks.handlerLink(elem);
                    } else {
                        angular.element(elem).bind('click', function (e) {
                            e.preventDefault();
                            LinkService.openLink(elem.href, {}, false);
                        });
                    }
                });
            });
        }
    };
});
