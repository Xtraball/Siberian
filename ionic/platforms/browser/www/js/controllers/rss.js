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

    $scope.loadContent = function () {
        Rss
        .getFeeds()
        .then(function (data) {
            $scope.feeds = data.feeds;
            $scope.settings = data.settings;
            $scope.page_title = data.page_title;
            Rss.feeds = angular.copy($scope.feeds);
            Rss.settings = angular.copy($scope.settings);

        }, function (error) {
            Dialog.alert("Error", "We are unable to find this feed groups.", "OK", 2350, "rss");
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.refresh = function () {
        $scope.is_loading = true;
        $scope.loadContent();
    };

    $scope.goToList = function (feed) {
        $state.go("rss-list", {
            value_id: $scope.value_id,
            feed_id: feed.id
        });
    };

    $scope.loadContent();
})
.controller("RssListController", function ($filter, $scope, $state, $stateParams, Rss, Pages, Dialog) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        feed_id: $stateParams.feed_id
    });

    Rss.setValueId($stateParams.value_id);
    Rss.setFeedId($stateParams.feed_id);

    $scope.loadContent = function () {
        if ($scope.feed_id !== "") {
            Rss
            .getSingleFeed($scope.feed_id)
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

            }, function (error) {
                Dialog.alert("Error", "We are unable to find this feed groups.", "OK", 2350, "rss");
            }).then(function () {
                $scope.is_loading = false;
            });
        } else {
            Rss
            .getGroupedFeeds()
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

            }, function (error) {
                Dialog.alert("Error", "We are unable to find this feed groups.", "OK", 2350, "rss");
            }).then(function () {
                $scope.is_loading = false;
            });
        }
    };

    $scope.refresh = function () {
        $scope.is_loading = true;
        $scope.loadContent();
    };

    $scope.showItem = function (item) {
        $state.go("rss-view", {
            value_id: $scope.value_id,
            item_id: item.id
        });
    };

    $scope.loadContent();

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
