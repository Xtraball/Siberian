/**
 * fanwallPostItem
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .directive('fanwallPostList', function ($timeout, Lightbox) {
        return {
            restrict: 'E',
            replace: true,
            templateUrl: 'features/fanwall2/assets/templates/l1/tabs/directives/post-list.html',
            link: function (scope) {
                scope.$watch('post', function () {
                    // Updating local `post` instance
                    scope._post = scope.post;
                });
            },
            controller: function ($scope) {
                $scope.listDidRender = function () {
                    $timeout(function () {
                        // We need a single instance for every post*
                        document
                            .querySelectorAll('.list-posts fanwall-post-item')
                            .forEach(function (item) {
                                Lightbox.run('[rel="fanwall-gallery-' + item.id + '"]');
                            });
                    }, 200);
                };
            }
        };
    });
