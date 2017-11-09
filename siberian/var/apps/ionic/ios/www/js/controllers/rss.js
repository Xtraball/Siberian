/*global
    angular, BASE_PATH
 */
angular.module('starter').controller('RssListController', function ($filter, $scope, $state, $stateParams,
                                                                    Rss, Pages) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id
    });

    Rss.setValueId($stateParams.value_id);

    Rss.findAll()
        .then(function (data) {
            $scope.collection = data.collection;
            Rss.collection = angular.copy($scope.collection);
            if (data.cover) {
                Rss.collection.unshift(angular.copy(data.cover));
            }

            if (Pages.getLayoutIdForValueId(Rss.value_id) === 1) {
                $scope.cover = angular.copy(data.cover);
                $scope.page_title = angular.copy(data.page_title);
            } else {
                // Unshift before chunking!
                $scope.collection.unshift(angular.copy(data.cover));
                $scope.collection_chunks = $filter('chunk')($scope.collection, 2);
            }
        }).then(function () {
            $scope.is_loading = false;
        });

    $scope.showItem = function (item) {
        $state.go('rss-view', {
            value_id: $scope.value_id,
            feed_id: item.id
        });
    };
}).controller('RssViewController', function ($rootScope, $scope, $stateParams, LinkService, Rss) {
    angular.extend($scope, {
        is_loading: false,
        value_id: $stateParams.value_id
    });

    Rss.setValueId($stateParams.value_id);
    Rss.feed_id = $stateParams.feed_id;

    $scope.loadContent = function () {
        $scope.is_loading = true;

        Rss.getFeed($stateParams.feed_id)
            .then(function (feed) {
                $scope.item = feed;
            }).then(function () {
                $scope.is_loading = false;
            });
    };

    $scope.showItem = function () {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }

        LinkService.openLink($scope.item.url);
    };

    $scope.loadContent();
});
