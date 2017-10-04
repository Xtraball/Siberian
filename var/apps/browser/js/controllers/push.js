/**
 * PushController
 *
 * @author Xtraball SAS
 */

angular.module('starter').controller('PushController', function ($location, $rootScope, $scope, $stateParams,
                                                                 LinkService, SB, Push) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        collection: [],
        toggle_text: false,
        card_design: false,
        load_more: false,
        use_pull_refresh: true
    });

    Push.setValueId($stateParams.value_id);

    $scope.loadContent = function (loadMore) {
        var offset = $scope.collection.length;

        Push.findAll(offset)
            .then(function (data) {
                if (data.collection) {
                    $scope.collection = $scope.collection.concat(data.collection);
                    $rootScope.$broadcast(SB.EVENTS.PUSH.readPush);
                }

                $scope.page_title = data.page_title;

                $scope.load_more = (data.collection.length >= data.displayed_per_page);
            }).then(function () {
                if (loadMore) {
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }

                $scope.is_loading = false;
            });
    };

    $scope.pullToRefresh = function () {
        $scope.pull_to_refresh = true;
        $scope.load_more = false;

        Push.findAll(0, true)
            .then(function (data) {
                if (data.collection) {
                    $scope.collection = data.collection;
                    $rootScope.$broadcast(SB.EVENTS.PUSH.readPush);
                }

                $scope.load_more = (data.collection.length >= data.displayed_per_page);
            }).then(function () {
                $scope.$broadcast('scroll.refreshComplete');
                $scope.pull_to_refresh = false;
            });
    };

    /**
     * Toggle item or open link/feature
     * @param item
     */
    $scope.showItem = function (item) {
        if (item.url) {
            if ($rootScope.isNotAvailableOffline()) {
                return;
            }

            LinkService.openLink(item.url);
        } else if (item.action_value) {
            $location.path(item.action_value);
        } else {
            $scope.toggle_text = !$scope.toggle_text;
        }
    };

    $scope.loadMore = function () {
        $scope.loadContent(true);
    };

    $scope.loadContent();
});
