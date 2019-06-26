/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular.module("starter").factory("FanwallUtils", function ($rootScope, Modal) {
    var factory = {
        _postModal: null,
        _commentModal: null
    };

    /**
     *
     * @param post
     */
    factory.postModal = function (post) {
        Modal
            .fromTemplateUrl("features/fanwall/assets/templates/l1/modal/new.html", {
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
     * @param post
     */
    factory.commentModal = function (post) {
        Modal
        .fromTemplateUrl("features/fanwall/assets/templates/l1/modal/comment.html", {
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

    return factory;
});