/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular.module("starter").factory("FanwallUtils", function ($rootScope, $timeout, Dialog, Loader, Modal, FanwallPost) {
    var factory = {
        _postModal: null,
        _showPostModal: null,
        _commentModal: null
    };

    /**
     *
     * @param post
     */
    factory.postModal = function (post) {
        Modal
        .fromTemplateUrl("features/fanwall2/assets/templates/l1/modal/new.html", {
            scope: angular.extend($rootScope.$new(true), {
                post: post,
                close: function () {
                    factory._postModal.hide();
                }
            }),
            animation: "slide-in-right-left"
        }).then(function (modal) {
            factory._postModal = modal;
            factory._postModal.show();

            return modal;
        });
    };

    /**
     *
     * @param postGroup
     */
    factory.showPostModal = function (postGroup) {
        var _localScope = angular.extend($rootScope.$new(true), {
            //postGroup: postGroup,
            //isPostDetails: true,
            modalReady: false,
            close: function () {
                factory._showPostModal.hide();
            }
        });

        Modal
        .fromTemplateUrl("features/fanwall2/assets/templates/l1/modal/post.html", {
            scope: _localScope,
            animation: "slide-in-right-left"
        }).then(function (modal) {
            factory._showPostModal = modal;
            factory._showPostModal.show();

            // Sending data to modal only after rendering!
            $timeout(function () {
                _localScope.postGroup = postGroup;
                _localScope.isPostDetails = true;
                _localScope.modalReady = true;

                $rootScope.$broadcast("fanwall.modal.ready");
            }, 500);

            return modal;
        });
    };

    /**
     *
     * @param post
     */
    factory.commentModal = function (post) {
        Modal
        .fromTemplateUrl("features/fanwall2/assets/templates/l1/modal/comment.html", {
            scope: angular.extend($rootScope.$new(true), {
                post: post,
                close: function () {
                    factory._commentModal.hide();
                }
            }),
            animation: "slide-in-right-left"
        }).then(function (modal) {
            factory._commentModal = modal;
            factory._commentModal.show();

            return modal;
        });
    };

    factory.blockUser = function (sourceId, from) {
        Dialog
        .confirm(
            "Confirmation",
            "You are about to block all this user messages!",
            ["YES", "NO"],
            -1,
            "fanwall")
        .then(function (value) {
            if (!value) {
                return;
            }
            Loader.show();

            FanwallPost
            .blockUser(sourceId, from)
            .then(function (payload) {
                $rootScope.$broadcast("fanwall.refresh");
                $rootScope.$broadcast("fanwall.refresh.comments", {comments: payload.comments, postId: payload.postId});
                Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
            }, function (payload) {
                Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
            }).then(function () {
                Loader.hide();
            });
        });
    };

    factory.unblockUser = function (sourceId, from) {
        Dialog
        .confirm(
            "Confirmation",
            "You are about to unblock all this user messages!",
            ["YES", "NO"],
            -1,
            "fanwall")
        .then(function (value) {
            if (!value) {
                return;
            }
            Loader.show();

            FanwallPost
            .unblockUser(sourceId, from)
            .then(function (payload) {
                $rootScope.$broadcast("fanwall.refresh");
                $rootScope.$broadcast("fanwall.refresh.comments", {comments: payload.comments, postId: payload.postId});
                Dialog.alert("Thanks!", payload.message, "OK", 2350, "fanwall");
            }, function (payload) {
                Dialog.alert("Error!", payload.message, "OK", -1, "fanwall");
            }).then(function () {
                Loader.hide();
            });
        });
    };

    return factory;
});