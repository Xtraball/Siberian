/* global
 angular, lazyLoadResolver, BASE_PATH
 */

angular.module("starter").config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state("rss-group", {
            url: BASE_PATH + "/rss/mobile_feed_group/index/value_id/:value_id",
            templateUrl: function (param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layout_id) {
                    case 2:
                        return "templates/rss/l2/group.html";
                    case 3:
                        return "templates/rss/l3/group.html";
                    case 1:
                    default:
                        return "templates/rss/l1/group.html";
                }
            },
            controller: "RssGroupController",
            cache: false,
            resolve: lazyLoadResolver("rss")
        })
        .state("rss-list", {
            url: BASE_PATH + "/rss/mobile_feed_list/index/value_id/:value_id/feed_id/:feed_id",
            templateUrl: function (param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layout_id) {
                    case 2:
                        return "templates/rss/l2/list.html";
                    case 3:
                        return "templates/rss/l3/list.html";
                    case 1:
                    default:
                        return "templates/rss/l1/list.html";
                }
            },
            controller: "RssListController",
            cache: false,
            resolve: lazyLoadResolver("rss")
        }).state("rss-view", {
            url: BASE_PATH + "/rss/mobile_feed_view/index/value_id/:value_id/item_id/:item_id",
        templateUrl: function (param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch (layout_id) {
                case 2:
                    return "templates/rss/l2/view.html";
                case 3:
                    return "templates/rss/l3/view.html";
                case 1:
                default:
                    return "templates/rss/l1/view.html";
            }
        },
            controller: "RssViewController",
            cache: false,
            resolve: lazyLoadResolver("rss")
        });
});
