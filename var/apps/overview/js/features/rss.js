/* global
 angular, lazyLoadResolver, BASE_PATH
 */

angular.module('starter').config(function ($stateProvider, HomepageLayoutProvider) {
    $stateProvider
        .state('rss-list', {
            url: BASE_PATH + '/rss/mobile_feed_list/index/value_id/:value_id',
            templateUrl: function (param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch (layout_id) {
                    case 2:
                        layout_id = 'l5';
                        break;
                    case 3:
                        layout_id = 'l6';
                        break;
                    default:
                        layout_id = 'l3';
                }

                return 'templates/html/' + layout_id + '/list.html';
            },
            controller: 'RssListController',
            cache: false,
            resolve: lazyLoadResolver('rss')
        }).state('rss-view', {
            url: BASE_PATH + '/rss/mobile_feed_view/index/value_id/:value_id/feed_id/:feed_id',
            templateUrl: 'templates/rss/l1/view.html',
            controller: 'RssViewController',
            cache: false,
            resolve: lazyLoadResolver('rss')
        });
});
