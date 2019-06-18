/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallCommentController", function ($ionicHistory, $pwaRequest, $rootScope, $scope, $state, $stateParams,
                                                  $timeout, $translate, $window, Comment, Customer, Dialog, Modal,
                                                  FanwallPost, FanwallComment) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        is_logged_in: Customer.isLoggedIn(),
        comment: {
            id: $stateParams.comment_id,
            text: ""
        },
        cardDesign: false
    });

    FanwallPost.setValueId($stateParams.value_id);
    FanwallComment.setValueId($stateParams.value_id);

    $scope.post = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        if (_.isObject($scope.comment) &&
            _.isString($scope.comment.text) &&
            $scope.comment.text.length > 0 &&
            $scope.comment.text.length < 1024) {
            $scope.isLoading = true;

            FanwallComment
            .add($scope.comment)
            .then(function (data) {
                if (data.success) {
                    $scope.comment.text = '';

                    Dialog.alert('', data.message, 'OK', -1)
                        .then(function () {
                            Newswall.findAll(0, true)
                                .then(function () {
                                    $ionicHistory.goBack();
                                });
                        });
                }
            }).then(function () {
                $scope.isLoading = false;
            });
        }
    };
});