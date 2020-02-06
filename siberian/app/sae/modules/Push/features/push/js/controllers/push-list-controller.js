/**
 * PushController
 *
 * @author Xtraball SAS
 * @version 4.18.9
 */

angular
.module('starter')
.controller('PushListController', function ($location, $rootScope, $scope, $stateParams,
                                        LinkService, SB, Push, Popover) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id,
        collection: [],
        cardDesign: false,
        load_more: false,
        actionsPopover: null,
        popoverItems: []
    });

    Push.setValueId($stateParams.value_id);

    $scope.loadContent = function (loadMore) {
        // Overview sample data
        if (isOverview) {
            Push
                .getSample()
                .then(function (payload) {
                    $scope.collection = payload.collection;
                    $scope.is_loading = false;
                });
            return;
        }

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

    // Popover actions!
    $scope.openActions = function ($event) {
        $scope
            .closeActions()
            .then(function () {
                Popover
                    .fromTemplateUrl('templates/push/l1/directives/actions-popover.html', {
                        scope: $scope
                    }).then (function (popover) {
                        $scope.actionsPopover = popover;
                        $scope.actionsPopover.show($event);
                    });
            });
    };

    $scope.closeActions = function () {
        try {
            if ($scope.actionsPopover) {
                return $scope.actionsPopover.hide();
            }
        } catch (e) {
            // We skip!
        }

        return $q.resolve();
    };

    /**
     *
     * */
    $scope.buildPopoverItems = function () {
        var deletePush = {
            label: $translate.instant('Delete', 'push'),
            icon: 'icon ion-close',
            click: function () {
                Push
                    .delete()
                    .then(function () {
                        $scope.pullToRefresh();
                    });
            }
        };
    };

    $scope.loadContent();
});
