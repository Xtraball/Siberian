/**
 * PushController
 *
 * @author Xtraball SAS
 * @version 4.18.5
 */

angular
.module('starter')
.controller('PushController', function ($location, $rootScope, $scope, $stateParams, $timeout,
                                        LinkService, SB, Push, Dialog) {
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
        // Overview sample data
        if (isOverview) {
            Push
                .getSample()
                .then(function (payload) {
                    $scope.collection = payload.collection;
                    $scope.settings = payload.settings;
                    $timeout(function () {
                        $scope.card_design = (payload.settings.design === 'card');
                    });
                    $scope.is_loading = false;
                });
            return;
        }

        var offset = $scope.collection.length;

        Push.findAll(offset)
            .then(function (data) {
                if (data.collection) {
                    $scope.collection = $scope.collection.concat(data.collection);
                    $scope.settings = data.settings;
                    $timeout(function () {
                        $scope.card_design = (data.settings.design === 'card');
                    });
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
        // Overview sample data
        if (isOverview) {
            Push
                .getSample()
                .then(function (payload) {
                    $scope.collection = payload.collection;
                    $scope.is_loading = false;
                    $scope.$broadcast('scroll.refreshComplete');
                });
            return;
        }

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

    $scope.deletePush = function (item) {
        Dialog
            .confirm(
                'Confirmation',
                'Please confirm you want to delete this notification!',
                ['Yes', 'No'],
                '',
                'chatrooms')
            .then(function (result) {
                if (result) {
                    Push
                        .deletePush(item.deliver_id)
                        .then(function (success) {
                            $scope.pullToRefresh();
                            Dialog.alert('Success', success.message, 'OK', -1, 'push');
                        }, function (error) {
                            Dialog.alert('Error', error.message, 'OK', -1, 'push');
                        });
                }
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

            LinkService.openLink(item.url, {}, false);
        } else if (item.action_value) {
            $location.path(item.action_value);
        }
    };

    $scope.hasItem = function (item) {
        return (item.url || item.action_value);
    };

    $scope.loadMore = function () {
        $scope.loadContent(true);
    };

    $scope.loadContent();
});
