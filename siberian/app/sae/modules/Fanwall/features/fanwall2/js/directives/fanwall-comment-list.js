/**
 * fanwallCommentList
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .directive('fanwallCommentList', function ($timeout, $interval, ModalScrollDelegate, Lightbox, FanwallPost) {
    return {
        restrict: 'E',
        replace: true,
        templateUrl: 'features/fanwall2/assets/templates/l1/modal/directives/comment-list.html',
        link: function (scope) {
            scope.$watch('post', function () {
                // Updating local `post` instance
                scope._post = scope.post;
            });
        },
        controller: function ($rootScope, $scope) {
            $scope.scrollToBottom = function () {
                $timeout(function () {
                    ModalScrollDelegate
                        .$getByHandle('fanwall-comment-list')
                        .scrollBottom(true);

                    $timeout(function () {
                        Lightbox.run('.list-comments');
                    }, 200);
                }, 200);
            };

            $scope.listDidRender = function () {
                $scope.scrollToBottom();
            };

            //$scope.$on('destroy', function () {
            //    $interval.cancel($scope.liveComments);
            //});

            // Refresh comments every 5 seconds!
            //$scope.liveComments = $interval(function () {
            //    if ($scope.refreshInProgress) {
            //        return;
            //    }
//
            //    $scope.refreshInProgress = true;
            //    FanwallPost
            //        .findOne($scope.post.id)
            //        .then(function (payload) {
            //            $scope.refreshInProgress = false;
            //            $rootScope.$broadcast('fanwall.refresh.comments', {
            //                comments: payload.collection[0].comments,
            //                postId: payload.collection[0].id
            //            });
            //        }, function () {
            //            $scope.refreshInProgress = false;
            //        });
            //}, 5000);

            $rootScope.$on('fanwall.refresh.comments', function (event, payload) {
                // Comments are updated, but we don't want to interfere were interval auto-refresh!
                //if ($scope.refreshInProgress) {
                //    return;
                //}
                if (payload.postId === $scope.post.id) {
                    $timeout(function () {
                        $scope.post.comments = angular.copy(payload.comments);
                        $scope.post.commentCount = $scope.post.comments.length;
                    });
                }
            });
        }
    };
});
