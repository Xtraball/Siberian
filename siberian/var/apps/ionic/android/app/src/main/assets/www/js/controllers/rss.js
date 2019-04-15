/**
 * RSS Controllers
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.6
 */
angular.module("starter")
.controller("RssGroupController", function ($filter, $scope, $state, $stateParams, Rss, Dialog) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        feeds: []
    });

    Rss.setValueId($stateParams.value_id);

    $scope.loadContent = function (refresh) {
        Rss
        .getFeeds(refresh)
        .then(function (data) {
            $scope.feeds = data.feeds;
            $scope.settings = data.settings;
            $scope.page_title = data.page_title;
            Rss.feeds = angular.copy($scope.feeds);
            Rss.settings = angular.copy($scope.settings);

            $scope.feeds_chunks = $filter("chunk")($scope.feeds, 2);
            $scope.feeds_achunks = $filter("achunk")($scope.feeds, 2, 1);

        }, function (error) {
            Dialog.alert("Error", "We are unable to find this feed groups.", "OK", 2350, "rss");
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.thumbnailSrc = function (item) {
        if (item.thumbnail.match(/^http/)) {
            return item.thumbnail;
        }
        return IMAGE_URL + "images/application" + item.thumbnail;
    };

    $scope.refresh = function () {
        $scope.is_loading = true;
        $scope.loadContent(true);
    };

    $scope.goToList = function (feed) {
        $state.go("rss-list", {
            value_id: $scope.value_id,
            feed_id: feed.id
        });
    };

    $scope.loadContent(false);
})
.controller("RssListController", function ($filter, $scope, $state, $stateParams, Rss, Pages, Dialog) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        feed_id: $stateParams.feed_id
    });

    Rss.setValueId($stateParams.value_id);
    Rss.setFeedId($stateParams.feed_id);

    $scope.loadContent = function (refresh) {
        if ($scope.feed_id !== "") {
            Rss
            .getSingleFeed($scope.feed_id, refresh)
            .then(function (data) {
                $scope.collection = data.collection;
                $scope.settings = data.settings;
                $scope.page_title = data.page_title;
                Rss.collection = angular.copy($scope.collection);
                Rss.settings = angular.copy($scope.settings);

                if ($scope.settings.displayCover &&
                    $scope.collection.length > 0) {

                    $scope.cover = $scope.collection[0];
                    $scope.collection.shift();
                }

                $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
                $scope.collection_achunks = $filter("achunk")($scope.collection, 2, 1);

            }, function (error) {
                Dialog.alert("Error", "We are unable to find this feed groups.", "OK", 2350, "rss");
            }).then(function () {
                $scope.is_loading = false;
            });
        } else {
            Rss
            .getGroupedFeeds(refresh)
            .then(function (data) {
                $scope.collection = data.collection;
                $scope.settings = data.settings;
                $scope.page_title = data.page_title;
                Rss.collection = angular.copy($scope.collection);
                Rss.settings = angular.copy($scope.settings);

                if ($scope.settings.displayCover &&
                    $scope.collection.length > 0) {

                    $scope.cover = $scope.collection[0];
                    $scope.collection.shift();
                }

                $scope.collection_chunks = $filter("chunk")($scope.collection, 2);
                $scope.collection_achunks = $filter("achunk")($scope.collection, 2, 1);

            }, function (error) {
                Dialog.alert("Error", "We are unable to find this feed groups.", "OK", 2350, "rss");
            }).then(function () {
                $scope.is_loading = false;
            });
        }
    };

    $scope.refresh = function () {
        $scope.is_loading = true;
        $scope.loadContent(true);
    };

    $scope.showItem = function (item) {
        $state.go("rss-view", {
            value_id: $scope.value_id,
            item_id: item.id
        });
    };

    $scope.loadContent(false);

}).controller("RssViewController", function ($rootScope, $scope, $stateParams, LinkService, Rss) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id
    });

    Rss.setValueId($stateParams.value_id);
    Rss.item_id = $stateParams.item_id;

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Rss
        .findItem($stateParams.item_id)
        .then(function (item) {
            $scope.item = item;
            $scope.settings = Rss.settings;
            $scope.page_title = item.title;
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.showItem = function () {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        LinkService.openLink($scope.item.link);
    };

    $scope.loadContent();
});
