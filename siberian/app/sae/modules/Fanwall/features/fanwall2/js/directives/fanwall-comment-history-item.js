/**
 * fanwallCommentHistoryItem
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .directive('fanwallCommentHistoryItem', function (Fanwall) {
        return {
            restrict: 'E',
            templateUrl: 'features/fanwall2/assets/templates/l1/modal/comment/history-item.html',
            controller: function ($scope, $filter) {
                $scope.getCardDesign = function () {
                    return Fanwall.getSettings().cardDesign;
                };

                $scope.imagePath = function () {
                    if ($scope.item.image.length <= 0) {
                        return './features/fanwall2/assets/templates/images/placeholder.png';
                    }
                    return IMAGE_URL + 'images/application' + $scope.item.image;
                };

                $scope.publicationDate = function () {
                    return $filter('moment_calendar')($scope.item.date * 1000);
                };
            }
        };
    });


