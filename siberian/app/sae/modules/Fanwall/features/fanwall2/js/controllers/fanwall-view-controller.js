/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module('starter')
.controller('FanwallViewController', function (Modal, $pwaRequest, $rootScope, $scope, $state, $stateParams, $timeout,
                                               $translate, Application, SB, Comment, Customer, Dialog, FanwallPost,
                                               SocialSharing, Loader) {
    angular.extend($scope, {
        value_id: $stateParams.value_id,
        avatars: {},
        page_title: '',
        is_logged_in: Customer.isLoggedIn(),
        social_sharing_active: false,
        show_comment_button: true,
        show_like_button: true,
        cardDesign: true
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.customerAvatar = function (customer_id) {
        if (!(customer_id in $scope.avatars)) {
            var avatar = Customer.getAvatarUrl(customer_id);
            $scope.avatars[customer_id] = avatar;
        }
        return $scope.avatars[customer_id];
    };

    $scope.loadContent = function (refresh) {
        $scope.is_logged_in = Customer.isLoggedIn();

        $scope.isLoading = true;

        FanwallPost
        .getComment($stateParams.comment_id)
        .then(function (news) {
            $scope.social_sharing_active = (news.social_sharing_active && $rootScope.isNativeApp);
            $scope.comments = news.answers;
            $scope.show_flag_button = (news.code === 'fanwall');
            $scope.page_title = news.title;
            $scope.item = news;
        }).then(function () {
            $scope.isLoading = false;
        });
    };

    $scope.share = function () {
        var file = ($scope.item.picture) ? $scope.item.picture : undefined;

        SocialSharing.share($scope.item.cleaned_message, undefined, undefined, undefined, file);
    };

    $scope.login = function () {
        Customer.loginModal($scope, function () {
            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.loadContent(true);
        });
    };

    $scope.comment = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        $state.go('newswall-comment', {
            value_id: $scope.value_id,
            comment_id: $stateParams.comment_id
        });
    };

    $scope.addLike = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Loader.show();

        FanwallPost
        .addLike($scope.item.id)
        .then(function (data) {
            if (data.success) {
                $scope.item.number_of_likes++;

                Dialog.alert('', data.message, 'OK', -1);
            }
        }, function (data) {
            $scope.showError(data);
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.flagPost = function () {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Loader.show();

        FanwallPost
        .flagPost($scope.item.id)
        .then(function (data) {
            if (data.success) {
                Dialog.alert('', data.message, 'OK', -1);
            }
        }, function (data) {
            $scope.showError(data);
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.flagComment = function (answer_id) {
        if ($rootScope.isNotAvailableOffline()) {
            return;
        }

        Loader.show();

        FanwallPost
        .flagComment(answer_id)
        .then(function (data) {
            if (data.success) {
                Dialog.alert('', data.message, 'OK', -1);
            }
        }, function (data) {
            $scope.showError(data);
        }).then(function () {
            Loader.hide();
        });
    };

    $scope.showError = function (data) {
        if (data && angular.isDefined(data.message)) {
            Dialog.alert('Error', data.message, 'OK', -1);
        }
    };

    $scope.loadContent();

    $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
        $scope.loadContent();
    });

    $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
        $scope.loadContent();
    });
});