/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular.module("starter").factory("FanwallUtils", function ($rootScope, Modal) {
    var factory = {
        _commentModal: null
    };

    /**
     *
     * @param item
     * @param cardDesign
     */
    factory.commentModal = function (item, cardDesign) {
        Modal
        .fromTemplateUrl("features/fanwall/assets/templates/l1/modal/comment.html", {
            scope: angular.extend($rootScope.$new(true), {
                item: item,
                cardDesign: cardDesign,
                close: function () {
                    factory._commentModal.hide();
                }
            }),
            animation: "slide-in-up"
        }).then(function (modal) {
            factory._commentModal = modal;
            factory._commentModal.show();

            return modal;
        });
    };

    return factory;
});