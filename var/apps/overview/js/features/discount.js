/*global
 App, angular, lazyLoadResolver, BASE_PATH
 */
angular.module("starter").config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider
        .state("discount-list", {
            url             : BASE_PATH+"/promotion/mobile_list/index/value_id/:value_id",
            controller      : "DiscountListController",
            cache           : false,
            resolve         : lazyLoadResolver("discount"),
            templateUrl     : function(param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch(layout_id) {
                    case 2:
                        layout_id = "l2";
                        break;
                    case 3:
                        layout_id = "l5";
                        break;
                    case 4:
                        layout_id = "l6";
                        break;
                    default:
                        layout_id = "l3";
                }
                return "templates/html/" + layout_id + "/list.html";
            }
        }).state("discount-view", {
            url             : BASE_PATH+"/promotion/mobile_view/index/value_id/:value_id/promotion_id/:promotion_id",
            controller      : "DiscountViewController",
            templateUrl     : "templates/discount/l1/view.html",
            cache           : false,
            resolve         : lazyLoadResolver("discount")
        });

});